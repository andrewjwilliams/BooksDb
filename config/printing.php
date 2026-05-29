<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Label Printer Host
    |--------------------------------------------------------------------------
    |
    | The IP address (or hostname) of the Brother QL label printer on the LAN.
    | Set via LABEL_PRINTER_HOST in the .env file. The printer should be
    | configured with a static DHCP reservation on the local network.
    |
    | If unset, the print endpoint returns a clear "printer not configured"
    | error rather than attempting to dispatch a job.
    |
    */
    'host' => env('LABEL_PRINTER_HOST'),

    /*
    |--------------------------------------------------------------------------
    | Default Tape
    |--------------------------------------------------------------------------
    |
    | Identifier for the tape currently loaded in the printer. Matches the
    | brother_ql --label parameter. Today only "62" (DK-22205) is supported.
    |
    */
    'tape' => env('LABEL_PRINTER_TAPE', '62'),

    /*
    |--------------------------------------------------------------------------
    | Sidecar URL
    |--------------------------------------------------------------------------
    |
    | URL of the Python printer-bridge sidecar that wraps brother_ql.
    | Both the app container and the sidecar use network_mode: host, so this
    | resolves to the Pi's loopback address.
    |
    */
    'sidecar_url' => env('LABEL_PRINTER_SIDECAR_URL', 'http://127.0.0.1:5151'),

    /*
    |--------------------------------------------------------------------------
    | Tape canvas dimensions
    |--------------------------------------------------------------------------
    |
    | brother_ql expects exact pixel widths for each tape. Heights for
    | continuous tapes are variable and chosen to match the visual design.
    |
    */
    'tapes' => [
        '62' => [
            'kind' => 'continuous',
            'width_px' => 696,
            'height_px' => 342,
        ],
        '29x90' => [
            'kind' => 'die-cut',
            'width_px' => 306,
            'height_px' => 991,
        ],
    ],
];
