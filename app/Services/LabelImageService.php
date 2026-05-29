<?php

namespace App\Services;

use App\Models\Book;
use Picqer\Barcode\BarcodeGeneratorPNG;

class LabelImageService
{
    /**
     * Render the book's label as a PNG.
     *
     * @return string Raw PNG bytes
     */
    public function render(Book $book, string $tape = '62'): string
    {
        $book->loadMissing('author');

        $tapes = config('printing.tapes', []);
        if (! isset($tapes[$tape])) {
            throw new \InvalidArgumentException("Unknown tape: {$tape}");
        }

        $w = $tapes[$tape]['width_px'];
        $h = $tapes[$tape]['height_px'];

        $im = imagecreatetruecolor($w, $h);
        imageantialias($im, false);
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        imagefilledrectangle($im, 0, 0, $w, $h, $white);

        $fontDir = base_path('resources/fonts');
        $fontReg = $fontDir.'/DejaVuSans.ttf';
        $fontBold = $fontDir.'/DejaVuSans-Bold.ttf';

        $libraryName = (string) config('app.library_name', 'Library');
        $domain = parse_url((string) config('app.url'), PHP_URL_HOST) ?? '';
        $title = (string) ($book->title ?? '');
        $author = (string) optional($book->author)->name;
        $dewey = (string) ($book->dewey_classification ?? '');

        $sideStripWidth = 80;
        $padX = 6;
        $padY = 4;
        $mainRight = $w - $sideStripWidth - 4;

        // ----- Top: library name (line 1, bold), domain (line 2, smaller, regular) -----
        $libSize = 20;
        $domainSize = 12;
        $libBaseline = $padY + $libSize;
        $domainBaseline = $libBaseline + 4 + $domainSize;
        $fittedLib = $this->fitText($libraryName, $mainRight - $padX, $libSize, $fontBold);
        imagettftext($im, $libSize, 0, $padX, $libBaseline, $black, $fontBold, $fittedLib);
        if ($domain !== '') {
            $fittedDomain = $this->fitText($domain, $mainRight - $padX, $domainSize, $fontReg);
            imagettftext($im, $domainSize, 0, $padX, $domainBaseline, $black, $fontReg, $fittedDomain);
        }
        $topBaseline = $domainBaseline;

        // ----- Bottom: title (bold) + author (regular) -----
        $titleSize = 24;
        $authorSize = 18;
        $bottomBaseline = $h - $padY - 2;
        $titleBaseline = $bottomBaseline - ($authorSize + 4);

        $fittedTitle = $this->fitText($title, $mainRight - $padX, $titleSize, $fontBold);
        $fittedAuthor = $this->fitText($author, $mainRight - $padX, $authorSize, $fontReg);

        imagettftext($im, $titleSize, 0, $padX, $titleBaseline, $black, $fontBold, $fittedTitle);
        imagettftext($im, $authorSize, 0, $padX, $bottomBaseline, $black, $fontReg, $fittedAuthor);

        // ----- Middle: barcode of the book ID -----
        $barcodeTop = $topBaseline + 6;
        $barcodeBottom = $titleBaseline - $titleSize - 4;
        $barcodeW = $mainRight - $padX;
        $barcodeH = max(60, $barcodeBottom - $barcodeTop);

        $generator = new BarcodeGeneratorPNG();
        $barcodePng = $generator->getBarcode((string) $book->id, BarcodeGeneratorPNG::TYPE_CODE_128, 2, max(40, $barcodeH));
        $barcodeIm = imagecreatefromstring($barcodePng);
        if ($barcodeIm !== false) {
            $bcW = imagesx($barcodeIm);
            $bcH = imagesy($barcodeIm);
            imagecopyresampled(
                $im,
                $barcodeIm,
                $padX,
                $barcodeTop,
                0,
                0,
                $barcodeW,
                $barcodeH,
                $bcW,
                $bcH
            );
            imagedestroy($barcodeIm);
        }

        // ----- Right strip: vertical ID and Dewey (bottom-to-top) -----
        $sideTextSize = 18;
        $idText = 'ID '.$book->id;
        // angle 90 in GD = bottom-to-top (head tilts left to read)
        imagettftext($im, $sideTextSize, 90, $w - $sideStripWidth + 18, $h - $padY, $black, $fontBold, $idText);
        if ($dewey !== '') {
            $deweyText = 'DDC '.$dewey;
            imagettftext($im, $sideTextSize, 90, $w - $sideStripWidth + 50, $h - $padY, $black, $fontBold, $deweyText);
        }

        // Stamp 300 DPI on the PNG so CUPS knows the physical size and
        // doesn't apply its own scale-to-fit guesses.
        imageresolution($im, 300, 300);

        ob_start();
        imagepng($im, null, 1);
        $png = ob_get_clean();
        imagedestroy($im);

        return $png;
    }

    /**
     * Truncate text with a trailing ellipsis until it fits in maxWidth pixels at the given font size.
     */
    private function fitText(string $text, int $maxWidth, int $size, string $font): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        $width = function (string $s) use ($size, $font): int {
            $bbox = imagettfbbox($size, 0, $font, $s);
            return $bbox[2] - $bbox[0];
        };

        if ($width($text) <= $maxWidth) {
            return $text;
        }

        $ellipsis = '…';
        $candidate = $text;
        while (mb_strlen($candidate) > 1) {
            $candidate = mb_substr($candidate, 0, mb_strlen($candidate) - 1);
            if ($width(rtrim($candidate).$ellipsis) <= $maxWidth) {
                return rtrim($candidate).$ellipsis;
            }
        }

        return $candidate;
    }
}
