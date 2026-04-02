<?php
namespace App\Services;

class CompetitorAnalysisService {
    private HtmlFetcher $fetcher;
    private GooglePlacesService $places;
    private string $cacheDir;

    public function __construct() {
        $this->fetcher = new HtmlFetcher();
        $this->places = new GooglePlacesService();
        $this->cacheDir = storage_path('cache/competitors');
    }

    public function analyze(string $websiteUrl, int $limit = 3): array {
        $cacheKey = md5(strtolower(trim($websiteUrl)) . '|' . $limit);
        $cached = $this->readCache($cacheKey);
        if ($cached !== null) {
            $cached['cached'] = true;
            return $cached;
        }

        $subject = $this->buildSiteProfile($websiteUrl);
        $serviceTerms = $subject['service_terms'] ?? [];
        $locationTerms = $subject['location_terms'] ?? [];

        $placesResult = $this->places->findCompetitors($websiteUrl, $serviceTerms, $locationTerms, $limit);
        $competitors = [];

        foreach (($placesResult['competitors'] ?? []) as $place) {
            $competitorUrl = trim((string) ($place['website_url'] ?? ''));
            $profile = $competitorUrl !== '' ? $this->buildSiteProfile($competitorUrl) : $this->emptyProfile();
            $competitors[] = [
                'name' => $place['name'] ?? 'Competitor',
                'domain' => $place['domain'] ?? '',
                'website_url' => $competitorUrl,
                'maps_url' => $place['maps_url'] ?? '',
                'address' => $place['address'] ?? '',
                'query' => $place['query'] ?? '',
                'score' => $place['score'] ?? null,
                'profile' => $profile,
                'differences' => $this->buildDifferences($subject, $profile),
            ];
        }

        $result = [
            'success' => !empty($competitors),
            'subject' => $subject,
            'competitors' => $competitors,
            'queries' => $placesResult['queries'] ?? [],
            'error' => $placesResult['error'] ?? '',
            'cached' => false,
        ];

        $this->writeCache($cacheKey, $result);
        return $result;
    }

    private function buildSiteProfile(string $url): array {
        $fetch = $this->fetcher->fetch($url);
        if (empty($fetch['success']) || empty($fetch['html'])) {
            return $this->emptyProfile();
        }

        $html = (string) $fetch['html'];
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        $xpath = new \DOMXPath($dom);
        $text = $this->normalizeWhitespace($dom->textContent ?? '');
        $lower = strtolower($html);

        $title = trim((string) ($xpath->query('//title')?->item(0)?->textContent ?? ''));
        $h1 = trim((string) ($xpath->query('//h1')?->item(0)?->textContent ?? ''));
        $description = '';
        $descNode = $xpath->query('//meta[@name="description"]')?->item(0);
        if ($descNode instanceof \DOMElement) {
            $description = trim($descNode->getAttribute('content'));
        }

        $source = implode(' | ', array_filter([$title, $h1, $description]));
        $serviceTerms = $this->extractServiceTerms($source, $url);
        $locationTerms = $this->extractLocationTerms($source . ' ' . $text);

        return [
            'title' => $title,
            'h1' => $h1,
            'description' => $description,
            'service_terms' => $serviceTerms,
            'location_terms' => $locationTerms,
            'title_clarity' => $title !== '' && strlen($title) >= 20 && strlen($title) <= 70,
            'has_cta' => $this->hasCta($xpath, $lower),
            'has_reviews' => $this->hasReviewSignals($lower),
            'has_gbp' => $this->hasGbpLink($xpath),
            'has_service_area_coverage' => count($locationTerms) >= 2 || str_contains($lower, 'service area') || str_contains($lower, 'areas we serve'),
        ];
    }

    private function emptyProfile(): array {
        return [
            'title' => '',
            'h1' => '',
            'description' => '',
            'service_terms' => [],
            'location_terms' => [],
            'title_clarity' => false,
            'has_cta' => false,
            'has_reviews' => false,
            'has_gbp' => false,
            'has_service_area_coverage' => false,
        ];
    }

