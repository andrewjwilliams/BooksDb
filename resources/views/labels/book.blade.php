<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>Label: {{ $book->title }}</title>
    <style>
        :root {
            --label-w: 62mm;
            --label-h: 29mm;
            --label-pad: 1mm;
            --side-w: 5mm;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: Helvetica, Arial, sans-serif;
            background: #ddd;
        }

        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1rem;
            gap: 1rem;
        }

        .toolbar {
            display: flex;
            gap: 0.5rem;
        }

        .toolbar button {
            font-size: 1rem;
            padding: 0.5rem 1rem;
            cursor: pointer;
        }

        .toolbar button.btn-primary {
            background: #1f6feb;
            color: #fff;
            border: 1px solid #1f6feb;
            border-radius: 0.25rem;
            font-weight: bold;
        }

        .toolbar button.btn-primary:disabled {
            opacity: 0.6;
            cursor: wait;
        }

        .toolbar .link-secondary {
            font-size: 0.9rem;
            color: #555;
            text-decoration: underline;
            align-self: center;
        }

        .toolbar #print-status {
            align-self: center;
            font-size: 0.95rem;
            min-width: 8rem;
        }
        .toolbar #print-status.ok    { color: #1a7f37; }
        .toolbar #print-status.err   { color: #c00; }
        .toolbar #print-status.busy  { color: #555; }

        .label {
            box-sizing: border-box;
            width: var(--label-w);
            height: var(--label-h);
            padding: var(--label-pad);
            background: #fff;
            color: #000;
            display: grid;
            grid-template-rows: 4mm 1fr 4.5mm;
            grid-template-columns: minmax(0, 1fr) var(--side-w);
            grid-template-areas:
                "top    side"
                "main   side"
                "bottom side";
            row-gap: 0.3mm;
            column-gap: 0.5mm;
            box-shadow: 0 0 0 1px #aaa;
            overflow: hidden;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .label__top    { grid-area: top; }
        .label__main   { grid-area: main; }
        .label__bottom { grid-area: bottom; }
        .label__side   { grid-area: side; }

        .label__top {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            font-size: 5pt;
            line-height: 1;
          gap: 0.3mm;
        }

        .label__top .library { font-weight: bold; }
        .label__top .domain { color: #444; }
        .label__top .library,
        .label__top .domain {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .label__main {
            display: flex;
            align-items: stretch;
            min-height: 0;
            overflow: hidden;
        }

        .label__barcode {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 0;
        }

        .label__barcode svg {
            display: block;
            width: 100%;
            height: 100%;
            max-height: 100%;
        }

        .label__side {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            height: 100%;
            overflow: hidden;
        }

        .label__side > div {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            font-weight: bold;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-height: 100%;
        }

        .label__side span.label__side_title {
            font-size: 3.2pt;
        }

        .label__side .side-id {
            font-size: 4pt;
        }

        .label__side .side-dewey {
            font-size: 4pt;
        }

        .label__bottom {
            font-size: 5pt;
            line-height: 1.1;
            overflow: hidden;
        }

        .label__bottom .title {
            font-weight: bold;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .label__bottom .author {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media print {
            @page {
                size: 62mm 29mm landscape;
                margin: 0;
            }

            html, body {
                background: #fff;
                width: var(--label-w);
                height: var(--label-h);
            }

            body {
                display: block;
                padding: 0;
            }

            .toolbar { display: none; }
            .label { box-shadow: none; }
        }
    </style>
    @vite(['resources/js/label.js'])
</head>
<body>
    <div class="toolbar">
        <button type="button" id="btn-print-network" data-print-url="/books/{{ $book->id }}/print" class="btn-primary">Print to Printer</button>
        <button type="button" onclick="window.close()">Close</button>
        <a href="#" id="btn-print-browser" onclick="window.print(); return false;" class="link-secondary">Print via browser…</a>
        <span id="print-status" role="status" aria-live="polite"></span>
    </div>

    <div class="label" data-book-id="{{ $book->id }}">
        <div class="label__top">
            <span class="library">{{ $libraryName }}</span>
            <span class="domain">{{ $domain }}</span>
        </div>

        <div class="label__main">
            <div class="label__barcode">
                <svg class="js-barcode"></svg>
            </div>
        </div>

        <div class="label__bottom">
            <div class="title">{{ $book->title }}</div>
            <div class="author">{{ $book->author->name ?? '' }}</div>
        </div>

        <div class="label__side">
            <div class="side-id"><span class="label__side_title">ID:</span> {{ $book->id }}</div>
            @if ($book->dewey_classification)
                <div class="side-dewey"><span class="label__side_title">DDC:</span> {{ $book->dewey_classification }}</div>
            @endif
        </div>
    </div>
</body>
</html>
