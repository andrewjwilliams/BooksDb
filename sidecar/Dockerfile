FROM python:3.12-slim

WORKDIR /app

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1 \
    PIP_NO_CACHE_DIR=1 \
    DEBIAN_FRONTEND=noninteractive

# We don't run cupsd inside the container; the Pi host already has CUPS on
# port 631. We just need the lp/lpstat clients to dispatch to it. With
# network_mode: host, "localhost:631" inside the container is the host CUPS.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        cups-client \
        cups-bsd \
    && rm -rf /var/lib/apt/lists/*

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY app.py .

EXPOSE 5151

CMD ["uvicorn", "app:app", "--host", "127.0.0.1", "--port", "5151", "--log-level", "info"]
