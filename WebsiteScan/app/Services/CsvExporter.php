<?php
namespace App\Services;

class CsvExporter {
    public function export(array $data, array $headers): string {
        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);
        foreach ($data as $row) {
            $values = [];
            foreach ($headers as $header) {
                $key = strtolower(str_replace(' ', '_', $header));
                $values[] = $row[$key] ?? '';
            }
            fputcsv($output, $values);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }

    public function exportLeads(array $leads): string {
        $headers = ['ID', 'Business Name', 'Contact Name', 'Email', 'Phone', 'Website URL', 'Status', 'Notes', 'Created At'];
        $rows    = array_map(fn($l) => [
            'id'            => $l['id'],
            'business_name' => $l['business_name'] ?? '',
            'contact_name'  => $l['contact_name'] ?? '',
            'email'         => $l['email'] ?? '',
            'phone'         => $l['phone'] ?? '',
            'website_url'   => $l['website_url'] ?? '',
            'status'        => $l['status'] ?? 'new',
            'notes'         => $l['notes'] ?? '',
            'created_at'    => $l['created_at'] ?? '',
        ], $leads);

        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, array_values($row));
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }

    public function exportScans(array $scans): string {
        $headers = ['ID', 'URL', 'Status', 'Overall Score', 'SEO Score', 'Accessibility Score', 'Conversion Score', 'Technical Score', 'Requested At', 'Completed At'];
        $output  = fopen('php://temp', 'r+');
        fputcsv($output, $headers);
        foreach ($scans as $s) {
            fputcsv($output, [
                $s['id'],
                $s['website_url'],
                $s['status'],
                $s['overall_score'] ?? '',
                $s['seo_score'] ?? '',
                $s['accessibility_score'] ?? '',
                $s['conversion_score'] ?? '',
                $s['technical_score'] ?? '',
                $s['requested_at'] ?? '',
                $s['completed_at'] ?? '',
            ]);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }

    public function sendDownload(string $csv, string $filename): void {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $csv;
        exit;
    }
}
