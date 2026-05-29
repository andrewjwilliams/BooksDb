"""
Print sidecar — submits PNG label jobs to the host's CUPS queue via the
`lp` command. Host CUPS owns the IPP-Everywhere queue pointing at the
Brother QL-810W; this container just talks `lp` over the shared loopback
(both services use docker-compose `network_mode: host`).

Endpoints:
    POST /print   — submit a PNG to the queue
    GET  /health  — sidecar + queue status
"""

from __future__ import annotations

import asyncio
import base64
import io
import logging
import os
import subprocess
import tempfile
from contextlib import asynccontextmanager
from typing import Optional

from fastapi import FastAPI, HTTPException, status
from fastapi.responses import JSONResponse
from pydantic import BaseModel, Field
from PIL import Image

QUEUE_NAME = os.environ.get("LABEL_PRINTER_QUEUE", "booksdb")
JOB_NAME = os.environ.get("LABEL_PRINTER_JOB_NAME", "BooksDb label")
DEFAULT_MEDIA = os.environ.get("LABEL_PRINTER_MEDIA", "Custom.62x29mm")
DEFAULT_CUT = os.environ.get("LABEL_PRINTER_CUT", "EndOfJob")

log = logging.getLogger("printer-bridge")
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s %(levelname)s %(name)s %(message)s",
)

print_lock = asyncio.Lock()


@asynccontextmanager
async def lifespan(app: FastAPI):
    log.info(
        "starting printer-bridge queue=%s media=%s cut=%s",
        QUEUE_NAME,
        DEFAULT_MEDIA,
        DEFAULT_CUT,
    )
    yield


app = FastAPI(lifespan=lifespan)


class PrintRequest(BaseModel):
    png_b64: str = Field(..., description="Base64-encoded PNG image")
    label: str = Field("62", description="Tape identifier (informational)")
    copies: int = Field(1, ge=1, le=10)


def _lpstat() -> str:
    try:
        out = subprocess.run(
            ["lpstat", "-p", QUEUE_NAME], capture_output=True, text=True, timeout=5
        )
        return (out.stdout or out.stderr).strip()
    except Exception as e:
        return f"lpstat error: {e}"


@app.get("/health")
async def health() -> dict:
    return {
        "ok": True,
        "queue": QUEUE_NAME,
        "media": DEFAULT_MEDIA,
        "cut": DEFAULT_CUT,
        "lpstat": _lpstat(),
    }


@app.post("/print")
async def print_label(req: PrintRequest) -> JSONResponse:
    try:
        png_bytes = base64.b64decode(req.png_b64, validate=True)
    except Exception as e:
        raise HTTPException(status_code=400, detail=f"Invalid base64 PNG: {e}")

    try:
        image = Image.open(io.BytesIO(png_bytes))
        image.load()
    except Exception as e:
        raise HTTPException(status_code=400, detail=f"Cannot decode PNG: {e}")

    log.info(
        "print request label=%s copies=%d image=%dx%d",
        req.label,
        req.copies,
        image.width,
        image.height,
    )

    async with print_lock:
        with tempfile.NamedTemporaryFile(suffix=".png", delete=False) as f:
            f.write(png_bytes)
            tmp_path = f.name

        try:
            cmd = [
                "lp",
                "-d", QUEUE_NAME,
                "-n", str(req.copies),
                "-t", JOB_NAME,
                "-o", f"media={DEFAULT_MEDIA}",
                "-o", f"CutMedia={DEFAULT_CUT}",
                "-o", "print-scaling=fit",
                tmp_path,
            ]
            try:
                result = await asyncio.to_thread(
                    subprocess.run,
                    cmd,
                    capture_output=True,
                    text=True,
                    timeout=30,
                )
            except subprocess.TimeoutExpired:
                return JSONResponse(
                    status_code=502,
                    content={"ok": False, "error": "lp command timed out"},
                )

            if result.returncode != 0:
                log.warning("lp failed: %s", result.stderr or result.stdout)
                return JSONResponse(
                    status_code=502,
                    content={
                        "ok": False,
                        "error": "lp rejected job",
                        "detail": (result.stderr or result.stdout).strip(),
                    },
                )

            log.info("lp ok: %s", result.stdout.strip())
            return JSONResponse(content={"ok": True, "lp_stdout": result.stdout.strip()})
        finally:
            try:
                os.unlink(tmp_path)
            except OSError:
                pass
