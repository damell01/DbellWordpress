<?php
namespace App\Services;

use App\Models\Setting;

class GooglePlacesService {
    private string $apiKey = '';
    private bool $enabled = true;

    public function __construct() {
        $this->apiKey = (string) env('GOOGLE_MAPS_API_KEY', '');

        try {
            $settings = new Setting();
            $this->enabled = (string) $settings->get('enable_google_places_lookup', '1') !== '0';
            if ($this->apiKey === '') {
                $this->apiKey = (string) $settings->get('google_maps_api_key', '');
            }
        } catch (\Throwable $e) {
            $this->enabled = true;
        }
    }

    public function findBusinessProfile(string $websiteUrl, string|array $businessName = ''): array {
        if (!$this->enabled) {
            return ['success' => false, 'error' => 'Google Places lookup disabled in settings.'];
        }

        if ($this->apiKey === '') {
            return ['success' => false, 'error' => 'No Google Maps API key configured.'];
        }

        $domain = parse_url($websiteUrl, PHP_URL_HOST) ?? '';
        $domain = preg_replace('/^www\./i', '', strtolower($domain));
        $queries = $this->buildQueryCandidates($domain, $businessName);

        if (empty($queries)) {
            return ['success' => false, 'error' => 'No search query available.'];
        }

        $bestMatch = null;
        $bestScore = 0.0;
        $apiErrors = [];

        foreach ($queries as $query) {
            $response = $this->searchText($query);
            if (empty($response['success'])) {
                $apiErrors[] = $response['error'] ?? 'Unknown API error.';
                continue;
            }

            $places = $response['places'] ?? [];
            foreach ($places as $place) {
                $score = $this->scorePlaceMatch($place, $domain, $businessName);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $place;
                }
            }
        }

        if ($bestMatch !== null && $bestScore >= 0.58) {
            return [
                'success' => true,
                'match' => $bestMatch,
                'confidence' => round($bestScore, 3),
                'queries' => $queries,
            ];
        }

        if ($bestMatch !== null && $bestScore >= 0.4) {
            return [
                'success' => true,
                'match' => $bestMatch,
                'confidence' => round($bestScore, 3),
                'queries' => $queries,
                'warning' => 'Low-confidence business profile match.',
            ];
        }

        return [
            'success' => false,
            'error' => !empty($apiErrors) ? implode(' | ', array_unique($apiErrors)) : 'No matching place found.',
            'queries' => $queries,
        ];
    }

    private function searchText(string $query): array {
        $payload = json_encode([
            'textQuery' => $query,
            'pageSize' => 5,
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://places.googleapis.com/v1/places:searchText',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'X-Goog-Api-Key: ' . $this->apiKey,
                'X-Goog-FieldMask: places.displayName,places.formattedAddress,places.googleMapsUri,places.websiteUri,places.businessStatus,places.types',
            ],
        ]);

        $body = curl_exec($ch);
        $error = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error || $code >= 400 || !$body) {
            return ['success' => false, 'error' => $error ?: ('HTTP ' . $code)];
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            return ['success' => false, 'error' => 'Invalid API response.'];
        }

        return ['success' => true, 'places' => $data['places'] ?? []];
    }

    private function buildQueryCandidates(string $domain, string|array $businessName): array {
        $names = is_array($businessName) ? $businessName : [$businessName];
        $candidates = [];

        foreach ($names as $name) {
            $normalized = $this->normalizeBusinessName((string) $name);
            if ($normalized !== '') {
                $candidates[] = $normalized;
                if ($domain !== '') {
                    $candidates[] = $normalized . ' ' . $domain;
                }
            }
        }

        $domainLabel = $this->domainLabel($domain);
        if ($domainLabel !== '') {
            $candidates[] = $domainLabel;
        }
        if ($domain !== '') {
            $candidates[] = $domain;
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    private function scorePlaceMatch(array $place, string $domain, string|array $businessName): float {
        $score = 0.0;
        $website = strtolower((string) ($place['websiteUri'] ?? ''));
        $displayName = $this->normalizeBusinessName((string) (($place['displayName']['text'] ?? '')));
        $status = strtolower((string) ($place['businessStatus'] ?? ''));
        $types = array_map('strtolower', $place['types'] ?? []);
        $names = is_array($businessName) ? $businessName : [$businessName];

        if ($domain !== '' && $website !== '') {
            $websiteHost = parse_url($website, PHP_URL_HOST) ?? '';
            $websiteHost = preg_replace('/^www\./i', '', strtolower((string) $websiteHost));
            if ($websiteHost === $domain) {
                $score += 0.65;
            } elseif ($websiteHost !== '' && (str_contains($websiteHost, $domain) || str_contains($domain, $websiteHost))) {
                $score += 0.45;
            }
        }

        foreach ($names as $name) {
            $normalized = $this->normalizeBusinessName((string) $name);
            if ($normalized === '' || $displayName === '') {
                continue;
            }

            similar_text($normalized, $displayName, $percent);
            $score += ($percent / 100) * 0.5;

            if ($normalized === $displayName) {
                $score += 0.25;
            } elseif (str_contains($displayName, $normalized) || str_contains($normalized, $displayName)) {
                $score += 0.15;
            }
        }

        if ($status === 'operational') {
            $score += 0.05;
        }

        if (in_array('point_of_interest', $types, true) || in_array('establishment', $types, true)) {
            $score += 0.03;
        }

        return $score;
    }

    private function normalizeBusinessName(string $value): string {
        $value = strtolower(trim($value));
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/https?:\/\/|www\./', '', $value);
        $value = preg_replace('/\.[a-z]{2,}(?:\.[a-z]{2,})?$/i', '', $value);
        $value = preg_replace('/\b(home|homepage|welcome|official site|website|site)\b/i', '', $value);
        $value = preg_replace('/\b(llc|inc|co|company|corp|corporation|ltd|pllc)\b/i', '', $value);
        $value = preg_replace('/[|:\/_-]+/', ' ', $value);
        $value = preg_replace('/[^a-z0-9\s&]/', '', $value);
        $value = preg_replace('/\s+/', ' ', $value);
        return trim($value);
    }

    private function domainLabel(string $domain): string {
        if ($domain === '') {
            return '';
        }

        $labels = explode('.', $domain);
        $root = $labels[0] ?? '';
        $root = preg_replace('/[-_]+/', ' ', $root);
        return trim($root);
    }
}
