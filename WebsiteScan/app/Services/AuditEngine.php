<?php
namespace App\Services;

/**
 * Core audit engine – runs all checks and returns structured results.
 */
class AuditEngine {
    private HtmlFetcher $fetcher;
    private UrlNormalizer $urlNormalizer;
    private ?PageSpeedService $pageSpeedService = null;
    private ?GooglePlacesService $googlePlacesService = null;
    private \DOMDocument $dom;
    private \DOMXPath $xpath;
    private array $fetchResult = [];
    private array $pageProfiles = [];
    private array $pageSpeedResults = [];
    private string $html = '';
    private string $url  = '';
    private array $issues = [];

    public function __construct() {
        $this->fetcher       = new HtmlFetcher();
        $this->urlNormalizer = new UrlNormalizer();
        $this->pageSpeedService = new PageSpeedService();
        $this->googlePlacesService = new GooglePlacesService();
    }

    public function run(string $url): array {
        $this->url    = $url;
        $this->issues = [];
        $this->pageSpeedResults = [];

        $fetch = $this->fetcher->fetch($url);
        $this->fetchResult = $fetch;

        if (!$fetch['success']) {
            $this->addIssue('technical', 'critical', 'PAGE_FETCH_FAILED',
                'Website Could Not Be Reached',
                "We were unable to load your website. Error: " . ($fetch['error'] ?: "HTTP {$fetch['http_code']}"),
                'If visitors cannot reach your site, you lose all potential business.',
                'Check your hosting status, domain configuration, and SSL certificate.',
                'Potential revenue loss from unreachable website.'
            );
            return $this->buildResult($fetch);
        }

        $this->html = $fetch['html'];
        $this->loadDom($this->html);
        $this->pageProfiles = $this->buildPageProfiles();

        // Run all check groups
        $this->runSeoChecks();
        $this->runAccessibilityChecks();
        $this->runConversionChecks();
        $this->runTechnicalChecks();
        $this->runLocalChecks();

        return $this->buildResult($fetch);
    }

    // ─────────────────────────── DOM HELPERS ───────────────────────────────

    private function loadDom(string $html): void {
        $this->dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $this->dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        $this->xpath = new \DOMXPath($this->dom);
    }

    private function xpathQuery(string $expr): \DOMNodeList {
        return $this->xpath->query($expr) ?: new \DOMNodeList();
    }

    private function getText(string $expr): string {
        $nodes = $this->xpathQuery($expr);
        return $nodes->length > 0 ? trim($nodes->item(0)->textContent ?? '') : '';
    }

    private function getAttribute(string $expr, string $attr): string {
        $nodes = $this->xpathQuery($expr);
        if ($nodes->length > 0 && $nodes->item(0) instanceof \DOMElement) {
            return trim($nodes->item(0)->getAttribute($attr));
        }
        return '';
    }

    private function addIssue(
        string $category,
        string $severity,
        string $code,
        string $title,
        string $explanation,
        string $why,
        string $howToFix,
        string $businessImpact = '',
        string $detectedValue  = ''
    ): void {
        $this->issues[] = [
            'category'       => $category,
            'severity'       => $severity,
            'code'           => $code,
            'title'          => $title,
            'explanation'    => $explanation,
            'why_it_matters' => $why,
            'how_to_fix'     => $howToFix,
            'business_impact'=> $businessImpact,
            'detected_value' => $detectedValue,
        ];
    }

    private function normalizeWhitespace(string $value): string {
        return trim(preg_replace('/\s+/', ' ', $value));
    }

    private function limit(string $value, int $length = 160): string {
        $value = $this->normalizeWhitespace($value);
        if ($value === '') {
            return '';
        }
        return function_exists('mb_strimwidth')
            ? mb_strimwidth($value, 0, $length, '...', 'UTF-8')
            : (strlen($value) > $length ? substr($value, 0, $length - 3) . '...' : $value);
    }

    private function lower(string $value): string {
        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    }

    private function nodeSummary(\DOMNode $node): string {
        if (!$node instanceof \DOMElement) {
            return $this->limit($node->textContent ?? '');
        }

        $parts = [strtolower($node->tagName)];
        foreach (['id', 'name', 'type', 'href', 'src', 'action', 'class'] as $attr) {
            $val = trim($node->getAttribute($attr));
            if ($val !== '') {
                $parts[] = $attr . '=' . $this->limit($val, 80);
            }
        }

        $text = $this->normalizeWhitespace($node->textContent ?? '');
        if ($text !== '') {
            $parts[] = 'text="' . $this->limit($text, 90) . '"';
        }

        return implode(', ', $parts);
    }

    private function summarizeNodes(iterable $nodes, int $limit = 3): string {
        $items = [];
        $count = 0;
        foreach ($nodes as $node) {
            $items[] = $this->nodeSummary($node);
            $count++;
            if ($count >= $limit) {
                break;
            }
        }
        return implode(' | ', array_filter($items));
    }

    private function fetchCached(string $url): array {
        static $cache = [];
        if (!isset($cache[$url])) {
            $cache[$url] = $this->fetcher->fetch($url);
        }
        return $cache[$url];
    }

    private function findInternalLinks(array $keywords, int $limit = 2): array {
        $matches = [];
        $seen = [];
        $links = $this->xpathQuery('//a[@href]');

        foreach ($links as $link) {
            if (!$link instanceof \DOMElement) {
                continue;
            }

            $href = trim($link->getAttribute('href'));
            $resolved = $this->urlNormalizer->resolveLink($this->url, $href);
            if (!$resolved || !$this->urlNormalizer->isSameDomain($this->url, $resolved) || $resolved === $this->url) {
                continue;
            }

            $haystack = $this->lower($href . ' ' . ($link->textContent ?? ''));
            foreach ($keywords as $keyword) {
                if (str_contains($haystack, $keyword) && !isset($seen[$resolved])) {
                    $seen[$resolved] = true;
                    $matches[] = $resolved;
                    break;
                }
            }

            if (count($matches) >= $limit) {
                break;
            }
        }

        return $matches;
    }

    private function analyzePageForForms(string $url, string $html): array {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        $xpath = new \DOMXPath($dom);
        $normalizedHtml = $this->lower($html);

        $forms = $xpath->query('//form');
        $contactInputs = $xpath->query('//input[@type="email" or @type="tel" or contains(translate(@name,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"email") or contains(translate(@name,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"phone")]');
        $messageFields = $xpath->query('//textarea | //input[contains(translate(@name,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"message")]');
        $textInputs = $xpath->query('//input[not(@type) or @type="text" or @type="email" or @type="tel" or @type="search"]');
        $submitButtons = $xpath->query('//form//button[@type="submit"] | //form//input[@type="submit"] | //button[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"submit")] | //button[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"send")] | //button[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"contact")] | //button[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"quote")]');
        $formLikeContainers = $xpath->query('//*[contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"contact-form") or contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"forminator") or contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"wpforms") or contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"gravity") or contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"fluentform") or contains(translate(@id,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"contact-form")]');
        $embeddedFormFrames = $xpath->query('//iframe[contains(@src,"typeform") or contains(@src,"jotform") or contains(@src,"google.com/forms") or contains(@src,"calendly") or contains(@src,"hubspot") or contains(@src,"wufoo") or contains(@src,"formstack")]');
        $contactLinks = $xpath->query('//a[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"contact us") or contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"get a quote") or contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"book now") or starts-with(@href, "mailto:") or starts-with(@href, "tel:")]');
        $captchaSignals = str_contains($normalizedHtml, 'captcha') || str_contains($normalizedHtml, 'g-recaptcha') || str_contains($normalizedHtml, 'hcaptcha');
        $labelText = $this->lower($forms ? $dom->textContent ?? '' : $html);
        $labelSignalCount = 0;
        foreach (['name', 'email', 'subject', 'message', 'phone'] as $signal) {
            if (str_contains($labelText, $signal)) {
                $labelSignalCount++;
            }
        }
        $hasFormActionSignals = str_contains($normalizedHtml, 'formsubmit') || str_contains($normalizedHtml, 'forminator') || str_contains($normalizedHtml, 'wpforms') || str_contains($normalizedHtml, 'gravityforms') || str_contains($normalizedHtml, 'hs-form') || str_contains($normalizedHtml, 'hubspot') || str_contains($normalizedHtml, 'wpcf7') || str_contains($normalizedHtml, 'fluentform') || str_contains($normalizedHtml, 'elementor-form');
        $hasLikelyContactForm = (($forms?->length ?? 0) > 0 && ((($contactInputs?->length ?? 0) > 0) || (($messageFields?->length ?? 0) > 0) || (($textInputs?->length ?? 0) >= 2 && ($submitButtons?->length ?? 0) > 0)))
            || (($embeddedFormFrames?->length ?? 0) > 0)
            || (($formLikeContainers?->length ?? 0) > 0)
            || ((($textInputs?->length ?? 0) >= 2 || ($messageFields?->length ?? 0) > 0) && $labelSignalCount >= 3 && (($submitButtons?->length ?? 0) > 0 || $captchaSignals))
            || $hasFormActionSignals;

        return [
            'url' => $url,
            'forms' => $forms?->length ?? 0,
            'contact_inputs' => $contactInputs?->length ?? 0,
            'message_fields' => $messageFields?->length ?? 0,
            'text_inputs' => $textInputs?->length ?? 0,
            'submit_buttons' => $submitButtons?->length ?? 0,
            'embedded_forms' => $embeddedFormFrames?->length ?? 0,
            'contact_links' => $contactLinks?->length ?? 0,
            'form_like_blocks' => $formLikeContainers?->length ?? 0,
            'label_signals' => $labelSignalCount,
            'captcha_signals' => $captchaSignals ? 1 : 0,
            'has_contact_form' => $hasLikelyContactForm,
            'widgets' => $this->detectFormWidgets($html),
            'examples' => $forms ? $this->summarizeNodes($forms, 3) : '',
        ];
    }

    private function gatherContactFormEvidence(): array {
        $pages = array_merge([$this->url], $this->findInternalLinks(['contact', 'quote', 'consult', 'book', 'appointment'], 2));
        $results = [];

        foreach ($pages as $pageUrl) {
            $fetch = $this->fetchCached($pageUrl);
            if (!$fetch['success'] || empty($fetch['html'])) {
                continue;
            }
            $results[] = $this->analyzePageForForms($pageUrl, $fetch['html']);
        }

        return $results;
    }

    private function formatContactFormEvidence(array $evidence): string {
        $parts = [];
        foreach (array_slice($evidence, 0, 3) as $item) {
            $path = parse_url($item['url'], PHP_URL_PATH) ?: '/';
            $segment = $path . ': forms=' . $item['forms'] . ', contact-fields=' . $item['contact_inputs'] . ', message-fields=' . $item['message_fields'];
            if (!empty($item['submit_buttons'])) {
                $segment .= ', submit-buttons=' . $item['submit_buttons'];
            }
            if (!empty($item['embedded_forms'])) {
                $segment .= ', embeds=' . $item['embedded_forms'];
            }
            if (!empty($item['label_signals'])) {
                $segment .= ', labels=' . $item['label_signals'];
            }
            if (!empty($item['captcha_signals'])) {
                $segment .= ', captcha';
            }
            if (!empty($item['widgets'])) {
                $segment .= ', widgets=' . implode(',', array_slice($item['widgets'], 0, 3));
            }
            $parts[] = $segment;
        }
        return implode(' | ', $parts);
    }

