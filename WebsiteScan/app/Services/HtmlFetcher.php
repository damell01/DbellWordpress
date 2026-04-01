<?php
namespace App\Services;

class HtmlFetcher {
    private int $timeout = 8;
    private int $connectTimeout = 4;
    private array $defaultHeaders = [
        'User-Agent: Mozilla/5.0 (compatible; SiteScope/1.0; +https://sitescope.app)',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.5',
        'Accept-Encoding: gzip, deflate',
        'Connection: close',
    ];

    public function fetch(string $url): array {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_HTTPHEADER     => $this->defaultHeaders,
            CURLOPT_ENCODING       => '',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HEADER         => true,
        ]);

        $start    = microtime(true);
        $response = curl_exec($ch);
        $elapsed  = round((microtime(true) - $start) * 1000);

        $error    = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        if ($error) {
            return [
                'success'      => false,
                'error'        => $error,
                'html'         => '',
                'headers'      => [],
                'http_code'    => 0,
                'final_url'    => $url,
                'response_time'=> $elapsed,
                'size'         => 0,
            ];
        }

        $headers  = substr($response, 0, $headerSize);
        $html     = substr($response, $headerSize);
        $size     = strlen($html);

        return [
            'success'       => ($httpCode >= 200 && $httpCode < 400),
            'html'          => $html,
            'headers'       => $this->parseHeaders($headers),
            'http_code'     => $httpCode,
            'final_url'     => $finalUrl,
            'response_time' => $elapsed,
            'size'          => $size,
            'error'         => '',
        ];
    }

    public function fetchMany(array $urls): array {
        $urls = array_values(array_unique(array_filter($urls)));
        if (empty($urls)) {
            return [];
        }

        $multi = curl_multi_init();
        $handles = [];
        $results = [];

        foreach ($urls as $url) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 5,
                CURLOPT_TIMEOUT        => $this->timeout,
                CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
                CURLOPT_HTTPHEADER     => $this->defaultHeaders,
                CURLOPT_ENCODING       => '',
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HEADER         => true,
            ]);
            $handles[$url] = ['handle' => $ch, 'start' => microtime(true)];
            curl_multi_add_handle($multi, $ch);
        }

        do {
            $status = curl_multi_exec($multi, $running);
            if ($running) {
                curl_multi_select($multi, 1.0);
            }
        } while ($running && $status === CURLM_OK);

        foreach ($handles as $url => $meta) {
            $ch = $meta['handle'];
            $response = curl_multi_getcontent($ch);
            $elapsed = round((microtime(true) - $meta['start']) * 1000);
            $error = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

            if ($error || $response === false) {
                $results[$url] = [
                    'success' => false,
                    'error' => $error ?: 'Request failed.',
                    'html' => '',
                    'headers' => [],
                    'http_code' => 0,
                    'final_url' => $url,
                    'response_time' => $elapsed,
                    'size' => 0,
                ];
            } else {
                $headers = substr($response, 0, $headerSize);
                $html = substr($response, $headerSize);
                $results[$url] = [
                    'success' => ($httpCode >= 200 && $httpCode < 400),
                    'html' => $html,
                    'headers' => $this->parseHeaders($headers),
                    'http_code' => $httpCode,
                    'final_url' => $finalUrl,
                    'response_time' => $elapsed,
                    'size' => strlen($html),
                    'error' => '',
                ];
            }

            curl_multi_remove_handle($multi, $ch);
            curl_close($ch);
        }

        curl_multi_close($multi);
        return $results;
    }

    public function checkUrl(string $url): array {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_NOBODY         => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_TIMEOUT        => 4,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER     => $this->defaultHeaders,
        ]);
        $start    = microtime(true);
        curl_exec($ch);
        $elapsed  = round((microtime(true) - $start) * 1000);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);
        return [
            'http_code'     => $code,
            'response_time' => $elapsed,
            'error'         => $error,
            'success'       => !$error && $code >= 200 && $code < 400,
        ];
    }

    public function checkMany(array $urls): array {
        $urls = array_values(array_unique(array_filter($urls)));
        if (empty($urls)) {
            return [];
        }

        $multi = curl_multi_init();
        $handles = [];
        $results = [];

        foreach ($urls as $url) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_NOBODY         => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 3,
                CURLOPT_TIMEOUT        => 4,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HTTPHEADER     => $this->defaultHeaders,
            ]);
            $handles[$url] = ['handle' => $ch, 'start' => microtime(true)];
            curl_multi_add_handle($multi, $ch);
        }

        do {
            $status = curl_multi_exec($multi, $running);
            if ($running) {
                curl_multi_select($multi, 1.0);
            }
        } while ($running && $status === CURLM_OK);

        foreach ($handles as $url => $meta) {
            $ch = $meta['handle'];
            $elapsed = round((microtime(true) - $meta['start']) * 1000);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $results[$url] = [
                'http_code' => $code,
                'response_time' => $elapsed,
                'error' => $error,
                'success' => !$error && $code >= 200 && $code < 400,
            ];

            curl_multi_remove_handle($multi, $ch);
            curl_close($ch);
        }

        curl_multi_close($multi);
        return $results;
    }

    private function parseHeaders(string $rawHeaders): array {
        $headers = [];
        foreach (explode("\n", $rawHeaders) as $line) {
            if (str_contains($line, ':')) {
                [$key, $value] = array_map('trim', explode(':', $line, 2));
                $headers[strtolower($key)] = $value;
            }
        }
        return $headers;
    }
}
