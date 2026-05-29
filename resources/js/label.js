import JsBarcode from 'jsbarcode';

function renderBarcode() {
    const label = document.querySelector('.label[data-book-id]');
    const svg = document.querySelector('svg.js-barcode');
    if (!label || !svg) return;

    const bookId = label.dataset.bookId;
    if (!bookId) return;

    JsBarcode(svg, String(bookId), {
        format: 'CODE128',
        displayValue: false,
        margin: 0,
        height: 30,
        width: 1.2,
    });

    svg.setAttribute('preserveAspectRatio', 'none');
}

function setStatus(text, kind) {
    const el = document.getElementById('print-status');
    if (!el) return;
    el.textContent = text || '';
    el.className = kind || '';
}

function wirePrintButton() {
    const btn = document.getElementById('btn-print-network');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        const url = btn.dataset.printUrl;
        if (!url) return;

        btn.disabled = true;
        setStatus('Printing…', 'busy');

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
            });
            const body = await res.json().catch(() => ({}));
            if (res.ok && body.ok) {
                setStatus('Printed ✓', 'ok');
            } else {
                setStatus(body.error || `Print failed (${res.status})`, 'err');
            }
        } catch (err) {
            setStatus('Network error: ' + (err && err.message ? err.message : err), 'err');
        } finally {
            setTimeout(() => {
                btn.disabled = false;
                if (document.getElementById('print-status').classList.contains('ok')) {
                    setStatus('', '');
                }
            }, 1500);
        }
    });
}

function init() {
    renderBarcode();
    wirePrintButton();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
