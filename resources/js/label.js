import JsBarcode from 'jsbarcode';

function render() {
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

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', render);
} else {
    render();
}
