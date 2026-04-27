<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>Label: {{ $book->title }}</title>
    <style>
        :root {
            --label-w: 51mm;
            --label-h: 19mm;
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

        .label {
            box-sizing: border-box;
            width: var(--label-w);
            height: var(--label-h);
            padding: var(--label-pad);
            background: #fff;
            color: #000;
            display: grid;
            grid-template-rows: 2.5mm 1fr 4.5mm;
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
            justify-content: space-between;
            align-items: baseline;
            font-size: 5pt;
            line-height: 1;
            gap: 2mm;
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
            display: grid;
            grid-template-rows: 1fr 1fr;
            row-gap: 0.5mm;
            justify-items: center;
            align-items: start;
            height: 100%;
            overflow: hidden;
        }

        .label__side > div {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            font-size: 4pt;
            font-weight: bold;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-height: 100%;
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
                size: var(--label-w) var(--label-h);
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
        <button type="button" onclick="window.print()">Print</button>
        <button type="button" onclick="window.close()">Close</button>
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
            <div class="side-id">ID: {{ $book->id }}</div>
            @if ($book->dewey_classification)
                <div class="side-dewey">Dewey: {{ $book->dewey_classification }}</div>
            @endif
        </div>
    </div>
</body>
</html>