    private function countWords(string $text): int {
        $matches = [];
        preg_match_all('/\b[\p{L}\p{N}\']+\b/u', $this->normalizeWhitespace($text), $matches);
        return count($matches[0] ?? []);
    }

    private function analyzeSchemaMarkup(string $html): array {
        $types = [];
        $errors = [];
        $count = 0;

        if (preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches)) {
            foreach ($matches[1] as $rawBlock) {
                $block = trim((string) $rawBlock);
                if ($block === '') {
                    continue;
                }
                $count++;
                $decoded = json_decode($block, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $errors[] = 'Invalid JSON-LD block';
                    continue;
                }

                $walk = function ($node) use (&$walk, &$types): void {
                    if (!is_array($node)) {
                        return;
                    }

                    if (isset($node['@type'])) {
                        $nodeTypes = is_array($node['@type']) ? $node['@type'] : [$node['@type']];
                        foreach ($nodeTypes as $type) {
                            $type = trim((string) $type);
                            if ($type !== '') {
                                $types[] = $type;
                            }
                        }
                    }

                    foreach ($node as $child) {
                        if (is_array($child)) {
                            $walk($child);
                        }
                    }
                };

                $walk($decoded);
            }
        }

        if (preg_match_all('/itemtype=["\'][^"\']*schema\.org\/([^"\']+)["\']/i', $html, $microMatches)) {
            foreach ($microMatches[1] as $type) {
                $types[] = trim((string) $type);
            }
        }

