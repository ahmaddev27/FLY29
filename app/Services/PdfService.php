<?php

namespace App\Services;

use Illuminate\Http\Response;
use Mpdf\Mpdf;

/**
 * Thin wrapper around mPDF configured for Arabic / RTL output.
 *
 * mPDF (unlike dompdf) does full Arabic shaping + bidi natively, so
 * raw UTF-8 Arabic in the source HTML renders correctly with connected
 * letters and proper word order.
 */
class PdfService
{
    /**
     * Render the given HTML as a PDF download for Arabic/RTL content.
     */
    public function downloadArabic(string $html, string $filename, string $format = 'A4'): Response
    {
        $mpdf = $this->makeArabicMpdf($format);
        $mpdf->WriteHTML($html);

        return new Response(
            $mpdf->Output($filename, 'S'),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ],
        );
    }

    /**
     * Render to a raw binary string (useful for emails / inline embedding).
     */
    public function renderArabic(string $html, string $format = 'A4'): string
    {
        $mpdf = $this->makeArabicMpdf($format);
        $mpdf->WriteHTML($html);

        return $mpdf->Output('', 'S');
    }

    /**
     * Build an mPDF instance pre-configured for Arabic + Cairo font.
     */
    private function makeArabicMpdf(string $format = 'A4'): Mpdf
    {
        $tempDir = storage_path('app/mpdf-tmp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }


        // mPDF ships XB Riyaz (Arabic) + DejaVu Sans (Latin) in
        // vendor/mpdf/mpdf/ttfonts and registers them by default. We don't
        // need to download or register anything — just turn on the Arabic-
        // friendly options.
        return new Mpdf([
            'mode'              => 'utf-8',
            'format'            => $format,
            'orientation'       => 'P',
            'directionality'    => 'rtl',
            'autoScriptToLang'  => true,
            'autoLangToFont'    => true,
            'default_font'      => 'xbriyaz',
            'default_font_size' => 11,
            'margin_left'       => 12,
            'margin_right'      => 12,
            'margin_top'        => 14,
            'margin_bottom'     => 14,
            'tempDir'           => storage_path('app/mpdf-tmp'),
        ]);
    }
}
