<?php
namespace App\Services;

use App\Models\Setting;

class PageSpeedService {
    private string $apiKey = '';
    private string $cacheDir;

    public function __construct() {
        $this->cacheDir = storage_path('cache/pagespeed');
        $this->apiKey = (string) env('GOOGLE_PAGESPEED_API_KEY', env('GOOGLE_MAPS_API_KEY', ''));

        if ($this->apiKey === '') {
            try {
                $settings = new Setting();
                $enabled = (string) $settings->get('enable_pagespeed_lookup', '1');
                if ($enabled !== '0') {
                    $this->apiKey = (string) ($settings->get('google_pagespeed_api_key', '') ?: $settings->get('google_maps_api_key', ''));
                }
            } catch (\Throwable $e) {
                $this->apiKey = '';
            }
        }
    }

    public function run(string $url, string $strategy = 'mobile'): array {
        $results = $this->runMany($url, [$strategy]);
        return $results[$strategy] ?? [
            'success' => false,
            'error' => 'PageSpeed request failed.',
        ];
    }

    public function runMany(string $url, array $strategies = ['mobile', 'desktop']): array {
        $results = [];
        $pendingStrategies = [];
        $baseUrl = 'https://pagespeedonline.googleapis.com/pagespeedonline/v5/runPagespeed';
        $multi = curl_multi_init();
        $handles = [];
        $requests = [];

        foreach ($strategies as $strategy) {
            $cached = $this->readCache($url, $strategy);
            if ($cached !== null) {
                $cached['cached'] = true;
                $results[$strategy] = $cached;
                continue;
            }

            $pendingStrategies[] = $strategy;
            $endpoint = $this->buildEndpoint($baseUrl, $url, $strategy);
            $requests[$strategy] = $endpoint;

            $ch = curl_init();
            curl_setopt_array($ch, $this->buildCurlOptions($endpoint));

            $handles[$strategy] = $ch;
            curl_multi_add_handle($multi, $ch);
        }

        if (!empty($pendingStrategies)) {
            do {
                $status = curl_multi_exec($multi, $running);
                if ($running) {
                    curl_multi_select($multi, 1.0);
                }
            } while ($running && $status === CURLM_OK);
        }

        foreach ($handles as $strategy => $ch) {
            $body = curl_multi_getcontent($ch);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            $results[$strategy] = $this->parseResponse($body, $error, $errno, $code, $strategy);
            if (!empty($results[$strategy]['success'])) {
                $this->writeCache($url, $strategy, $results[$strategy]);
            }

            curl_multi_remove_handle($multi, $ch);
            curl_close($ch);
        }

        curl_multi_close($multi);

        foreach ($strategies as $strategy) {
            $current = $results[$strategy] ?? null;
            if (!$this->shouldRetrySequentially($current)) {
                continue;
            }

            $results[$strategy] = $this->runSequentialRequest($requests[$strategy] ?? $this->buildEndpoint($baseUrl, $url, $strategy), $strategy);
            if (!empty($results[$strategy]['success'])) {
                $this->writeCache($url, $strategy, $results[$strategy]);
            }
        }

        return $results;
    }

    private function buildEndpoint(string $baseUrl, string $url, string $strategy): string {
        $query = [
            'url' => $url,
            'strategy' => $strategy,
        ];

        if (!empty($this->apiKey)) {
            $query['key'] = $this->apiKey;
        }

        $endpoint = $baseUrl . '?' . http_build_query($query);
        foreach (['performance', 'accessibility', 'best-practices', 'seo'] as $category) {
            $endpoint .= '&category=' . rawurlencode($category);
        }

        return $endpoint;
    }

    private function buildCurlOptions(string $endpoint): array {
        return [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_USERAGENT => 'SiteScopeAudit/1.0',
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ];
    }

    private function runSequentialRequest(string $endpoint, string $strategy): array {
        $ch = curl_init();
        curl_setopt_array($ch, $this->buildCurlOptions($endpoint));

        $body = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $this->parseResponse($body, $error, $errno, $code, $strategy);
    }

    private function shouldRetrySequentially(?array $result): bool {
        if (!is_array($result) || !empty($result['success'])) {
            return false;
        }

        $error = (string) ($result['error'] ?? '');
        $httpCode = (int) ($result['http_code'] ?? 0);

        return $httpCode === 0 || str_contains(strtolower($error), 'timed out') || str_contains(strtolower($error), 'could not resolve') || str_contains(strtolower($error), 'ssl');
    }