        return [
            'types' => array_values(array_unique(array_filter($types))),
            'errors' => array_values(array_unique(array_filter($errors))),
            'count' => $count,
        ];
    }

    private function extractSchemaTypesFromHtml(string $html): array {
        return $this->analyzeSchemaMarkup($html)['types'] ?? [];
    }

    private function inferPageType(string $path, string $html): string {
        $haystack = $this->lower($path . ' ' . $html);
        if ($path === '/' || $path === '') {
            return 'home';
        }
        if (str_contains($haystack, 'contact') || str_contains($haystack, 'book') || str_contains($haystack, 'quote') || str_contains($haystack, 'appointment')) {
            return 'contact';
        }
        if (str_contains($haystack, 'service') || str_contains($haystack, 'seo') || str_contains($haystack, 'webdesign') || str_contains($haystack, 'software') || str_contains($haystack, 'automation') || str_contains($haystack, 'marketing')) {
            return 'service';
        }
        if (str_contains($haystack, 'about')) {
            return 'about';
        }
        return 'general';
    }

    private function pageTypeLabel(string $type): string {
        return match ($type) {
            'home' => 'Homepage',
            'contact' => 'Contact Page',
            'service' => 'Service Page',
            'about' => 'About Page',
            default => 'Page',
        };
    }

    private function isKeySeoPage(array $profile): bool {
        return in_array((string) ($profile['page_type'] ?? 'general'), ['home', 'contact', 'service'], true);
    }

    private function pageEvidence(array $profile): string {
        return 'page=' . ($profile['path'] ?? '/') . ' | type=' . ($profile['page_type'] ?? 'general');
    }

    private function extractKeywordTerms(string $text): array {
        $text = $this->lower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', ' ', $text);
        $parts = preg_split('/\s+/', (string) $text) ?: [];
        $stop = ['the','and','for','with','your','from','that','this','have','you','our','are','but','not','all','can','too','use','one','page','home','about','contact','service','services','more','best','into'];
        $terms = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '' || strlen($part) < 4 || in_array($part, $stop, true) || is_numeric($part)) {
                continue;
            }
            $terms[$part] = true;
            if (count($terms) >= 6) {
                break;
            }
        }
        return array_keys($terms);
    }

    private function extractLocationTerms(string $text): array {
        $terms = [];
        $patterns = [
            '/\b(?:in|near|serving|around|throughout)\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+){0,2})(?:,\s*[A-Z]{2})?/u',
            '/\b([A-Z][a-z]+(?:\s+[A-Z][a-z]+){0,2},\s*[A-Z]{2})\b/u',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                foreach ($matches[1] as $match) {
                    $match = $this->normalizeWhitespace((string) $match);
                    if ($match !== '' && strlen($match) >= 4) {
                        $terms[$match] = true;
                    }
                }
            }
        }

        return array_keys($terms);
    }

    private function profileLocationTerms(array $profile): array {
        $source = implode(' ', array_filter([
            $profile['title'] ?? '',
            $profile['description'] ?? '',
            $profile['h1_text'] ?? '',
        ]));

        return $this->extractLocationTerms($source);
    }

    private function profileServiceTerms(array $profile): array {
        $source = implode(' ', array_filter([
            $profile['title'] ?? '',
            $profile['description'] ?? '',
            $profile['h1_text'] ?? '',
            $profile['path'] ?? '',
        ]));

        return $this->extractKeywordTerms($source);
    }

    private function isLikelyLocalLandingPage(array $profile): bool {
        return !empty($this->profileLocationTerms($profile)) && !empty($this->profileServiceTerms($profile));
    }

    private function extractPrimaryPhone(string $html): string {
        if (preg_match('/(?:\+?\d[\d\s().-]{7,}\d)/', $html, $match)) {
            return $this->normalizeWhitespace((string) $match[0]);
        }
        return '';
    }

    private function extractPrimaryEmail(string $html): string {
        if (preg_match('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', $html, $match)) {
            return strtolower(trim((string) $match[0]));
        }
        return '';
    }

    private function extractAddressSnippet(string $html): string {
        if (preg_match('/\d+\s+[A-Za-z0-9.\s]+(?:street|st|avenue|ave|road|rd|drive|dr|blvd|boulevard|way|lane|ln)[^<,\n]*/i', $html, $match)) {
            return $this->limit((string) $match[0], 120);
        }
        return '';
    }

    private function buildPageProfiles(): array {
        $profiles = [];
        $pages = array_merge(
            [$this->url],
            $this->findInternalLinks(['contact', 'about', 'service', 'services', 'location', 'locations', 'book', 'appointment', 'quote', 'estimate', 'seo', 'marketing', 'software', 'automation', 'webdesign'], 6)
        );

        $pages = array_values(array_unique($pages));
        $batch = $this->fetcher->fetchMany($pages);

        foreach ($pages as $pageUrl) {
            $fetch = $batch[$pageUrl] ?? $this->fetchCached($pageUrl);
            if (!$fetch['success'] || empty($fetch['html'])) {
                continue;
            }

            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML('<?xml encoding="UTF-8">' . $fetch['html']);
            libxml_clear_errors();
            $xpath = new \DOMXPath($dom);
            $html = (string) $fetch['html'];
            $bodyText = $this->lower($html);
            $visibleText = $this->normalizeWhitespace($dom->textContent ?? '');
            $title = trim((string) ($xpath->query('//title')?->item(0)?->textContent ?? ''));
            $description = '';
            $descNode = $xpath->query('//meta[@name="description"]')?->item(0);
            if ($descNode instanceof \DOMElement) {
                $description = trim($descNode->getAttribute('content'));
            }
            $h1Nodes = $xpath->query('//h1');
            $h1Text = trim((string) ($h1Nodes?->item(0)?->textContent ?? ''));
            $canonical = '';
            $canonicalNode = $xpath->query('//link[@rel="canonical"]')?->item(0);
            if ($canonicalNode instanceof \DOMElement) {
                $canonical = trim($canonicalNode->getAttribute('href'));
            }
            $robotsMeta = '';
            $robotsNode = $xpath->query('//meta[@name="robots"]')?->item(0);
            if ($robotsNode instanceof \DOMElement) {
                $robotsMeta = trim($robotsNode->getAttribute('content'));
            }
            $internalLinks = [];
            foreach (($xpath->query('//a[@href]') ?: []) as $linkNode) {
                if (!$linkNode instanceof \DOMElement) {
                    continue;
                }
                $resolved = $this->urlNormalizer->resolveLink($fetch['final_url'] ?? $pageUrl, trim($linkNode->getAttribute('href')));
                if ($resolved && $this->urlNormalizer->isSameDomain($this->url, $resolved)) {
                    $internalLinks[$resolved] = true;
                }
            }
            $pagePath = parse_url($pageUrl, PHP_URL_PATH) ?: '/';
            $pageType = $this->inferPageType($pagePath, $html);
            $schemaAnalysis = $this->analyzeSchemaMarkup($html);
            $heroNode = $xpath->query('//header | //section[contains(@class,"hero")] | //div[contains(@class,"hero")] | //main//*[self::h1 or self::h2][1]')?->item(0);
            $heroText = $heroNode ? $this->normalizeWhitespace($heroNode->textContent ?? '') : '';
            $heroWordCount = $this->countWords($heroText);
            $offerKeywords = ['free quote', 'estimate', 'book', 'schedule', 'consultation', 'call now', 'get started', 'request a quote', 'pricing'];
            $offerSignalCount = 0;
            foreach ($offerKeywords as $keyword) {
                if (str_contains($bodyText, $keyword)) {
                    $offerSignalCount++;
                }
            }
            $reviewSignalCount = 0;
            foreach (['testimonial', 'review', 'reviews', 'rated', 'stars', 'google reviews', 'customer stories'] as $keyword) {
                if (str_contains($bodyText, $keyword)) {
                    $reviewSignalCount++;
                }
            }
            $trustSignalCount = 0;
            foreach (['certified', 'accredited', 'award', 'guarantee', 'licensed', 'insured', 'trusted', 'years in business'] as $keyword) {
                if (str_contains($bodyText, $keyword)) {
                    $trustSignalCount++;
                }
            }
            $aboveFoldCtaCount = 0;
            foreach ([
                '//header//a[contains(@class,"btn")]',
                '//header//button[contains(@class,"btn")]',
                '//header//a[starts-with(@href,"tel:")]',
                '//section[contains(@class,"hero")]//a',
                '//section[contains(@class,"hero")]//button',
                '//div[contains(@class,"hero")]//a',
                '//div[contains(@class,"hero")]//button',
            ] as $selector) {
                $aboveFoldCtaCount += (int) ($xpath->query($selector)?->length ?? 0);
            }
            $formFieldCount = (int) ($xpath->query('//form//input | //form//select | //form//textarea')?->length ?? 0);
            $requiredFieldCount = (int) ($xpath->query('//form//*[@required]')?->length ?? 0);

            $ctaSelectors = [
                '//a[contains(@class,"btn")]',
                '//button[contains(@class,"btn")]',
                '//*[@role="button"]',
                '//a[starts-with(@href,"tel:")]',
                '//a[starts-with(@href,"mailto:")]',
                '//button[@type="submit"]',
                '//input[@type="submit"]',
                '//a[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"contact")]',
                '//a[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"quote")]',
                '//a[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"estimate")]',
                '//a[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"book")]',
                '//a[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"schedule")]',
                '//a[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"consult")]',
                '//a[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"call")]',
                '//a[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"get started")]',
                '//a[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"learn more")]',
                '//button[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"submit")]',
                '//button[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"contact")]',
                '//button[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"quote")]',
                '//button[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"book")]',
                '//button[contains(translate(normalize-space(.),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"schedule")]',
            ];
            $ctaNodes = [];
            foreach ($ctaSelectors as $selector) {
                foreach (($xpath->query($selector) ?: []) as $ctaNode) {
                    if (!$ctaNode instanceof \DOMElement) {
                        continue;
                    }
                    $ctaNodes[spl_object_hash($ctaNode)] = $ctaNode;
                }
            }
            $ctaCount = count($ctaNodes);

            $profiles[] = [
                'url' => $pageUrl,
                'path' => $pagePath,
                'page_type' => $pageType,
                'html' => $html,
                'title' => $title,
                'description' => $description,
                'h1_text' => $h1Text,
                'h1_count' => (int) ($h1Nodes?->length ?? 0),
                'canonical' => $canonical,
                'robots_meta' => $robotsMeta,
                'word_count' => $this->countWords($visibleText),
                'internal_link_count' => count($internalLinks),
                'schema_types' => $this->extractSchemaTypesFromHtml($html),
                'location_terms' => $this->extractLocationTerms(implode(' ', array_filter([$title, $description, $h1Text]))),
                'service_terms' => $this->extractKeywordTerms(implode(' ', array_filter([$title, $description, $h1Text, $pagePath]))),
                'hero_text' => $heroText,
                'hero_word_count' => $heroWordCount,
                'offer_signal_count' => $offerSignalCount,
                'review_signal_count' => $reviewSignalCount,
                'trust_signal_count' => $trustSignalCount,
                'above_fold_cta_count' => $aboveFoldCtaCount,
                'forms' => (int) ($xpath->query('//form')?->length ?? 0),
                'form_field_count' => $formFieldCount,
                'required_field_count' => $requiredFieldCount,
                'widgets' => $this->detectFormWidgets($html),
                'contact_inputs' => (int) ($xpath->query('//input[@type="email" or @type="tel" or contains(translate(@name,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"email") or contains(translate(@name,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"phone")]')?->length ?? 0),
                'message_fields' => (int) ($xpath->query('//textarea | //input[contains(translate(@name,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"message")]')?->length ?? 0),
                'has_phone' => (bool) preg_match('/(\+?[\d\s\-().]{7,}\d)/', $html) || (($xpath->query('//a[starts-with(@href, "tel:")]')?->length ?? 0) > 0),
                'has_email' => (bool) preg_match('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', $html) || (($xpath->query('//a[starts-with(@href, "mailto:")]')?->length ?? 0) > 0),
                'primary_phone' => $this->extractPrimaryPhone($html),
                'primary_email' => $this->extractPrimaryEmail($html),
                'address_snippet' => $this->extractAddressSnippet($html),
                'cta_count' => $ctaCount,
                'has_address' => (bool) preg_match('/\d+\s+[A-Za-z]+\s+(?:street|st|avenue|ave|road|rd|drive|dr|blvd|boulevard|way|lane|ln)/i', $html)
                    || (bool) preg_match('/p\.?\s*o\.?\s*box\s+\d+/i', $html)
                    || (bool) preg_match('/\b[a-z ]+,\s*[A-Z]{2}\s+\d{5}(?:-\d{4})?\b/', $html)
                    || str_contains($bodyText, ' zip ')
                    || str_contains($bodyText, ' postal ')
                    || (($xpath->query('//*[contains(@class,"address") or contains(@itemprop,"address")]')?->length ?? 0) > 0),
                'has_map' => str_contains($bodyText, 'maps.google')
                    || str_contains($bodyText, 'google.com/maps')
                    || str_contains($bodyText, 'maps/embed')
                    || str_contains($bodyText, 'maps.app.goo.gl')
                    || (($xpath->query('//iframe[contains(@src,"maps")]')?->length ?? 0) > 0),
                'has_hours' => str_contains($bodyText, 'monday')
                    || str_contains($bodyText, 'business hours')
                    || str_contains($bodyText, 'open:')
                    || str_contains($bodyText, 'hours of operation')
                    || (($xpath->query('//*[contains(@itemprop,"openingHours")]')?->length ?? 0) > 0),
                'schema_count' => (int) ($schemaAnalysis['count'] ?? 0),
                'schema_errors' => $schemaAnalysis['errors'] ?? [],
            ];
        }

        return $profiles;
    }

    private function detectFormWidgets(string $html): array {
        $widgets = [];
        foreach (['wpforms', 'gravityforms', 'contact-form-7', 'hubspot', 'typeform', 'jotform', 'forminator', 'ninja-forms', 'formstack', 'wufoo', 'calendly', 'fluentform', 'elementor-form', 'hs-form', 'google forms', 'formidable'] as $signal) {
            if (str_contains($this->lower($html), $signal)) {
                $widgets[] = $signal;
            }
        }
        return array_values(array_unique($widgets));
    }

    private function matchingPagePaths(callable $predicate, int $limit = 4): array {
        $matches = [];
        foreach ($this->pageProfiles as $profile) {
            if ($predicate($profile)) {
                $matches[] = $profile['path'] ?? '/';
            }
            if (count($matches) >= $limit) {
                break;
            }
        }
        return $matches;
    }

    private function formatPageEvidence(string $label, array $paths): string {
        if (empty($paths)) {
            return '';
        }
        return $label . ': ' . implode(', ', array_unique($paths));
    }

    private function pagePathForUrl(string $url): string {
        return parse_url($url, PHP_URL_PATH) ?: '/';
    }

    private function zoneForNode(\DOMNode $node): string {
        $current = $node instanceof \DOMElement ? $node : $node->parentNode;
        while ($current instanceof \DOMElement) {
            $tag = strtolower($current->tagName);
            $tokens = $this->lower(
                $tag . ' ' .
                $current->getAttribute('id') . ' ' .
                $current->getAttribute('class') . ' ' .
                $current->getAttribute('role')
            );

            if (str_contains($tokens, 'header') || $tag === 'header') {
                return 'header';
            }
            if (str_contains($tokens, 'nav') || $tag === 'nav') {
                return 'nav';
            }
            if (str_contains($tokens, 'hero') || str_contains($tokens, 'banner')) {
                return 'hero';
            }
            if ($tag === 'form' || str_contains($tokens, 'form') || str_contains($tokens, 'contact')) {
                return 'form';
            }
            if ($tag === 'footer' || str_contains($tokens, 'footer')) {
                return 'footer';
            }
            if ($tag === 'aside' || str_contains($tokens, 'sidebar')) {
                return 'sidebar';
            }
            if ($tag === 'main' || str_contains($tokens, 'main') || str_contains($tokens, 'content')) {
                return 'main';
            }

            $current = $current->parentNode;
        }

        return 'main';
    }

    private function locateNode(\DOMNode $node, ?string $pageUrl = null): string {
        $page = $this->pagePathForUrl($pageUrl ?: $this->url);
        $zone = $this->zoneForNode($node);
        return 'page=' . $page . ' | zone=' . $zone . ' | element=' . $this->nodeSummary($node);
    }

    private function locateNodes(iterable $nodes, int $limit = 3, ?string $pageUrl = null): string {
        $items = [];
        $count = 0;
        foreach ($nodes as $node) {
            $items[] = $this->locateNode($node, $pageUrl);
            $count++;
            if ($count >= $limit) {
                break;
            }
        }
        return implode(' | ', array_filter($items));
    }

    private function findGoogleBusinessProfileLinks(): array {
        $matches = [];
        foreach ($this->xpathQuery('//a[@href]') as $link) {
            if (!$link instanceof \DOMElement) {
                continue;
            }

            $href = trim($link->getAttribute('href'));
            $lowerHref = $this->lower($href);
            $isGbp = str_contains($lowerHref, 'g.page/')
                || str_contains($lowerHref, 'google.com/maps?cid=')
                || str_contains($lowerHref, 'google.com/maps/place/')
                || str_contains($lowerHref, 'google.com/maps/search/')
                || str_contains($lowerHref, 'google.com/localservices/')
                || str_contains($lowerHref, 'maps.app.goo.gl/')
                || str_contains($lowerHref, 'google.com/search?')
                || str_contains($lowerHref, 'goo.gl/maps/');

            if ($isGbp) {
                $text = $this->normalizeWhitespace($link->textContent ?? '');
                $matches[] = $this->limit(($text !== '' ? $text . ' -> ' : '') . $href, 180);
            }
        }

        return array_values(array_unique($matches));
    }

    private function extractSchemaTypes(): array {
        $types = [];
        foreach ($this->xpathQuery('//script[@type="application/ld+json"]') as $script) {
            $json = trim($script->textContent ?? '');
            if ($json === '') {
                continue;
            }

            $decoded = json_decode($json, true);
            if (!is_array($decoded)) {
                continue;
            }

            $stack = [$decoded];
            while ($stack) {
                $item = array_pop($stack);
                if (!is_array($item)) {
                    continue;
                }

                if (isset($item['@type'])) {
                    foreach ((array) $item['@type'] as $type) {
                        $type = trim((string) $type);
                        if ($type !== '') {
                            $types[] = $type;
                        }
                    }
                }

                foreach ($item as $child) {
                    if (is_array($child)) {
                        $stack[] = $child;
                    }
                }
            }
        }

        return array_values(array_unique($types));
    }

    private function extractBusinessNameCandidates(): array {
        $candidates = [];
        $raw = [
            $this->getText('//title'),
            $this->getText('//h1'),
            $this->getAttribute('//meta[@property="og:site_name"]', 'content'),
            $this->getAttribute('//meta[@name="application-name"]', 'content'),
        ];

        foreach ($raw as $value) {
            $value = $this->normalizeWhitespace((string) $value);
            if ($value === '') {
                continue;
            }

            foreach (preg_split('/\s+[|\-–:]\s+/', $value) ?: [$value] as $part) {
                $part = $this->normalizeWhitespace((string) $part);
                if ($part !== '' && strlen($part) >= 3) {
                    $candidates[] = $part;
                }
            }
        }

        $host = parse_url($this->url, PHP_URL_HOST) ?? '';
        $host = preg_replace('/^www\./i', '', strtolower((string) $host));
        if ($host !== '') {
            $domainLabel = preg_replace('/\.[a-z]{2,}(?:\.[a-z]{2,})?$/i', '', (string) $host);
            $domainLabel = str_replace(['-', '_'], ' ', $domainLabel);
            $domainLabel = $this->normalizeWhitespace($domainLabel);
            if ($domainLabel !== '') {
                $candidates[] = $domainLabel;
            }
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    private function extractBusinessLocationCandidates(): array {
        $candidates = [];

        foreach ($this->pageProfiles as $profile) {
            foreach ((array) ($profile['location_terms'] ?? []) as $term) {
                $term = $this->normalizeWhitespace((string) $term);
                if ($term !== '' && strlen($term) >= 3) {
                    $candidates[] = $term;
                }
            }

            $addressSnippet = $this->normalizeWhitespace((string) ($profile['address_snippet'] ?? ''));
            if ($addressSnippet !== '') {
                $candidates[] = $addressSnippet;
            }
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    // ─────────────────────────── SEO CHECKS ───────────────────────────────

    private function runSeoChecks(): void {
        // Title tag
        $title = $this->getText('//title');
        if (empty($title)) {
            $this->addIssue('seo', 'critical', 'MISSING_TITLE',
                'Missing Page Title',
                'Your page has no title tag.',
                'Page titles are one of the most important SEO factors. Search engines display them in results.',
                'Add a descriptive <title> tag inside your <head> section.',
                'Websites without titles rank poorly and have very low click-through rates.'
            );
        } elseif (strlen($title) < 20) {
            $this->addIssue('seo', 'high', 'WEAK_TITLE',
                'Page Title Is Too Short',
                "Your title tag is very short: \"{$title}\"",
                'Short titles miss valuable keywords and look unprofessional in search results.',
                'Rewrite your title to 50-60 characters including your primary keyword and business name.',
                '',
                $title
            );
        } elseif (strlen($title) > 70) {
            $this->addIssue('seo', 'medium', 'TITLE_TOO_LONG',
                'Page Title Is Too Long',
                "Your title is " . strlen($title) . " characters, which may get cut off in search results.",
                'Google displays 50-60 characters. Longer titles get truncated.',
                'Shorten your title to under 60 characters while keeping important keywords.',
                '',
                $title
            );
        }

        $h1Text = $this->getText('//h1');
        if ($title !== '' && $h1Text !== '' && $this->lower($this->normalizeWhitespace($title)) === $this->lower($this->normalizeWhitespace($h1Text))) {
            $this->addIssue('seo', 'low', 'TITLE_MATCHES_H1_EXACTLY',
                'Title Tag Matches H1 Exactly',
                'Your page title and H1 are identical.',
                'Using the exact same phrasing in both places can make search snippets feel repetitive and miss a chance to broaden keyword coverage.',
                'Keep the topic aligned, but vary the title tag slightly by adding a location, service qualifier, or brand name.',
                '',
                $title
            );
        }

        // Meta description
        $desc = $this->getAttribute('//meta[@name="description"]', 'content');
        if (empty($desc)) {
            $this->addIssue('seo', 'high', 'MISSING_META_DESC',
                'Missing Meta Description',
                'No meta description was found on your page.',
                'Meta descriptions appear as the snippet in search results, affecting click-through rates.',
                'Add <meta name="description" content="Your compelling 150-160 character description here.">',
                'Missing meta descriptions can result in lower click-through rates from Google.'
            );
        } elseif (strlen($desc) < 50) {
            $this->addIssue('seo', 'medium', 'WEAK_META_DESC',
                'Meta Description Too Short',
                "Your meta description is only " . strlen($desc) . " characters.",
                'Short descriptions don\'t give search engines enough context.',
                'Expand to 150-160 characters with compelling, keyword-rich copy.',
                '',
                $desc
            );
        } elseif (strlen($desc) > 165) {
            $this->addIssue('seo', 'low', 'META_DESC_TOO_LONG',
                'Meta Description Is Too Long',
                "Your meta description is " . strlen($desc) . " characters, so search engines may truncate it.",
                'Descriptions that are too long can be cut off in search results and lose key messaging.',
                'Trim the description to around 150-160 characters while keeping the value proposition clear.',
                '',
                $desc
            );
        }

        // H1 tag
        $h1s = $this->xpathQuery('//h1');
        if ($h1s->length === 0) {
            $this->addIssue('seo', 'high', 'MISSING_H1',
                'Missing H1 Heading',
                'No H1 heading was found on your page.',
                'H1 tags signal the primary topic of your page to search engines.',
                'Add exactly one clear H1 tag near the top of your page content.',
                'Missing H1 tags weaken your page\'s ability to rank for target keywords.'
            );
        } elseif ($h1s->length > 1) {
            $this->addIssue('seo', 'medium', 'MULTIPLE_H1',
                'Multiple H1 Headings Found',
                "Found {$h1s->length} H1 headings. Best practice is exactly one per page.",
                'Multiple H1s can confuse search engines about your page\'s primary topic.',
                'Use only one H1 per page, then use H2-H6 for subheadings.',
                '',
                (string)$h1s->length
            );
        }

        // Canonical
        $canonical = $this->getAttribute('//link[@rel="canonical"]', 'href');
        if (empty($canonical)) {
            $this->addIssue('seo', 'medium', 'MISSING_CANONICAL',
                'Missing Canonical Tag',
                'No canonical URL tag was found.',
                'Canonical tags prevent duplicate content issues and tell search engines your preferred URL.',
                'Add <link rel="canonical" href="https://yoursite.com/page-url/"> in your <head>.',
                ''
            );
        } else {
            $resolvedCanonical = $this->urlNormalizer->resolveLink($this->fetchResult['final_url'] ?? $this->url, $canonical);
            if (!$resolvedCanonical) {
                $this->addIssue('seo', 'medium', 'INVALID_CANONICAL',
                    'Canonical Tag Could Not Be Resolved',
                    'A canonical tag exists, but its URL appears invalid or incomplete.',
                    'Broken canonical URLs can confuse search engines about which page should rank.',
                    'Use a valid absolute canonical URL that points to the preferred version of this page.',
                    '',
                    $canonical
                );
            } elseif (!$this->urlNormalizer->isSameDomain($this->url, $resolvedCanonical)) {
                $this->addIssue('seo', 'high', 'CANONICAL_OTHER_DOMAIN',
                    'Canonical Points To Another Domain',
                    'Your canonical tag points to a different domain.',
                    'Cross-domain canonicals can remove this page from search visibility if used incorrectly.',
                    'Verify that the canonical URL should point off-site. If not, update it to the correct page on this domain.',
                    '',
                    $resolvedCanonical
                );
            }
        }

        // Indexability
        $robotsMeta = $this->getAttribute('//meta[@name="robots"]', 'content');
        if ($robotsMeta !== '' && str_contains($this->lower($robotsMeta), 'noindex')) {
            $this->addIssue('seo', 'critical', 'NOINDEX_TAG_FOUND',
                'Page Marked Noindex',
                'Your page includes a meta robots tag with noindex.',
                'A noindex instruction tells search engines not to include this page in search results.',
                'Remove noindex if this page should rank in Google, or keep it only on pages meant to stay private.',
                'Search engines may exclude this page entirely from search.',
                $robotsMeta
            );
        }

        // Open Graph
        $ogTitle = $this->getAttribute('//meta[@property="og:title"]', 'content');
        $ogDescription = $this->getAttribute('//meta[@property="og:description"]', 'content');
        $ogImage = $this->getAttribute('//meta[@property="og:image"]', 'content');
        $ogUrl = $this->getAttribute('//meta[@property="og:url"]', 'content');
        if (empty($ogTitle) || empty($ogDescription) || empty($ogImage) || empty($ogUrl)) {
            $this->addIssue('seo', 'medium', 'MISSING_OG_TAGS',
                'Open Graph Tags Are Incomplete',
                'Your Open Graph metadata is missing one or more of the core tags: og:title, og:description, og:image, or og:url.',
                'When someone shares your site on Facebook or LinkedIn, Open Graph tags control the title, description, and image.',
                'Add og:title, og:description, og:image, and og:url meta tags.',
                ''
            );
        }

        // Twitter Card
        $twCard = $this->getAttribute('//meta[@name="twitter:card"]', 'content');
        if (empty($twCard)) {
            $this->addIssue('seo', 'low', 'MISSING_TWITTER_CARD',
                'Missing Twitter Card Tags',
                'No Twitter card meta tags were found.',
                'Twitter cards control how your page appears when shared on Twitter/X.',
                'Add <meta name="twitter:card" content="summary_large_image"> and related tags.',
                ''
            );
        }

        // Favicon
        $favicon = $this->getAttribute('//link[contains(@rel,"icon")]', 'href');
        if (empty($favicon)) {
            $this->addIssue('seo', 'low', 'MISSING_FAVICON',
                'Missing Favicon',
                'No favicon was detected.',
                'Favicons appear in browser tabs and bookmark lists, reinforcing brand recognition.',
                'Add a favicon.ico to your root directory and reference it with <link rel="icon">.',
                ''
            );
        }

        // HTTPS check
        if (!str_starts_with($this->url, 'https://')) {
            $this->addIssue('seo', 'critical', 'NO_HTTPS',
                'Website Not Using HTTPS',
                'Your website is served over HTTP, not HTTPS.',
                'Google flags non-HTTPS sites as "Not Secure". This harms trust, rankings, and conversions.',
                'Install an SSL certificate. Most hosts (including Hostinger) offer free Let\'s Encrypt SSL.',
                'Non-HTTPS sites lose visitors who see "Not Secure" warnings.'
            );
        }

        // Content depth
        $bodyText = $this->normalizeWhitespace($this->getText('//body'));
        $wordCount = preg_match_all('/\b[\p{L}\p{N}\']+\b/u', $bodyText, $matches);
        if ($wordCount > 0 && $wordCount < 250) {
            $this->addIssue('seo', 'medium', 'THIN_CONTENT',
                'Page Content Is Thin',
                "This page only contains about {$wordCount} words of visible text.",
                'Thin pages often struggle to rank because they provide limited context and topical depth.',
                'Add more useful, original copy that clearly explains the service, topic, or offer on the page.',
                '',
                (string) $wordCount . ' visible words'
            );
        }

        // Internal linking
        $internalLinks = [];
        foreach ($this->xpathQuery('//a[@href]') as $link) {
            if (!$link instanceof \DOMElement) {
                continue;
            }

            $href = trim($link->getAttribute('href'));
            $resolved = $this->urlNormalizer->resolveLink($this->fetchResult['final_url'] ?? $this->url, $href);
            if ($resolved && $this->urlNormalizer->isSameDomain($this->url, $resolved)) {
                $internalLinks[$resolved] = true;
            }
        }
        $internalLinkCount = count($internalLinks);
        if ($internalLinkCount < 3) {
            $this->addIssue('seo', 'low', 'LOW_INTERNAL_LINKS',
                'Very Few Internal Links',
                "Only {$internalLinkCount} internal link(s) were found on this page.",
                'Internal links help search engines discover more pages and understand site structure.',
                'Add links to related services, supporting pages, blog posts, or contact pages from this page.',
                '',
                (string) $internalLinkCount . ' internal links'
            );
        }

        // Sitemap check (HEAD request)
        $sitemapUrl  = $this->urlNormalizer->getDomain($this->url);
        $scheme      = parse_url($this->url, PHP_URL_SCHEME) ?? 'https';
        $headChecks = $this->fetcher->checkMany([
            "{$scheme}://{$sitemapUrl}/sitemap.xml",
            "{$scheme}://{$sitemapUrl}/robots.txt",
        ]);
        $sitemapCheck = $headChecks["{$scheme}://{$sitemapUrl}/sitemap.xml"] ?? ['success' => false];
        if (!$sitemapCheck['success']) {
            $this->addIssue('seo', 'medium', 'MISSING_SITEMAP',
                'No Sitemap Found',
                'No sitemap.xml was found at the standard location.',
                'Sitemaps help search engines discover and index all your pages efficiently.',
                'Create a sitemap.xml and submit it to Google Search Console.',
                ''
            );
        }

        // Robots.txt check
        $robotsCheck = $headChecks["{$scheme}://{$sitemapUrl}/robots.txt"] ?? ['success' => false];
        if (!$robotsCheck['success']) {
            $this->addIssue('seo', 'low', 'MISSING_ROBOTS',
                'No robots.txt Found',
                'No robots.txt file was found.',
                'robots.txt controls how search engine bots crawl your site.',
                'Create a robots.txt file and specify crawling rules.',
                ''
            );
        } else {
            $robotsFetch = $this->fetchCached("{$scheme}://{$sitemapUrl}/robots.txt");
            $robotsBody = $this->lower((string) ($robotsFetch['html'] ?? ''));

            if ($robotsBody !== '' && str_contains($robotsBody, 'disallow: /')) {
                $this->addIssue('seo', 'critical', 'ROBOTS_BLOCKS_SITE',
                    'robots.txt May Block Search Engines',
                    'Your robots.txt contains a Disallow: / rule.',
                    'A sitewide disallow can prevent search engines from crawling important pages.',
                    'Review robots.txt and remove broad blocking rules unless the site is intentionally private.',
                    'Search engines may stop crawling large parts of the website.',
                    $this->limit(trim((string) ($robotsFetch['html'] ?? '')), 220)
                );
            }

            if ($sitemapCheck['success'] && !str_contains($robotsBody, 'sitemap:')) {
                $this->addIssue('seo', 'low', 'ROBOTS_MISSING_SITEMAP_REFERENCE',
                    'robots.txt Does Not Reference Sitemap',
                    'robots.txt exists, but it does not appear to list your sitemap URL.',
                    'Including the sitemap in robots.txt helps crawlers discover it faster.',
                    'Add a Sitemap: line pointing to your sitemap.xml file in robots.txt.',
                    '',
                    'robots.txt found without a Sitemap directive'
                );
            }
        }

        $schemaTypes = $this->extractSchemaTypes();
        if (empty($schemaTypes)) {
            $this->addIssue('seo', 'medium', 'MISSING_STRUCTURED_DATA',
                'No Structured Data Detected',
                'No Schema.org structured data was found on the page.',
                'Structured data helps search engines understand your business, content, and potential rich result eligibility.',
                'Add relevant schema markup such as Organization, LocalBusiness, Service, FAQPage, Product, or Article depending on the page.',
                '',
                ''
            );
        }

        foreach ($this->pageProfiles as $profile) {
            if (!$this->isKeySeoPage($profile)) {
                continue;
            }

            $pageLabel = $this->pageTypeLabel((string) ($profile['page_type'] ?? 'general'));
            $evidence = $this->pageEvidence($profile)
                . ' | words=' . (int) ($profile['word_count'] ?? 0)
                . ' | internal_links=' . (int) ($profile['internal_link_count'] ?? 0)
                . ' | title=' . (($profile['title'] ?? '') !== '' ? 'yes' : 'no')
                . ' | description=' . (($profile['description'] ?? '') !== '' ? 'yes' : 'no')
                . ' | h1=' . (int) ($profile['h1_count'] ?? 0);

            $this->addIssue('seo', 'info', 'SEO_PAGE_PROFILE',
                'SEO Snapshot: ' . $pageLabel,
                $pageLabel . ' was included in the page-by-page SEO sweep.',
                'Scanning your important pages individually gives a more realistic SEO picture than checking only one URL.',
                'Use the page-by-page SEO section in the report to compare important pages side by side.',
                '',
                $evidence
            );

            if (($profile['path'] ?? '/') !== '/') {
                if (empty($profile['title'])) {
                    $this->addIssue('seo', 'high', 'KEY_PAGE_MISSING_TITLE',
                        $pageLabel . ' Is Missing A Title Tag',
                        'A key page is missing its title tag.',
                        'Important pages like contact and service pages need their own optimized titles to rank well.',
                        'Add a descriptive unique title tag for this page.',
                        '',
                        $evidence
                    );
                }
                if (empty($profile['description'])) {
                    $this->addIssue('seo', 'medium', 'KEY_PAGE_MISSING_META_DESC',
                        $pageLabel . ' Is Missing A Meta Description',
                        'A key page is missing a meta description.',
                        'Key pages should have a custom search snippet to improve click-through rate.',
                        'Write a custom 150-160 character meta description for this page.',
                        '',
                        $evidence
                    );
                }
                if ((int) ($profile['h1_count'] ?? 0) === 0) {
                    $this->addIssue('seo', 'medium', 'KEY_PAGE_MISSING_H1',
                        $pageLabel . ' Is Missing An H1',
                        'A key page has no H1 heading.',
                        'Search engines and visitors both rely on clear page-level headings.',
                        'Add one clear H1 heading to this page.',
                        '',
                        $evidence
                    );
                }
            }

            if ((int) ($profile['word_count'] ?? 0) < 150 && ($profile['page_type'] ?? '') !== 'contact') {
                $this->addIssue('seo', 'medium', 'KEY_PAGE_THIN_CONTENT',
                    $pageLabel . ' Has Thin Content',
                    'A key page has very little visible text.',
                    'Important pages often need enough copy to explain the offer and support keyword relevance.',
                    'Add more useful explanatory copy to this page.',
                    '',
                    $evidence
                );
            }

            if ((int) ($profile['internal_link_count'] ?? 0) < 2) {
                $this->addIssue('seo', 'low', 'KEY_PAGE_LOW_INTERNAL_LINKS',
                    $pageLabel . ' Has Few Internal Links',
                    'A key page has very few internal links pointing to other pages on the site.',
                    'Internal links help spread authority and clarify the site structure for search engines.',
                    'Add links to related pages, services, FAQs, or contact actions from this page.',
                    '',
                    $evidence
                );
            }

            $focusTerms = $this->extractKeywordTerms(($profile['title'] ?? '') . ' ' . ($profile['h1_text'] ?? ''));
            if (!empty($focusTerms)) {
                $body = $this->lower((string) ($profile['html'] ?? ''));
                $matchedTerms = 0;
                foreach ($focusTerms as $term) {
                    if (substr_count($body, $term) >= 2) {
                        $matchedTerms++;
                    }
                }

                if ($matchedTerms < min(2, count($focusTerms))) {
                    $this->addIssue('seo', 'medium', 'WEAK_TOPIC_RELEVANCE',
                        $pageLabel . ' Has Weak Topic Reinforcement',
                        'The page title/H1 suggests target topics, but those terms do not appear strongly in the page content.',
                        'Pages tend to rank better when the title, heading, and body copy reinforce the same topic naturally.',
                        'Make sure the service/topic mentioned in the title and H1 is clearly explained in the body content.',
                        '',
                        $evidence . ' | focus_terms=' . implode(',', array_slice($focusTerms, 0, 4))
                    );
                }
            }
        }

        $homeProfile = null;
        foreach ($this->pageProfiles as $profile) {
            if (($profile['path'] ?? '') === '/') {
                $homeProfile = $profile;
                break;
            }
        }

        if ($homeProfile) {
            $homeEvidence = $this->pageEvidence($homeProfile)
                . ' | service_terms=' . implode(',', array_slice((array) ($homeProfile['service_terms'] ?? []), 0, 4))
                . ' | location_terms=' . implode(',', array_slice((array) ($homeProfile['location_terms'] ?? []), 0, 3));

            if (count((array) ($homeProfile['service_terms'] ?? [])) < 2) {
                $this->addIssue('seo', 'medium', 'HOMEPAGE_SEARCH_INTENT_WEAK',
                    'Homepage Search Intent Is Weak',
                    'The homepage does not clearly reinforce the services or topics the business wants to rank for.',
                    'The homepage is usually the strongest authority page on a small business website, so it should clearly state what the business does.',
                    'Strengthen the homepage title, H1, hero copy, and supporting text around your main services and offers.',
                    '',
                    $homeEvidence
                );
            }

            if (!empty($this->matchingPagePaths(fn(array $profile): bool => !empty($profile['has_address']) || !empty($profile['has_map']) || !empty($profile['has_hours'])))) {
                if (empty((array) ($homeProfile['location_terms'] ?? []))) {
                    $this->addIssue('seo', 'low', 'HOMEPAGE_LOCATION_SIGNAL_WEAK',
                        'Homepage Location Signal Is Weak',
                        'The homepage does not clearly mention a city or service area.',
                        'For local businesses, the homepage often needs at least one clear geographic signal to support local relevance.',
                        'Mention your core service area naturally in the homepage title, H1, hero copy, or trust section.',
                        '',
                        $homeEvidence
                    );
                }
            }
        }

        $serviceProfiles = array_values(array_filter($this->pageProfiles, fn(array $profile): bool => ($profile['page_type'] ?? '') === 'service'));
        $serviceWithLocation = array_values(array_filter($serviceProfiles, fn(array $profile): bool => !empty($profile['location_terms'] ?? [])));
        $localLandingProfiles = array_values(array_filter($this->pageProfiles, fn(array $profile): bool => $this->isLikelyLocalLandingPage($profile)));

        if (!empty($serviceProfiles) && empty($serviceWithLocation)) {
            $servicePaths = array_map(fn(array $profile): string => $profile['path'] ?? '/', array_slice($serviceProfiles, 0, 4));
            $this->addIssue('seo', 'medium', 'LIMITED_CITY_SERVICE_COVERAGE',
                'Service Pages Lack City or Area Coverage',
                'Service pages were found, but they do not appear to mention cities or service areas clearly.',
                'Businesses competing in local search often need service pages to reinforce where they operate, not just what they do.',
                'Add natural city, metro, county, or service-area language where it fits on important service pages.',
                '',
                'service_pages=' . implode(',', $servicePaths)
            );
        }

        $hasLocalSignals = !empty($this->matchingPagePaths(fn(array $profile): bool => !empty($profile['has_address']) || !empty($profile['has_map']) || !empty($profile['has_hours'])));
        if ($hasLocalSignals && empty($localLandingProfiles)) {
            $this->addIssue('seo', 'medium', 'NO_LOCAL_LANDING_PAGE_SIGNAL',
                'No Strong Local Landing Page Signal Found',
                'The site shows local business signals, but none of the scanned pages strongly combine service intent with location intent.',
                'Local landing pages help search engines understand which services are offered in which areas.',
                'Create or strengthen pages that clearly pair a service with a city or service area, such as "Web Design in Fairhope, AL".',
                '',
                'pages_scanned=' . implode(',', array_slice(array_column($this->pageProfiles, 'path'), 0, 6))
            );
        }
    }

    // ─────────────────────────── ACCESSIBILITY CHECKS ─────────────────────

    private function runAccessibilityChecks(): void {
        // Document language
        $lang = $this->getAttribute('//html', 'lang');
        if (empty($lang)) {
            $this->addIssue('accessibility', 'high', 'MISSING_LANG',
                'Missing Document Language Attribute',
                'Your <html> tag is missing the lang attribute.',
                'Screen readers use the lang attribute to select the correct pronunciation engine.',
                'Add lang="en" (or your language code) to the <html> tag.',
                'WCAG 2.1 Level A requirement – affects accessibility compliance.'
            );
        }

        // Images without alt text
        $imagesWithoutAlt = $this->xpathQuery('//img[not(@alt) or @alt=""]');
        $totalImages      = $this->xpathQuery('//img');
        if ($imagesWithoutAlt->length > 0) {
            $this->addIssue('accessibility', 'high', 'MISSING_ALT',
                'Images Missing Alt Text',
                "{$imagesWithoutAlt->length} of {$totalImages->length} images are missing alt text.",
                'Screen readers cannot describe images without alt text, excluding visually impaired users.',
                'Add descriptive alt attributes to all images. Decorative images use alt="".',
                'Affects users with visual impairments and also impacts image search SEO.',
                $this->locateNodes($imagesWithoutAlt, 4)
            );
        }

        // Form labels
        $inputs = $this->xpathQuery('//input[@type!="hidden" and @type!="submit" and @type!="button"]');
        $unlabeled = 0;
        $unlabeledNodes = [];
        foreach ($inputs as $input) {
            $id = $input instanceof \DOMElement ? $input->getAttribute('id') : '';
            if (empty($id)) {
                $unlabeled++;
                $unlabeledNodes[] = $input;
            } else {
                $label = $this->xpathQuery("//label[@for='{$id}']");
                if ($label->length === 0) {
                    $unlabeled++;
                    $unlabeledNodes[] = $input;
                }
            }
        }
        if ($unlabeled > 0) {
            $this->addIssue('accessibility', 'high', 'MISSING_FORM_LABELS',
                'Form Inputs Missing Labels',
                "{$unlabeled} form inputs appear to be missing associated labels.",
                'Screen readers cannot tell users what to type without labels.',
                'Add <label for="input-id"> elements or use aria-label attributes.',
                'May violate WCAG 2.1 requirements and alienate users with disabilities.',
                $this->locateNodes($unlabeledNodes, 4)
            );
        }

        // Button text
        $emptyButtons = $this->xpathQuery('//button[not(normalize-space(.))]');
        if ($emptyButtons->length > 0) {
            $this->addIssue('accessibility', 'high', 'EMPTY_BUTTONS',
                'Buttons Missing Accessible Text',
                "{$emptyButtons->length} button element(s) have no visible text.",
                'Screen readers cannot identify buttons without text or aria-label.',
                'Add visible text inside each button or use aria-label="Button description".',
                '',
                $this->locateNodes($emptyButtons, 4)
            );
        }

        // Skip navigation link
        $skipLink = $this->xpathQuery('//a[contains(@href, "#main") or contains(@class, "skip") or contains(translate(., "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "skip")]');
        if ($skipLink->length === 0) {
            $this->addIssue('accessibility', 'medium', 'MISSING_SKIP_LINK',
                'Missing Skip Navigation Link',
                'No "skip to content" link was detected.',
                'Keyboard-only users must tab through the entire navigation on every page without a skip link.',
                'Add a "Skip to main content" link as the first element in your page.',
                ''
            );
        }

        // Vague link text (click here, read more etc.)
        $vagueLinks = $this->xpathQuery('//a[translate(normalize-space(.), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = "click here" or translate(normalize-space(.), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = "read more" or translate(normalize-space(.), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = "here" or translate(normalize-space(.), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = "more"]');
        if ($vagueLinks->length > 0) {
            $this->addIssue('accessibility', 'medium', 'VAGUE_LINK_TEXT',
                'Vague Link Text Found',
                "Found {$vagueLinks->length} link(s) using non-descriptive text like 'Click Here' or 'Read More'.",
                'Screen reader users navigate by links and need descriptive text to understand context.',
                'Replace vague link text with descriptive alternatives like "Learn about our SEO services".',
                '',
                $this->locateNodes($vagueLinks, 4)
            );
        }

        // Heading structure quality (skip level check)
        $h2Count = $this->xpathQuery('//h2')->length;
        $h3Count = $this->xpathQuery('//h3')->length;
        if ($h3Count > 0 && $h2Count === 0) {
            $this->addIssue('accessibility', 'medium', 'HEADING_SKIP',
                'Heading Hierarchy Skips Levels',
                'H3 headings found but no H2 headings. Heading levels should not skip.',
                'Screen readers present headings as a document outline. Skipping levels confuses navigation.',
                'Use headings in order: H1 → H2 → H3. Do not skip levels.',
                ''
            );
        }

        // Viewport meta tag (for mobile accessibility)
        $viewport = $this->getAttribute('//meta[@name="viewport"]', 'content');
        if (empty($viewport)) {
            $this->addIssue('accessibility', 'medium', 'MISSING_VIEWPORT',
                'Missing Mobile Viewport Tag',
                'No viewport meta tag was found.',
                'Without a viewport tag, mobile users see a desktop-sized page, harming usability.',
                'Add <meta name="viewport" content="width=device-width, initial-scale=1">',
                'Google penalizes sites that are not mobile-friendly.'
            );
        }

        // Color contrast placeholder
        $this->addIssue('accessibility', 'info', 'COLOR_CONTRAST_CHECK',
            'Color Contrast Should Be Verified',
            'Automated color contrast analysis requires browser rendering. Manual or tool review recommended.',
            'Low contrast text is hard to read for users with visual impairments. WCAG requires 4.5:1 ratio.',
            'Use tools like WebAIM Contrast Checker or browser DevTools to verify color contrast.',
            ''
        );
    }

    // ─────────────────────────── CONVERSION / TRUST CHECKS ──────────────

    private function runConversionChecks(): void {
        $bodyText = strtolower($this->html);
        $scannedPaths = array_column($this->pageProfiles, 'path');
        $homeProfile = null;
        foreach ($this->pageProfiles as $profile) {
            if (($profile['page_type'] ?? '') === 'home') {
                $homeProfile = $profile;
                break;
            }
        }

        // Phone number visibility
        $phonePaths = $this->matchingPagePaths(fn(array $profile): bool => !empty($profile['has_phone']));
        $hasPhone = !empty($phonePaths);
        if (!$hasPhone) {
            $this->addIssue('conversion', 'high', 'NO_PHONE',
                'No Phone Number Detected',
                'No phone number was found on the homepage or common contact/about pages.',
                'Visitors want to easily contact a business. No phone number reduces trust and conversions.',
                'Add your phone number prominently in the header and contact section.',
                'Up to 70% of service business leads prefer to call first.',
                $this->formatPageEvidence('Checked pages', $scannedPaths)
            );
        }

        // Email visibility
        $emailPaths = $this->matchingPagePaths(fn(array $profile): bool => !empty($profile['has_email']));
        $hasEmail = !empty($emailPaths);
        if (!$hasEmail) {
            $this->addIssue('conversion', 'medium', 'NO_EMAIL',
                'No Email Address Detected',
                'No email address or mailto link was found on the homepage or common contact/about pages.',
                'Many visitors prefer email contact. Missing it reduces accessibility of your business.',
                'Add your email address or a mailto: link in your header, footer, or contact section.',
                '',
                $this->formatPageEvidence('Checked pages', $scannedPaths)
            );
        }

        // Contact form
        $formEvidence = $this->gatherContactFormEvidence();
        $hasContactForm = false;
        foreach ($formEvidence as $item) {
            if (!empty($item['has_contact_form']) || !empty($item['widgets'])) {
                $hasContactForm = true;
                break;
            }
        }
        if (!$hasContactForm) {
            $this->addIssue('conversion', 'high', 'NO_CONTACT_FORM',
                'No Contact Form Found',
                'No contact form or common embedded form widget was detected on the homepage or likely contact pages.',
                'Contact forms are essential lead capture tools. Without them, you lose potential clients.',
                'Add a contact form or make sure your existing form is server-rendered and crawlable on the contact page.',
                'Businesses with contact forms convert up to 3x more visitors into leads.',
                $this->formatContactFormEvidence($formEvidence) ?: 'Checked homepage but did not find likely contact-page form markup.'
            );
        }

        // CTA buttons
        $ctaPaths = $this->matchingPagePaths(fn(array $profile): bool => (int) ($profile['cta_count'] ?? 0) > 0);
        $foundCta = !empty($ctaPaths);
        if (!$foundCta) {
            $this->addIssue('conversion', 'high', 'NO_CTA',
                'No Clear Call-to-Action Found',
                'No clear CTA buttons or links were detected on the homepage or other primary pages.',
                'CTAs tell visitors what to do next. Without them, visitors leave without converting.',
                'Add prominent buttons like "Get a Free Quote", "Contact Us", or "Book a Consultation".',
                'Sites with clear CTAs convert up to 202% more than those without.'
            );
        } else {
            $this->addIssue('conversion', 'info', 'CTA_LOCATIONS_FOUND',
                'Calls-to-Action Found Across The Site',
                'Clickable CTA elements were found while scanning your primary pages.',
                'Knowing where CTAs already exist makes it easier to improve placement and consistency across the user journey.',
                'Keep high-intent CTAs visible near the hero, service sections, and contact page.',
                '',
                $this->formatPageEvidence('Pages with CTA elements', $ctaPaths)
            );
        }

        if ($homeProfile) {
            if ((int) ($homeProfile['above_fold_cta_count'] ?? 0) < 1) {
                $this->addIssue('conversion', 'medium', 'CTA_NOT_PROMINENT',
                    'Calls-To-Action Are Not Prominent Early On',
                    'The homepage does not appear to place a strong CTA in the header or hero area.',
                    'Visitors often decide quickly whether to take action. Weak early CTA placement can lower conversions.',
                    'Place a clear call-to-action near the top of the homepage so visitors immediately know what to do next.',
                    'Weak above-the-fold CTA placement can lower lead flow from first-time visitors.',
                    $this->pageEvidence($homeProfile)
                );
            }

            if ((int) ($homeProfile['hero_word_count'] ?? 0) < 6 || empty($homeProfile['service_terms'] ?? [])) {
                $this->addIssue('conversion', 'medium', 'WEAK_HERO_MESSAGE',
                    'Homepage Hero Message Is Not Clear Enough',
                    'The main homepage intro looks light on clear service messaging.',
                    'If the first message is vague, visitors may not quickly understand what the business does.',
                    'Make the hero headline and subheadline more direct about the core service, audience, and next step.',
                    'Unclear first impressions can reduce both trust and lead conversion.',
                    $this->pageEvidence($homeProfile)
                );
            }

            if ((int) ($homeProfile['offer_signal_count'] ?? 0) < 1) {
                $this->addIssue('conversion', 'medium', 'OFFER_CLARITY_WEAK',
                    'The Offer Is Not Clear Enough',
                    'The homepage does not strongly signal an offer such as a quote, consultation, estimate, or booking action.',
                    'People convert more easily when the offer and next step are obvious.',
                    'Make the offer more explicit with wording like request a quote, schedule a consultation, or get started.',
                    'Weak offer clarity can reduce response rate from ready-to-buy visitors.',
                    $this->pageEvidence($homeProfile)
                );
            }
        }

        if (count($ctaPaths) > 0 && count($ctaPaths) < 2) {
            $this->addIssue('conversion', 'low', 'CTA_PLACEMENT_THIN',
                'Calls-To-Action Are Not Repeated Enough',
                'CTA elements were found, but they only appear on a limited number of scanned pages.',
                'Repeating clear calls-to-action across the user journey gives visitors more chances to convert.',
                'Repeat strong CTA buttons on the homepage, service pages, and contact page.',
                '',
                $this->formatPageEvidence('Pages with CTA elements', $ctaPaths)
            );
        }

        // Testimonials / reviews
        $hasReviews = str_contains($bodyText, 'testimonial') || str_contains($bodyText, 'review') ||
                      str_contains($bodyText, 'stars') || str_contains($bodyText, '★') ||
                      $this->xpathQuery('//*[contains(@class,"testimonial") or contains(@class,"review")]')->length > 0;
        if (!$hasReviews) {
            $this->addIssue('conversion', 'medium', 'NO_TESTIMONIALS',
                'No Testimonials or Reviews Found',
                'No testimonials or customer reviews section was detected.',
                'Social proof like reviews and testimonials significantly increase visitor trust and conversions.',
                'Add a testimonials section with real customer quotes, star ratings, or review widgets.',
                '72% of consumers say positive reviews make them trust a business more.'
            );
        }

        $reviewSignalPages = $this->matchingPagePaths(fn(array $profile): bool => (int) ($profile['review_signal_count'] ?? 0) > 0);
        $trustSignalPages = $this->matchingPagePaths(fn(array $profile): bool => (int) ($profile['trust_signal_count'] ?? 0) > 0);
        if (empty($reviewSignalPages) && empty($trustSignalPages)) {
            $this->addIssue('conversion', 'medium', 'TRUST_SIGNAL_DENSITY_LOW',
                'Trust Signals Look Thin Across The Site',
                'The scanned pages show limited signs of reviews, guarantees, credentials, or other confidence-building proof.',
                'Trust signals help visitors feel safer about contacting a business.',
                'Add visible proof like reviews, credentials, guarantees, years in business, or before-and-after results.',
                'Thin trust signals can lower conversion rate from visitors who are comparing options.',
                $this->formatPageEvidence('Checked pages', $scannedPaths)
            );
        }

        // Social media links
        $socialNetworks = ['facebook.com', 'twitter.com', 'x.com', 'instagram.com', 'linkedin.com', 'youtube.com'];
        $hasSocial      = false;
        foreach ($socialNetworks as $network) {
            if (str_contains($bodyText, $network)) { $hasSocial = true; break; }
        }
        if (!$hasSocial) {
            $this->addIssue('conversion', 'low', 'NO_SOCIAL_LINKS',
                'No Social Media Links Detected',
                'No links to social media profiles were found.',
                'Social links build credibility and provide additional contact points.',
                'Add links to your active social media profiles in your header or footer.',
                ''
            );
        }

        // Trust badges
        $hasTrust = str_contains($bodyText, 'bbb') || str_contains($bodyText, 'certified') ||
                    str_contains($bodyText, 'award') || str_contains($bodyText, 'guarantee') ||
                    str_contains($bodyText, 'accredited') || str_contains($bodyText, 'trusted');
        if (!$hasTrust) {
            $this->addIssue('conversion', 'low', 'NO_TRUST_BADGES',
                'No Trust Indicators Detected',
                'No trust badges, certifications, or guarantees were detected.',
                'Trust signals increase visitor confidence and reduce hesitation before contacting you.',
                'Add trust indicators: years in business, guarantees, certifications, or association memberships.',
                ''
            );
        }

        $highFrictionPaths = $this->matchingPagePaths(fn(array $profile): bool => (int) ($profile['required_field_count'] ?? 0) >= 5 || (int) ($profile['form_field_count'] ?? 0) >= 8);
        if (!empty($highFrictionPaths)) {
            $this->addIssue('conversion', 'medium', 'HIGH_FORM_FRICTION',
                'The Contact Form May Ask For Too Much Up Front',
                'One or more scanned forms appear to ask for a relatively high number of fields before someone can submit.',
                'Long forms can discourage leads who only want to ask a quick question.',
                'Trim the form down to the essentials and leave extra qualification questions for follow-up.',
                'Higher form friction can reduce submission rate.',
                $this->formatPageEvidence('Pages with heavier forms', $highFrictionPaths)
            );
        }
    }

    // ─────────────────────────── LOCAL BUSINESS CHECKS ───────────────────

    private function runLocalChecks(): void {
        $bodyText = strtolower($this->html);
        $scannedPaths = array_column($this->pageProfiles, 'path');

        // Address / location
        $addressPaths = $this->matchingPagePaths(fn(array $profile): bool => !empty($profile['has_address']));
        $hasAddress = !empty($addressPaths);
        if (!$hasAddress) {
            $this->addIssue('local', 'medium', 'NO_ADDRESS',
                'No Physical Address Detected',
                'No street address was found on the homepage or common location/contact pages.',
                'Local businesses need visible addresses for trust and local search rankings.',
                'Add your full business address in your footer or contact section.',
                'Local SEO rankings depend on consistent name, address, and phone (NAP) information.',
                $this->formatPageEvidence('Checked pages', $scannedPaths)
            );
        }

        // Google Maps
        $mapPaths = $this->matchingPagePaths(fn(array $profile): bool => !empty($profile['has_map']));
        $hasMap = !empty($mapPaths);
        if (!$hasMap) {
            $this->addIssue('local', 'low', 'NO_MAP',
                'No Google Map Embed Found',
                'No embedded map or Google Maps listing was found on the homepage or likely contact/location pages.',
                'Maps help visitors find your location and signal to Google that you\'re a local business.',
                'Embed a Google Map on your contact page.',
                '',
                $this->formatPageEvidence('Checked pages', $scannedPaths)
            );
        }

        // Business hours
        $hoursPaths = $this->matchingPagePaths(fn(array $profile): bool => !empty($profile['has_hours']));
        $hasHours = !empty($hoursPaths);
        if (!$hasHours) {
            $this->addIssue('local', 'low', 'NO_HOURS',
                'No Business Hours Detected',
                'No business hours were found on the homepage or common contact/location pages.',
                'Customers want to know when they can reach you.',
                'Add your business hours to your contact page or footer.',
                '',
                $this->formatPageEvidence('Checked pages', $scannedPaths)
            );
        }

        // Schema.org LocalBusiness
        $schemaTypes = $this->extractSchemaTypes();
        $localSchemaTypes = array_values(array_filter($schemaTypes, static function (string $type): bool {
            $type = strtolower($type);
            return str_contains($type, 'localbusiness')
                || in_array($type, ['organization', 'store', 'restaurant', 'plumber', 'electrician', 'dentist', 'legalservice', 'realestateagent'], true);
        }));
        if (empty($localSchemaTypes)) {
            $this->addIssue('local', 'medium', 'NO_SCHEMA',
                'No Local Business Structured Data Found',
                'No LocalBusiness-style schema markup was detected.',
                'Structured data helps Google understand your business type, location, and services.',
                'Add LocalBusiness schema markup to your homepage or contact page.',
                'Rich results in Google Search require structured data.',
                $schemaTypes ? 'Other schema types found: ' . implode(', ', array_slice($schemaTypes, 0, 5)) : ''
            );
        } else {
            $this->addIssue('local', 'info', 'LOCALBUSINESS_SCHEMA_PRESENT',
                'Local Business Structured Data Found',
                'The scan detected LocalBusiness-style structured data on the site.',
                'This helps search engines understand the business identity, location, and service context more clearly.',
                'Keep the schema accurate and aligned with your real business details across the site and directory listings.',
                '',
                'Schema types found: ' . implode(', ', array_slice($localSchemaTypes, 0, 5))
            );
        }

        $phones = array_values(array_unique(array_filter(array_map(fn(array $profile): string => trim((string) ($profile['primary_phone'] ?? '')), $this->pageProfiles))));
        $emails = array_values(array_unique(array_filter(array_map(fn(array $profile): string => trim((string) ($profile['primary_email'] ?? '')), $this->pageProfiles))));
        $addresses = array_values(array_unique(array_filter(array_map(fn(array $profile): string => trim((string) ($profile['address_snippet'] ?? '')), $this->pageProfiles))));
        if (count($phones) > 1 || count($emails) > 1 || count($addresses) > 1) {
            $this->addIssue('local', 'low', 'NAP_CONSISTENCY_HINT',
                'Business Contact Details May Not Be Consistent Everywhere',
                'The scanned pages show more than one phone, email, or address pattern.',
                'Local SEO usually performs best when your core business details stay consistent across important pages.',
                'Double-check that the main business name, address, phone, and email stay consistent on key pages.',
                'Inconsistent contact details can weaken local trust and map relevance.',
                'phones=' . implode(', ', array_slice($phones, 0, 2))
                    . ' | emails=' . implode(', ', array_slice($emails, 0, 2))
                    . ' | addresses=' . implode(' || ', array_slice($addresses, 0, 2))
            );
        }

        $prominentLocalPages = $this->matchingPagePaths(fn(array $profile): bool =>
            in_array(($profile['page_type'] ?? ''), ['home', 'contact'], true)
            && (!empty($profile['has_phone']) || !empty($profile['has_address']) || !empty($profile['has_map']))
        );
        if (empty($prominentLocalPages)) {
            $this->addIssue('local', 'medium', 'LOCAL_CONTACT_PROMINENCE_WEAK',
                'Local Contact Signals Are Not Prominent Enough',
                'Important contact details do not appear strongly on the homepage or contact-focused pages.',
                'Local visitors often look for visible contact and location details before reaching out.',
                'Bring your core contact and location details into more prominent positions on the homepage and contact page.',
                'Weak local contact prominence can lower trust and local conversion rate.',
                $this->formatPageEvidence('Checked pages', $scannedPaths)
            );
        }

        $localLandingProfiles = array_values(array_filter($this->pageProfiles, fn(array $profile): bool => $this->isLikelyLocalLandingPage($profile)));
        if (count($localLandingProfiles) > 0 && count($localLandingProfiles) < 2) {
            $landingPaths = array_map(fn(array $profile): string => (string) ($profile['path'] ?? '/'), $localLandingProfiles);
            $this->addIssue('local', 'medium', 'CITY_SERVICE_PAGE_COUNT_LOW',
                'There Are Not Many Strong City + Service Landing Pages',
                'The scan found only a limited number of pages that strongly pair services with locations.',
                'Dedicated city-and-service pages often help local businesses compete in more nearby searches.',
                'Build out more strong local landing pages for your main services and target areas.',
                'Thin local landing page coverage can limit how many nearby searches you can realistically compete for.',
                $this->formatPageEvidence('Local landing pages found', $landingPaths)
            );
        }

        $reviewPages = $this->matchingPagePaths(fn(array $profile): bool => (int) ($profile['review_signal_count'] ?? 0) > 0);
        if (empty($reviewPages)) {
            $this->addIssue('local', 'medium', 'LOCAL_REVIEW_SIGNAL_MISSING',
                'Local Review Signals Look Thin',
                'The scan did not find strong signs of customer reviews or local social proof on the site.',
                'Review signals help local visitors trust the business and support stronger local SEO credibility.',
                'Show real review snippets, star ratings, or a visible Google review path on key pages.',
                'Thin review signals can make it harder to earn trust from local searchers.',
                $this->formatPageEvidence('Checked pages', $scannedPaths)
            );
        }

        $gbpLinks = $this->findGoogleBusinessProfileLinks();
        if (!empty($gbpLinks)) {
            $this->addIssue('local', 'info', 'GBP_LINK_PRESENT',
                'Google Business Profile Link Found',
                'A Google Business Profile or Google Maps listing link was detected on the site.',
                'Linking your Google Business Profile helps visitors verify your business and improves local trust signals.',
                'Keep the link visible on your contact page, footer, or review section.',
                '',
                implode(' | ', array_slice($gbpLinks, 0, 2))
            );
        } elseif ($this->googlePlacesService) {
            $businessNames = $this->extractBusinessNameCandidates();
            $locationHints = $this->extractBusinessLocationCandidates();
            $externalGbp = $this->googlePlacesService->findBusinessProfile($this->url, $businessNames, $locationHints);
            if (!empty($externalGbp['success']) && !empty($externalGbp['match'])) {
                $match = $externalGbp['match'];
                $label = trim((string) (($match['displayName']['text'] ?? '') ?: 'Business Profile match'));
                $mapsUrl = trim((string) ($match['googleMapsUri'] ?? ''));
                $address = trim((string) ($match['formattedAddress'] ?? ''));
                $confidence = isset($externalGbp['confidence']) ? ' | confidence=' . $externalGbp['confidence'] : '';
                $this->addIssue('local', 'info', 'GBP_FOUND_EXTERNALLY',
                    'Google Business Profile Found Online',
                    'A likely Google Business Profile was found online, but your website does not link to it directly.',
                    'Linking your Google Business Profile from the site helps visitors find reviews, directions, and trust signals faster.',
                    'Add the Google Business Profile or Maps listing link to your footer, contact page, or review section.',
                    '',
                    $this->limit($label . ($address !== '' ? ' | ' . $address : '') . ($mapsUrl !== '' ? ' | ' . $mapsUrl : '') . $confidence, 220)
                );
                if (!empty($externalGbp['cached'])) {
                    $this->addIssue('local', 'info', 'GBP_LOOKUP_CACHED',
                        'Google Business Profile Lookup Used Cached Data',
                        'The Google Business Profile comparison reused a recent cached lookup result.',
                        'Caching helps the scanner stay fast while still surfacing likely profile matches.',
                        'Re-run the audit later if you recently changed your Google Business Profile details and want a fresh check.',
                        '',
                        'Cached GBP lookup'
                    );
                }
            } else {
                $queryEvidence = !empty($externalGbp['queries']) ? 'Searches tried: ' . implode(', ', array_slice((array) $externalGbp['queries'], 0, 4)) : '';
                $this->addIssue('local', 'medium', 'NO_GBP_LINK',
                    'No Google Business Profile Link Detected',
                    'No link to a Google Business Profile or Google Maps business listing was detected on the site, and no likely external profile match was found from the business/domain search.',
                    'A visible Google Business Profile reinforces legitimacy and helps local SEO and review discovery.',
                    'Add a link to your Google Business Profile, Google Maps listing, or review profile on your site.',
                    'Google Business Profiles are a major trust and visibility signal for local businesses.',
                    $queryEvidence
                );
            }
        }
    }

    // ─────────────────────────── TECHNICAL CHECKS ───────────────────────

    private function runTechnicalChecks(): void {
        // Response time
        $rt = $this->fetchResult['response_time'] ?? 0;
        if ($rt > 3000) {
            $this->addIssue('technical', 'high', 'SLOW_RESPONSE',
                'Very Slow Page Load',
                "Your page took {$rt}ms to load. Recommended is under 1000ms.",
                'Slow pages have higher bounce rates. Google uses page speed as a ranking factor.',
                'Optimize images, enable caching, use a CDN, and minimize JavaScript.',
                'A 1-second delay reduces conversions by 7%.',
                "{$rt}ms total fetch time"
            );
        } elseif ($rt > 1500) {
            $this->addIssue('technical', 'medium', 'MODERATE_RESPONSE',
                'Page Load Could Be Faster',
                "Your page loaded in {$rt}ms. Aim for under 1000ms.",
                'Faster sites rank better and convert more visitors.',
                'Enable browser caching, compress images, and review heavy scripts.',
                '',
                "{$rt}ms total fetch time"
            );
        } else {
            $this->addIssue('technical', 'info', 'LOAD_TIME_BASELINE',
                'Page Load Baseline Captured',
                "The page responded in {$rt}ms during the audit request.",
                'Tracking response time over multiple scans helps you spot regressions before they hurt rankings or conversions.',
                'Re-run audits after design or plugin changes and compare response time, page size, script count, and image count.',
                '',
                "{$rt}ms total fetch time"
            );
        }

        // Page size
        $size = $this->fetchResult['size'] ?? 0;
        if ($size > 500000) {
            $this->addIssue('technical', 'medium', 'LARGE_PAGE_SIZE',
                'Large Page Size',
                'Your page HTML is very large (' . formatBytes($size) . ').',
                'Large pages load slower and use more bandwidth.',
                'Minimize inline CSS/JS, defer non-critical resources, and use lazy loading.',
                '',
                formatBytes($size)
            );
        } else {
            $this->addIssue('technical', 'info', 'PAGE_WEIGHT_BASELINE',
                'Page Weight Baseline Captured',
                'The downloaded HTML for this page was measured during the audit.',
                'Page weight is a useful performance baseline when comparing future scans or major site changes.',
                'Keep HTML lean, move large assets to deferred files, and watch this metric after theme or plugin updates.',
                '',
                formatBytes($size) . ' HTML size'
            );
        }

        // Images
        $images = $this->xpathQuery('//img');
        if ($images->length > 30) {
            $this->addIssue('technical', 'info', 'MANY_IMAGES',
                'High Number of Images',
                "Found {$images->length} images on this page.",
                'Many unoptimized images can slow down your page significantly.',
                'Ensure all images are compressed and use modern formats like WebP.',
                '',
                (string)$images->length . ' images'
            );
        }

        // Scripts
        $scripts = $this->xpathQuery('//script[not(@type) or @type="text/javascript"]');
        if ($scripts->length > 15) {
            $this->addIssue('technical', 'medium', 'MANY_SCRIPTS',
                'Many JavaScript Files',
                "Found {$scripts->length} script tags on this page.",
                'Too many scripts slow page load. Each request adds delay.',
                'Bundle and minify JavaScript files. Defer or async load non-critical scripts.',
                '',
                (string)$scripts->length . ' script tags'
            );
        }

        // SSL check
        $isHttps = str_starts_with($this->fetchResult['final_url'] ?? $this->url, 'https://');
        if (!$isHttps) {
            // Already reported in SEO checks
        }

        // HTTP status
        $code = $this->fetchResult['http_code'] ?? 200;
        if ($code >= 400) {
            $this->addIssue('technical', 'critical', 'HTTP_ERROR',
                "HTTP Error {$code} Detected",
                "Your page returned HTTP status code {$code}.",
                'Error pages prevent visitors from seeing your site content.',
                'Check your server configuration and ensure your pages return HTTP 200.',
                '',
                (string)$code
            );
        }

        // Viewport
        $viewport = $this->getAttribute('//meta[@name="viewport"]', 'content');
        if (empty($viewport)) {
            // Already reported in accessibility
        }

        // Check for render-blocking resources
        $syncScripts = $this->xpathQuery('//head/script[not(@async) and not(@defer) and @src]');
        if ($syncScripts->length > 2) {
            $this->addIssue('technical', 'medium', 'RENDER_BLOCKING',
                'Render-Blocking Scripts in Head',
                "{$syncScripts->length} synchronous scripts in <head> may delay page rendering.",
                'Scripts in the head block rendering until downloaded, parsed, and executed.',
                'Move scripts to bottom of <body> or add async/defer attributes.',
                '',
                $this->summarizeNodes($syncScripts, 4)
            );
        }

        if ($this->pageSpeedService) {
            $targetUrl = $this->fetchResult['final_url'] ?? $this->url;
            $this->pageSpeedResults = $this->pageSpeedService->runMany($targetUrl, ['mobile']);
            $mobilePageSpeed = $this->pageSpeedResults['mobile'] ?? ['success' => false];

            if (!empty($mobilePageSpeed['success'])) {
                $seoCategory = isset($mobilePageSpeed['categories']['seo']) ? (int) $mobilePageSpeed['categories']['seo'] : null;
                if ($seoCategory !== null) {
                    $seoSeverity = $seoCategory < 50 ? 'high' : ($seoCategory < 75 ? 'medium' : 'info');
                    $seoCode = $seoCategory < 75 ? 'LIGHTHOUSE_SEO_LOW' : 'LIGHTHOUSE_SEO_BASELINE';
                    $seoTitle = $seoCategory < 75 ? 'Lighthouse SEO Score Needs Work' : 'Lighthouse SEO Score Captured';
                    $this->addIssue('seo', $seoSeverity, $seoCode,
                        $seoTitle,
                        "Google Lighthouse reported a mobile SEO score of {$seoCategory}/100 for this page.",
                        'Lighthouse checks technical search basics like crawlable links, mobile viewport behavior, and indexing-related page setup.',
                        'Review the Lighthouse SEO audit details and fix the flagged crawlability, metadata, and mobile-search issues.',
                        $seoCategory < 75 ? 'A weak technical SEO baseline can limit how well pages are understood and surfaced in search.' : '',
                        'Mobile Lighthouse SEO ' . $seoCategory . '/100'
                    );
                }

                $score = (int) ($mobilePageSpeed['score'] ?? 0);
                $metrics = $mobilePageSpeed['metrics'] ?? [];
                $metricSummary = [];
                foreach ([
                    'fcp' => 'FCP',
                    'lcp' => 'LCP',
                    'speed_index' => 'Speed Index',
                    'tbt' => 'TBT',
                    'cls' => 'CLS',
                ] as $key => $label) {
                    if (!empty($metrics[$key])) {
                        $metricSummary[] = $label . ' ' . $metrics[$key];
                    }
                }
                $evidence = 'Mobile Lighthouse ' . $score . '/100';
                if ($metricSummary) {
                    $evidence .= ' | ' . implode(' | ', $metricSummary);
                }
                if (!empty($mobilePageSpeed['cached'])) {
                    $evidence .= ' | cached result';
                }

                $severity = $score < 50 ? 'high' : ($score < 75 ? 'medium' : 'info');
                $code = $score < 75 ? 'LIGHTHOUSE_PERFORMANCE_LOW' : 'LIGHTHOUSE_PERFORMANCE_BASELINE';
                $title = $score < 75 ? 'Lighthouse Performance Score Needs Work' : 'Lighthouse Performance Score Captured';
                $explanation = $score < 75
                    ? "Google Lighthouse reported a mobile performance score of {$score}/100 for this page."
                    : "Google Lighthouse reported a mobile performance score of {$score}/100 for this page.";
                $howToFix = 'Review the Lighthouse opportunities, optimize images and scripts, reduce render-blocking resources, and compare future scans after changes.';
                $businessImpact = $score < 75 ? 'Slower, less stable pages can reduce rankings, raise bounce rate, and lower conversions.' : '';

                $this->addIssue('technical', $severity, $code,
                    $title,
                    $explanation,
                    'Lighthouse measures lab performance signals like paint timing, blocking time, and layout stability that strongly affect user experience.',
                    $howToFix,
                    $businessImpact,
                    $evidence
                );
            } else {
                $error = strtolower(trim((string) ($mobilePageSpeed['error'] ?? '')));
                $isPending = $error === ''
                    || str_contains($error, 'timed out')
                    || str_contains($error, 'connection failed')
                    || str_contains($error, 'could not resolve')
                    || str_contains($error, 'ssl');

                $this->addIssue('technical', $isPending ? 'low' : 'medium', $isPending ? 'PAGESPEED_LOOKUP_PENDING' : 'PAGESPEED_LOOKUP_FAILED',
                    $isPending ? 'Performance Lab Data Is Still Pending' : 'Performance Lab Data Could Not Be Retrieved',
                    $isPending
                        ? 'The audit did not wait for a slow external PageSpeed response, so Lighthouse data is marked as pending for now.'
                        : 'The scan could not retrieve Google PageSpeed data for this run.',
                    'External lab tools can be slow or unavailable, but your audit should still finish quickly without blocking the whole report.',
                    'Re-run the audit later if you want a fresh Lighthouse lab snapshot, or keep using the on-page speed guidance shown in the report.',
                    '',
                    trim((string) ($mobilePageSpeed['error'] ?? ''))
                );
            }
        }
    }

    // ─────────────────────────── RESULT BUILDER ─────────────────────────

    private function buildResult(array $fetch): array {
        return [
            'url'           => $this->url,
            'final_url'     => $fetch['final_url'] ?? $this->url,
            'http_code'     => $fetch['http_code'] ?? 0,
            'response_time' => $fetch['response_time'] ?? 0,
            'page_size'     => $fetch['size'] ?? 0,
            'success'       => $fetch['success'] ?? false,
            'issues'        => $this->issues,
            'meta'          => $this->extractMeta(),
            'page_speed'    => $this->pageSpeedResults,
            'page_profiles' => $this->pageProfiles,
        ];
    }

    private function extractMeta(): array {
        if (!$this->dom instanceof \DOMDocument) return [];
        return [
            'title'       => $this->getText('//title'),
            'description' => $this->getAttribute('//meta[@name="description"]', 'content'),
            'h1'          => $this->getText('//h1'),
            'lang'        => $this->getAttribute('//html', 'lang'),
            'canonical'   => $this->getAttribute('//link[@rel="canonical"]', 'href'),
            'viewport'    => $this->getAttribute('//meta[@name="viewport"]', 'content'),
            'images'      => $this->xpathQuery('//img')->length,
            'links'       => $this->xpathQuery('//a[@href]')->length,
            'scripts'     => $this->xpathQuery('//script')->length,
            'forms'       => $this->xpathQuery('//form')->length,
        ];
    }
}
