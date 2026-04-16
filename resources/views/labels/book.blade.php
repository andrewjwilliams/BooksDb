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
            --dewey-w: 4mm;
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
            grid-template-rows: auto 1fr auto;
            box-shadow: 0 0 0 1px #aaa;
            overflow: hidden;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

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
            display: grid;
            grid-template-columns: 1fr var(--dewey-w);
            align-items: center;
            gap: 1mm;
            min-height: 0;
        }

        .label__main.no-dewey {
            grid-template-columns: 1fr;
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
            width: 100%;
            height: 100%;
        }

        .label__dewey {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            transform: rotate(180deg);
            font-size: 6pt;
            font-weight: bold;
            text-align: center;
            line-height: 1;
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

        <div class="label__main {{ $book->dewey_classification ? '' : 'no-dewey' }}">
            <div class="label__barcode">
                <svg class="js-barcode"></svg>
            </div>
            @if ($book->dewey_classification)
                <div class="label__dewey">{{ $book->dewey_classification }}</div>
            @endif
        </div>

        <div class="label__bottom">
            <div class="title">{{ $book->title }}</div>
            <div class="author">{{ $book->author->name ?? '' }}</div>
        </div>
    </div>
</body>
</html>