    private function parseResponse(string|bool $body, string $error, int $errno, int $code, string $strategy): array {

        if ($error || $code >= 400 || !$body) {
            $message = $error !== '' ? $error : ('HTTP ' . $code);
            if ($code === 0 && $error === '') {
                $message = 'Connection failed before Google responded.';
            }

            app_log('warning', 'PageSpeed request failed', [
                'strategy' => $strategy,
                'http_code' => $code,
                'curl_errno' => $errno,
                'curl_error' => $error,
            ]);

            return [
                'success' => false,
                'error' => $message,
                'http_code' => $code,
                'curl_errno' => $errno,
            ];
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            app_log('warning', 'PageSpeed returned invalid JSON', [
                'strategy' => $strategy,
                'http_code' => $code,
                'body_preview' => substr((string) $body, 0, 300),
            ]);
            return [
                'success' => false,
                'error' => 'Invalid API response.',
                'http_code' => $code,
                'curl_errno' => $errno,
            ];
        }

        $lighthouse = $data['lighthouseResult'] ?? [];
        $categories = $lighthouse['categories'] ?? [];
        $audits = $lighthouse['audits'] ?? [];
        $timing = $data['analysisUTCTimestamp'] ?? null;
        $performanceScore = isset($categories['performance']['score']) ? (int) round(($categories['performance']['score'] ?? 0) * 100) : null;
        $screenshots = $audits['screenshot-thumbnails']['details']['items'] ?? [];
        $finalScreenshot = $audits['final-screenshot']['details']['data'] ?? '';

        return [
            'success' => $performanceScore !== null,
            'score' => $performanceScore,
            'strategy' => $strategy,
            'http_code' => $code,
            'curl_errno' => $errno,
            'categories' => [
                'performance' => $this->normalizeCategoryScore($categories['performance']['score'] ?? null),
                'accessibility' => $this->normalizeCategoryScore($categories['accessibility']['score'] ?? null),
                'best_practices' => $this->normalizeCategoryScore($categories['best-practices']['score'] ?? null),
                'seo' => $this->normalizeCategoryScore($categories['seo']['score'] ?? null),
            ],
            'metrics' => [
                'fcp' => $audits['first-contentful-paint']['displayValue'] ?? '',
                'lcp' => $audits['largest-contentful-paint']['displayValue'] ?? '',
                'speed_index' => $audits['speed-index']['displayValue'] ?? '',
                'tbt' => $audits['total-blocking-time']['displayValue'] ?? '',
                'cls' => $audits['cumulative-layout-shift']['displayValue'] ?? '',
                'tti' => $audits['interactive']['displayValue'] ?? '',
            ],
            'environment' => [
                'timestamp' => $timing,
                'fetch_time' => $lighthouse['fetchTime'] ?? null,
                'user_agent' => $lighthouse['userAgent'] ?? '',
                'environment' => $lighthouse['environment'] ?? [],
            ],
            'screenshots' => [
                'final' => $finalScreenshot,
                'thumbnails' => array_values(array_filter(array_map(
                    static fn(array $item): string => (string) ($item['data'] ?? ''),
                    is_array($screenshots) ? $screenshots : []
                ))),
            ],
            'opportunities' => $this->collectAuditDetails($audits, [
                'render-blocking-resources',
                'unused-javascript',
                'unused-css-rules',
                'offscreen-images',
                'uses-optimized-images',
                'uses-responsive-images',
                'uses-text-compression',
                'server-response-time',
                'font-display',
            ]),
            'diagnostics' => $this->collectAuditDetails($audits, [
                'dom-size',
                'largest-contentful-paint-element',
                'network-requests',
                'network-rtt',
                'network-server-latency',
                'bootup-time',
                'mainthread-work-breakdown',
                'long-tasks',
                'diagnostics',
                'total-byte-weight',
                'uses-long-cache-ttl',
                'layout-shift-elements',
                'unsized-images',
            ]),
            'raw' => $data,
            'timestamp' => $timing,
        ];
    }

    private function normalizeCategoryScore(mixed $value): ?int {
        if (!is_numeric($value)) {
            return null;
        }
        return (int) round(((float) $value) * 100);
    }

    private function collectAuditDetails(array $audits, array $keys): array {
        $items = [];
        foreach ($keys as $key) {
            $audit = $audits[$key] ?? null;
            if (!is_array($audit) || empty($audit['title'])) {
                continue;
            }

            $items[] = [
                'id' => $key,
                'title' => (string) ($audit['title'] ?? ''),
                'description' => trim((string) ($audit['description'] ?? '')),
                'display_value' => trim((string) ($audit['displayValue'] ?? '')),
                'score' => isset($audit['score']) && is_numeric($audit['score']) ? (float) $audit['score'] : null,
                'score_display_mode' => (string) ($audit['scoreDisplayMode'] ?? ''),
            ];
        }

        return $items;
    }

    private function cacheFile(string $url, string $strategy): string {
        return $this->cacheDir . DIRECTORY_SEPARATOR . md5($strategy . '|' . strtolower(trim($url))) . '.json';
    }

    private function readCache(string $url, string $strategy): ?array {
        $file = $this->cacheFile($url, $strategy);
        if (!is_file($file) || filemtime($file) < (time() - 21600)) {
            return null;
        }

        $decoded = json_decode((string) @file_get_contents($file), true);
        return is_array($decoded) ? $decoded : null;
    }

    private function writeCache(string $url, string $strategy, array $payload): void {
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0775, true);
        }

        @file_put_contents($this->cacheFile($url, $strategy), json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
