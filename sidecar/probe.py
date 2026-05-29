"""One-off: query the printer's supported document formats via IPP."""
import struct
import httpx
import asyncio
import sys


def attr(tag, name, value):
    nb = name.encode("utf-8")
    return struct.pack(">B H", tag, len(nb)) + nb + struct.pack(">H", len(value)) + value


def build_get_attrs(uri: str) -> bytes:
    body = struct.pack(">B B H I", 1, 1, 0x000B, 1)
    body += bytes([0x01])
    body += attr(0x47, "attributes-charset", b"utf-8")
    body += attr(0x48, "attributes-natural-language", b"en")
    body += attr(0x45, "printer-uri", uri.encode("utf-8"))
    body += attr(0x44, "requested-attributes", b"document-format-supported")
    body += attr(0x44, "requested-attributes", b"document-format-default")
    body += attr(0x44, "requested-attributes", b"printer-state")
    body += attr(0x44, "requested-attributes", b"printer-make-and-model")
    body += attr(0x44, "requested-attributes", b"ipp-versions-supported")
    body += attr(0x44, "requested-attributes", b"operations-supported")
    body += bytes([0x03])
    return body


async def main():
    host = sys.argv[1] if len(sys.argv) > 1 else "192.168.53.40"
    uri = f"ipp://{host}:631/ipp/print"
    url = f"http://{host}:631/ipp/print"

    async with httpx.AsyncClient(timeout=10) as c:
        r = await c.post(url, content=build_get_attrs(uri), headers={"Content-Type": "application/ipp"})
        print("HTTP", r.status_code, "body_len", len(r.content))
        if len(r.content) >= 8:
            ipp_status = struct.unpack(">H", r.content[2:4])[0]
            print(f"ipp_status=0x{ipp_status:04x}")

        b = r.content
        i = 8
        last_name = None
        while i < len(b):
            tag = b[i]
            i += 1
            if tag in (0x01, 0x02, 0x04, 0x05):
                continue
            if tag == 0x03:
                break
            if i + 2 > len(b):
                break
            nl = struct.unpack(">H", b[i:i + 2])[0]
            i += 2
            name = b[i:i + nl].decode("utf-8", errors="replace")
            i += nl
            if i + 2 > len(b):
                break
            vl = struct.unpack(">H", b[i:i + 2])[0]
            i += 2
            val = b[i:i + vl]
            i += vl
            try:
                vstr = val.decode("utf-8")
            except UnicodeDecodeError:
                if vl == 4:
                    vstr = str(struct.unpack(">i", val)[0])
                else:
                    vstr = val.hex(" ")
            n = name if name else f"(continuation of {last_name})"
            if name:
                last_name = name
            print(f"  {n} = {vstr}")


asyncio.run(main())