    private function extractServiceTerms(string $source, string $url): array {
        $source = strtolower($source);
        $source = preg_replace('/[^a-z0-9\s-]/', ' ', $source);
        $source = preg_replace('/\s+/', ' ', (string) $source);
        $tokens = array_values(array_filter(explode(' ', trim((string) $source))));
        $stop = ['the','and','for','with','your','from','that','this','home','official','site','welcome','best','top','near','serving','in','of','to','a','an'];
        $terms = [];
        foreach ($tokens as $token) {
            if (strlen($token) < 4 || in_array($token, $stop, true)) {
                continue;
            }
            $terms[] = $token;
            if (count($terms) >= 4) {
                break;
            }
        }

        if (empty($terms)) {
            $host = parse_url($url, PHP_URL_HOST) ?? '';
            $host = preg_replace('/^www\./i', '', strtolower((string) $host));
            $label = preg_replace('/\.[a-z]{2,}(?:\.[a-z]{2,})?$/i', '', (string) $host);
            $label = str_replace(['-', '_'], ' ', $label);
            if ($label !== '') {
                $terms[] = trim($label);
            }
        }

        return array_values(array_unique($terms));
    }

    private function extractLocationTerms(string $text): array {
        $terms = [];
        foreach ([
            '/\b([A-Z][a-z]+(?:\s+[A-Z][a-z]+){0,2},\s*[A-Z]{2})\b/u',
            '/\b(?:in|near|serving|around|throughout)\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+){0,2})(?:,\s*[A-Z]{2})?/u',
        ] as $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                foreach (($matches[1] ?? []) as $match) {
                    $match = $this->normalizeWhitespace((string) $match);
                    if ($match !== '' && strlen($match) >= 4) {
                        $terms[$match] = true;
                    }
                }
            }
        }

        return array_slice(array_keys($terms), 0, 4);
    }

    private function hasCta(\DOMXPath $xpath, string $html): bool {
        $selectors = [
            '//a[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"contact")]',
            '//a[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"quote")]',
            '//a[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"book")]',
            '//a[starts-with(@href,"tel:")]',
            '//button[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"contact")]',
            '//button[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"quote")]',
        ];

        foreach ($selectors as $selector) {
            if (($xpath->query($selector)?->length ?? 0) > 0) {
                return true;
            }
        }

        return str_contains($html, 'get started') || str_contains($html, 'request a quote');
    }

    private function hasReviewSignals(string $html): bool {
        foreach (['testimonial', 'review', 'reviews', 'rated', 'stars', 'google reviews'] as $term) {
            if (str_contains($html, $term)) {
                return true;
            }
        }
        return false;
    }

    private function hasGbpLink(\DOMXPath $xpath): bool {
        foreach (($xpath->query('//a[@href]') ?: []) as $link) {
            if (!$link instanceof \DOMElement) {
                continue;
            }
            $href = strtolower(trim($link->getAttribute('href')));
            if (str_contains($href, 'g.page/')
                || str_contains($href, 'google.com/maps')
                || str_contains($href, 'maps.app.goo.gl')
                || str_contains($href, 'goo.gl/maps')) {
                return true;
            }
        }
        return false;
    }

    private function buildDifferences(array $subject, array $competitor): array {
        $differences = [];

        if (($competitor['title_clarity'] ?? false) && !($subject['title_clarity'] ?? false)) {
            $differences[] = 'Competitor title looks clearer and more search-ready.';
        }
        if (($competitor['has_cta'] ?? false) && !($subject['has_cta'] ?? false)) {
            $differences[] = 'Competitor shows a stronger visible CTA on the page.';
        }
        if (($competitor['has_reviews'] ?? false) && !($subject['has_reviews'] ?? false)) {
            $differences[] = 'Competitor shows stronger review or testimonial signals.';
        }
        if (($competitor['has_gbp'] ?? false) && !($subject['has_gbp'] ?? false)) {
            $differences[] = 'Competitor links to Google Maps or a Google Business Profile more clearly.';
        }
        if (($competitor['has_service_area_coverage'] ?? false) && !($subject['has_service_area_coverage'] ?? false)) {
            $differences[] = 'Competitor appears to show broader service-area coverage.';
        }

        return array_slice($differences, 0, 4);
    }

    private function normalizeWhitespace(string $value): string {
        return trim((string) preg_replace('/\s+/', ' ', $value));
    }

    private function cacheFile(string $cacheKey): string {
        return $this->cacheDir . DIRECTORY_SEPARATOR . $cacheKey . '.json';
    }

    private function readCache(string $cacheKey): ?array {
        $file = $this->cacheFile($cacheKey);
        if (!is_file($file) || filemtime($file) < (time() - 43200)) {
            return null;
        }

        $decoded = json_decode((string) @file_get_contents($file), true);
        return is_array($decoded) ? $decoded : null;
    }

    private function writeCache(string $cacheKey, array $payload): void {
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0775, true);
        }

        @file_put_contents($this->cacheFile($cacheKey), json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
