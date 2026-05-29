#!/bin/bash
# Start CUPS in the background, register the printer queue, then run uvicorn.

set -e

QUEUE_NAME="${LABEL_PRINTER_QUEUE:-booksdb}"
PRINTER_HOST="${LABEL_PRINTER_HOST:-}"
PRINTER_PORT="${LABEL_PRINTER_PORT:-631}"
PRINTER_PATH="${LABEL_PRINTER_IPP_PATH:-/ipp/print}"

# CUPS configuration: listen on localhost only, allow no-auth admin from local
mkdir -p /var/log/cups /var/spool/cups /run/cups
cat > /etc/cups/cupsd.conf <<EOF
LogLevel warn
PageLogFormat
MaxLogSize 0
SystemGroup root
Listen localhost:631
Browsing No
DefaultAuthType None
WebInterface No
DefaultPolicy default
<Location />
  Order allow,deny
  Allow localhost
</Location>
<Location /admin>
  Order allow,deny
  Allow localhost
</Location>
<Location /admin/conf>
  Order allow,deny
  Allow localhost
</Location>
<Policy default>
  JobPrivateAccess default
  JobPrivateValues default
  SubscriptionPrivateAccess default
  SubscriptionPrivateValues default
  <Limit All>
    Order allow,deny
    Allow localhost
  </Limit>
</Policy>
EOF

# Start cupsd in the background
/usr/sbin/cupsd -f &
CUPSD_PID=$!
sleep 2

# Wait for cups to be responsive
for i in {1..15}; do
    if lpstat -r >/dev/null 2>&1; then break; fi
    sleep 1
done
lpstat -r || (echo "cupsd did not start"; exit 1)

# Register the printer queue using IPP Everywhere driverless setup
if [ -n "$PRINTER_HOST" ]; then
    echo "Registering queue $QUEUE_NAME -> ipp://$PRINTER_HOST:$PRINTER_PORT$PRINTER_PATH"
    lpadmin -p "$QUEUE_NAME" -E -v "ipp://$PRINTER_HOST:$PRINTER_PORT$PRINTER_PATH" -m everywhere || \
        echo "lpadmin failed (will retry per-print)"
    cupsenable "$QUEUE_NAME" 2>/dev/null || true
    cupsaccept "$QUEUE_NAME" 2>/dev/null || true
    lpstat -p "$QUEUE_NAME" || true
else
    echo "LABEL_PRINTER_HOST not set; skipping printer registration"
fi

# Run uvicorn in foreground; if either uvicorn or cupsd dies, the container exits
trap 'kill $CUPSD_PID 2>/dev/null; exit 0' TERM INT
uvicorn app:app --host 127.0.0.1 --port 5151 --log-level info &
UVICORN_PID=$!
wait -n
kill $CUPSD_PID $UVICORN_PID 2>/dev/null
exit 1
