<?php

namespace App\Services;

use Illuminate\Support\Facades\Response;

class ExportService
{
    /**
     * Export data to CSV
     */
    public static function exportToCsv(array $data, array $headers, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = $filename.'.csv';

        return Response::streamDownload(function () use ($data, $headers) {
            $file = fopen('php://output', 'w');

            // Add BOM for Excel UTF-8 compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Write headers
            fputcsv($file, $headers);

            // Write data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export data to PDF using DomPDF
     */
    public static function exportToPdf(array $data, array $headers, string $title, string $filename): \Illuminate\Http\Response
    {
        $html = view('exports.pdf-table', [
            'title' => $title,
            'headers' => $headers,
            'data' => $data,
        ])->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $filename = $filename.'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Prepare data for export from collection
     */
    public static function prepareData($collection, array $fields): array
    {
        return $collection->map(function ($item) use ($fields) {
            $row = [];
            foreach ($fields as $field => $label) {
                if (is_callable($label)) {
                    $row[] = $label($item);
                } else {
                    $value = data_get($item, $field, 'N/A');
                    $row[] = $value instanceof \Illuminate\Support\Carbon ? $value->format('Y-m-d H:i:s') : $value;
                }
            }

            return $row;
        })->toArray();
    }
}
