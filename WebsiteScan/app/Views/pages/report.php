<?php
// Helpers for report display
$scoreColor = function(int $score): string {
    if ($score >= 80) return '#22c55e';
    if ($score >= 60) return '#f59e0b';
    if ($score >= 40) return '#ef4444';
    return '#dc2626';
};
$severityBadge = function(string $severity): string {
    return match($severity) {
        'critical' => 'danger',
        'high'     => 'warning',
        'medium'   => 'primary',
        'low'      => 'info',
        default    => 'secondary',
    };
};
$categoryIcon = function(string $cat): string {
    return match($cat) {
        'seo'           => 'bi-search',
        'accessibility' => 'bi-universal-access',
        'conversion'    => 'bi-graph-up-arrow',
        'technical'     => 'bi-lightning',
        'local'         => 'bi-geo-alt',
        default         => 'bi-exclamation-circle',
    };
};
$overall       = (int)($report['overall_score'] ?? 0);
$scores        = $scores ?? [];
$seo           = (int)($scores['seo_score'] ?? 0);
$a11y          = (int)($scores['accessibility_score'] ?? 0);
$conversion    = (int)($scores['conversion_score'] ?? 0);
$technical     = (int)($scores['technical_score'] ?? 0);
$local         = (int)($scores['local_score'] ?? 0);
$gradeInfo     = $grade ?? ['grade'=>'C','label'=>'Fair','color'=>'#f59e0b'];
$siteUrl       = $requestData['website_url'] ?? '';
$screenshotUrl = $report['screenshot_url'] ?? '';
$rawIssues     = $issues ?? [];
$allIssues     = array_values(array_filter($rawIssues, static fn($issue) => ($issue['code'] ?? '') !== 'SEO_PAGE_PROFILE'));
$comparison    = $comparison ?? null;
$feedbackSummary = $feedbackSummary ?? [];
$pageSpeedData = $pageSpeedData ?? ['mobile' => null, 'desktop' => null];
$pageFlashSuccess = \App\Core\Session::getFlash('success');
$pageFlashError = \App\Core\Session::getFlash('error');
$criticals     = array_filter($allIssues, fn($i) => $i['severity'] === 'critical');
$highs         = array_filter($allIssues, fn($i) => $i['severity'] === 'high');
$priorityIssues = array_slice(array_values(array_filter(
    $allIssues,
    fn($i) => in_array($i['severity'] ?? 'info', ['critical', 'high', 'medium'], true)
)), 0, 3);
$severityOrder = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3, 'info' => 4];
$sortedIssues = $allIssues;
usort($sortedIssues, static function (array $left, array $right) use ($severityOrder): int {
    $leftRank = $severityOrder[$left['severity'] ?? 'info'] ?? 99;
    $rightRank = $severityOrder[$right['severity'] ?? 'info'] ?? 99;
    if ($leftRank === $rightRank) {
        return strcmp((string) ($left['title'] ?? ''), (string) ($right['title'] ?? ''));
    }
    return $leftRank <=> $rightRank;
});
$topFindingIssues = array_slice($sortedIssues, 0, 5);
$loadMetricIssue = null;
$pageWeightIssue = null;
$scriptIssue = null;
$lighthouseIssue = null;
$lighthouseMetrics = [];
$lighthouseOpportunities = [];
$performanceDiagnostics = [];
$metricThresholds = [
    'First Contentful Paint' => ['good' => 1.8, 'needs' => 3.0],
    'Largest Contentful Paint' => ['good' => 2.5, 'needs' => 4.0],
    'Total Blocking Time' => ['good' => 200, 'needs' => 600],
    'Cumulative Layout Shift' => ['good' => 0.1, 'needs' => 0.25],
    'Speed Index' => ['good' => 3.4, 'needs' => 5.8],
    'Time to Interactive' => ['good' => 3.8, 'needs' => 7.3],
];
$metricGuides = [
    'First Contentful Paint' => 'How quickly the page starts showing visible content.',
    'Largest Contentful Paint' => 'How quickly the main content becomes visible.',
    'Total Blocking Time' => 'How much JavaScript delay can block clicks and scrolling.',
    'Cumulative Layout Shift' => 'How much the layout jumps around while loading.',
    'Speed Index' => 'How quickly the page looks visually complete overall.',
    'Time to Interactive' => 'When the page becomes reliably usable.',
];
$parseMetricValue = function (string $value): ?float {
    if (!preg_match('/-?\d+(?:\.\d+)?/', $value, $match)) {
        return null;
    }
    return (float) $match[0];
};
$metricTone = function (string $label, string $value) use ($metricThresholds, $parseMetricValue): array {
    if (!isset($metricThresholds[$label])) {
        return ['tone' => 'secondary', 'label' => 'Captured'];
    }

    $numeric = $parseMetricValue($value);
    if ($numeric === null) {
        return ['tone' => 'secondary', 'label' => 'Captured'];
    }

    if ($numeric <= $metricThresholds[$label]['good']) {
        return ['tone' => 'success', 'label' => 'Good'];
    }
    if ($numeric <= $metricThresholds[$label]['needs']) {
        return ['tone' => 'warning', 'label' => 'Needs work'];
    }
    return ['tone' => 'danger', 'label' => 'Poor'];
};
$formatPsiScoreTone = function (?int $score): string {
    if ($score === null) {
        return 'secondary';
    }
    if ($score >= 90) {
        return 'success';
    }
    if ($score >= 50) {
        return 'warning';
    }
    return 'danger';
};
$formatPsiScoreLabel = function (?int $score): string {
    if ($score === null) {
        return 'Unavailable';
    }
    if ($score >= 90) {
        return 'Good';
    }
    if ($score >= 50) {
        return 'Needs work';
    }
    return 'Poor';
};
$extractFirstUrl = function (?string $value): ?string {
    $value = (string) $value;
    if (preg_match('~https?://[^\s|<>"\']+~i', $value, $match)) {
        return $match[0];
    }
    return null;
};
$pageSpeedTabs = array_filter([
    'mobile' => $pageSpeedData['mobile'] ?? null,
    'desktop' => $pageSpeedData['desktop'] ?? null,
], static fn($item) => is_array($item) && !empty($item['success']));
$primarySpeedStrategy = !empty($pageSpeedTabs['mobile']) ? 'mobile' : array_key_first($pageSpeedTabs);
$primarySpeedData = ($primarySpeedStrategy !== null && isset($pageSpeedTabs[$primarySpeedStrategy])) ? $pageSpeedTabs[$primarySpeedStrategy] : null;
$issueGroups = [
    'high' => array_values(array_filter($allIssues, static fn($issue) => in_array($issue['severity'] ?? 'info', ['critical', 'high'], true))),
    'medium' => array_values(array_filter($allIssues, static fn($issue) => ($issue['severity'] ?? 'info') === 'medium')),
    'low' => array_values(array_filter($allIssues, static fn($issue) => in_array($issue['severity'] ?? 'info', ['low', 'info'], true))),
];
$issuesByCode = [];
$seoIssueCount = 0;
$seoCriticalCount = 0;
$seoHighCount = 0;
$gbpIssue = null;
$parseLocatorEvidence = function (?string $evidence): array {
    $evidence = trim((string) $evidence);
    if ($evidence === '') {
        return ['pages' => [], 'zones' => [], 'elements' => []];
    }

    preg_match_all('/page=([^|]+)/i', $evidence, $pageMatches);
    preg_match_all('/zone=([^|]+)/i', $evidence, $zoneMatches);
    preg_match_all('/element=([^|]+)/i', $evidence, $elementMatches);

    $pages = array_values(array_unique(array_filter(array_map(fn($value) => trim((string) $value), $pageMatches[1] ?? []))));
    $zones = array_values(array_unique(array_filter(array_map(fn($value) => trim((string) $value), $zoneMatches[1] ?? []))));
    $elements = array_values(array_unique(array_filter(array_map(fn($value) => trim((string) $value), $elementMatches[1] ?? []))));

    if (empty($pages)) {
        preg_match_all('~(?:^|[\s|,])(/[^|,:"]*)~', $evidence, $pathMatches);
        $pages = array_values(array_unique(array_filter(array_map('trim', $pathMatches[1] ?? []))));
    }

    return [
        'pages' => array_slice($pages, 0, 4),
        'zones' => array_slice($zones, 0, 4),
        'elements' => array_slice($elements, 0, 3),
    ];
};
$buildScreenshotMarkers = function (array $issues) use ($parseLocatorEvidence): array {
    $zonePositions = [
        'nav' => ['top' => '10%', 'left' => '20%'],
        'header' => ['top' => '15%', 'left' => '28%'],
        'hero' => ['top' => '28%', 'left' => '58%'],
        'form' => ['top' => '54%', 'left' => '63%'],
        'main' => ['top' => '52%', 'left' => '35%'],
        'sidebar' => ['top' => '48%', 'left' => '82%'],
        'footer' => ['top' => '84%', 'left' => '42%'],
    ];

    $markers = [];
    $index = 1;
    foreach (array_slice($issues, 0, 5) as $issue) {
        $locator = $parseLocatorEvidence($issue['detected_value'] ?? '');
        $zone = $locator['zones'][0] ?? 'main';
        $position = $zonePositions[$zone] ?? ['top' => (18 + ($index * 11)) . '%', 'left' => (20 + ($index * 9)) . '%'];
        $markers[] = [
            'number' => $index,
            'title' => $issue['title'] ?? 'Issue',
            'zone' => $zone,
            'pages' => $locator['pages'],
            'elements' => $locator['elements'],
            'top' => $position['top'],
            'left' => $position['left'],
        ];
        $index++;
    }
    return $markers;
};
foreach ($allIssues as $issueItem) {
    $issueCode = (string) ($issueItem['code'] ?? '');
    if ($issueCode !== '') {
        $issuesByCode[$issueCode] = $issueItem;
    }
    if (($issueItem['category'] ?? '') === 'seo') {
        $seoIssueCount++;
        if (($issueItem['severity'] ?? '') === 'critical') {
            $seoCriticalCount++;
        }
        if (($issueItem['severity'] ?? '') === 'high') {
            $seoHighCount++;
        }
    }
    if (in_array($issueItem['code'] ?? '', ['SLOW_RESPONSE', 'MODERATE_RESPONSE', 'LOAD_TIME_BASELINE'], true) && !$loadMetricIssue) {
        $loadMetricIssue = $issueItem;
    }
    if (in_array($issueItem['code'] ?? '', ['LARGE_PAGE_SIZE', 'PAGE_WEIGHT_BASELINE'], true) && !$pageWeightIssue) {
        $pageWeightIssue = $issueItem;
    }
    if (($issueItem['code'] ?? '') === 'MANY_SCRIPTS' && !$scriptIssue) {
        $scriptIssue = $issueItem;
    }
    if (in_array($issueItem['code'] ?? '', ['LIGHTHOUSE_PERFORMANCE_LOW', 'LIGHTHOUSE_PERFORMANCE_BASELINE'], true) && !$lighthouseIssue) {
        $lighthouseIssue = $issueItem;
    }
    if (in_array($issueItem['code'] ?? '', ['GBP_LINK_PRESENT', 'GBP_FOUND_EXTERNALLY'], true) && !$gbpIssue) {
        $gbpIssue = $issueItem;
    }
}

if (!empty($lighthouseIssue['detected_value'])) {
    preg_match('/Mobile Lighthouse\s+(\d+)\/100/i', $lighthouseIssue['detected_value'], $scoreMatch);
    if (!empty($scoreMatch[1])) {
        $lighthouseMetrics['Performance'] = $scoreMatch[1] . '/100';
    }
    $metricMap = [
        'FCP' => 'First Contentful Paint',
        'LCP' => 'Largest Contentful Paint',
        'Speed Index' => 'Speed Index',
        'TBT' => 'Total Blocking Time',
        'CLS' => 'Cumulative Layout Shift',
        'TTI' => 'Time to Interactive',
    ];
    foreach ($metricMap as $metricKey => $metricLabel) {
        if (preg_match('/' . preg_quote($metricKey, '/') . '\s+([^|]+)/i', $lighthouseIssue['detected_value'], $metricMatch)) {
            $lighthouseMetrics[$metricLabel] = trim($metricMatch[1]);
        }
    }
}
foreach ($allIssues as $issueItem) {
    if (!in_array($issueItem['code'] ?? '', ['RENDER_BLOCKING', 'MANY_SCRIPTS', 'LARGE_PAGE_SIZE'], true)) {
        continue;
    }
    $lighthouseOpportunities[] = $issueItem;
}
if ($loadMetricIssue) {
    $performanceDiagnostics[] = $loadMetricIssue;
}
if ($pageWeightIssue) {
    $performanceDiagnostics[] = $pageWeightIssue;
}
if ($scriptIssue) {
    $performanceDiagnostics[] = $scriptIssue;
}

$severityPenaltyMap = [
    'critical' => 20,
    'high' => 10,
    'medium' => 5,
    'low' => 2,
    'info' => 0,
];
$scoreFromIssues = function (array $issues) use ($severityPenaltyMap): int {
    $penalty = 0;
    foreach ($issues as $issue) {
        $penalty += $severityPenaltyMap[$issue['severity'] ?? 'info'] ?? 0;
    }
    return max(0, 100 - min(100, $penalty));
};
$onPageSeoCodes = [
    'MISSING_TITLE','WEAK_TITLE','TITLE_TOO_LONG','TITLE_MATCHES_H1_EXACTLY',
    'MISSING_META_DESC','WEAK_META_DESC','META_DESC_TOO_LONG',
    'MISSING_H1','MULTIPLE_H1','THIN_CONTENT','LOW_INTERNAL_LINKS',
    'KEY_PAGE_MISSING_TITLE','KEY_PAGE_MISSING_META_DESC','KEY_PAGE_MISSING_H1',
    'KEY_PAGE_THIN_CONTENT','KEY_PAGE_LOW_INTERNAL_LINKS','WEAK_TOPIC_RELEVANCE'
];
$technicalSeoCodes = [
    'MISSING_CANONICAL','INVALID_CANONICAL','CANONICAL_OTHER_DOMAIN',
    'NOINDEX_TAG_FOUND','MISSING_STRUCTURED_DATA','MISSING_SITEMAP','MISSING_ROBOTS',
    'ROBOTS_BLOCKS_SITE','ROBOTS_MISSING_SITEMAP_REFERENCE',
    'MISSING_OG_TAGS','MISSING_TWITTER_CARD','MISSING_FAVICON',
    'NO_HTTPS','LIGHTHOUSE_SEO_LOW','LIGHTHOUSE_SEO_BASELINE'
];
$localSeoCodes = ['NO_ADDRESS','NO_MAP','NO_HOURS','NO_SCHEMA','NO_GBP_LINK','GBP_FOUND_EXTERNALLY','GBP_LINK_PRESENT'];
$onPageSeoIssues = array_values(array_filter($allIssues, static fn($issue) => in_array(($issue['code'] ?? ''), $onPageSeoCodes, true)));
$technicalSeoIssues = array_values(array_filter($allIssues, static fn($issue) => in_array(($issue['code'] ?? ''), $technicalSeoCodes, true)));
$localSeoIssues = array_values(array_filter($allIssues, static fn($issue) => in_array(($issue['code'] ?? ''), $localSeoCodes, true)));
$competitiveSeoCodes = ['HOMEPAGE_SEARCH_INTENT_WEAK','HOMEPAGE_LOCATION_SIGNAL_WEAK','LIMITED_CITY_SERVICE_COVERAGE','NO_LOCAL_LANDING_PAGE_SIGNAL','WEAK_TOPIC_RELEVANCE'];
$competitiveSeoIssues = array_values(array_filter($allIssues, static fn($issue) => in_array(($issue['code'] ?? ''), $competitiveSeoCodes, true)));
$onPageSeoScore = $scoreFromIssues($onPageSeoIssues);
$technicalSeoScore = $scoreFromIssues($technicalSeoIssues);
$localSeoSignalScore = $scoreFromIssues($localSeoIssues);
$competitiveSeoScore = $scoreFromIssues($competitiveSeoIssues);

$firstMatchingIssue = function (array $codes) use ($allIssues): ?array {
    foreach ($codes as $code) {
        foreach ($allIssues as $issue) {
            if (($issue['code'] ?? '') === $code) {
                return $issue;
            }
        }
    }
    return null;
};

$seoSalesSummary = null;
$seoPrimaryIssue = $firstMatchingIssue([
    'NOINDEX_TAG_FOUND',
    'ROBOTS_BLOCKS_SITE',
    'MISSING_TITLE',
    'MISSING_META_DESC',
    'HOMEPAGE_SEARCH_INTENT_WEAK',
    'LIMITED_CITY_SERVICE_COVERAGE',
    'NO_LOCAL_LANDING_PAGE_SIGNAL',
    'WEAK_TOPIC_RELEVANCE',
    'THIN_CONTENT',
    'LIGHTHOUSE_SEO_LOW',
]);

if ($seoPrimaryIssue) {
    $seoSalesSummary = match ($seoPrimaryIssue['code']) {
        'NOINDEX_TAG_FOUND', 'ROBOTS_BLOCKS_SITE' =>
            'Your biggest SEO opportunity is visibility: parts of the site may be telling search engines not to crawl or index important pages, which can stop rankings before they even start.',
        'MISSING_TITLE', 'MISSING_META_DESC' =>
            'Your biggest SEO opportunity is search snippet quality: key pages are missing core title or meta description signals, which can reduce rankings and click-through rate from Google.',
        'HOMEPAGE_SEARCH_INTENT_WEAK' =>
            'Your biggest SEO opportunity is homepage clarity: the homepage is not signaling your main services strongly enough, so search engines and visitors may not quickly understand what you offer.',
        'LIMITED_CITY_SERVICE_COVERAGE', 'NO_LOCAL_LANDING_PAGE_SIGNAL' =>
            'Your biggest SEO opportunity is local market coverage: the site needs stronger service-plus-location targeting so it can compete better for nearby searches and city-based intent.',
        'WEAK_TOPIC_RELEVANCE', 'THIN_CONTENT' =>
            'Your biggest SEO opportunity is content depth: important pages need stronger topical copy so search engines can connect your services to the searches you want to win.',
        'LIGHTHOUSE_SEO_LOW' =>
            'Your biggest SEO opportunity is technical readiness: the site is missing some of the baseline crawlability and mobile-search signals that help pages perform consistently in search.',
        default =>
            'Your biggest SEO opportunity is tightening the signals on your most important pages so search engines can better understand what you do, where you operate, and which pages should rank.',
    };
} elseif ($seo <= 60) {
    $seoSalesSummary = 'Your biggest SEO opportunity is strengthening the basics across the site so your main pages send clearer service, location, and technical trust signals to search engines.';
} elseif ($seo <= 80) {
    $seoSalesSummary = 'Your SEO foundation is workable, but the fastest gains will likely come from sharpening page intent, local relevance, and a few technical cleanup items.';
} else {
    $seoSalesSummary = 'Your SEO foundation looks solid overall. The biggest gains now will likely come from expanding service-area coverage and polishing higher-converting search snippets.';
}

$seoPageProfiles = [];
foreach ($rawIssues as $issueItem) {
    if (($issueItem['code'] ?? '') !== 'SEO_PAGE_PROFILE') {
        continue;
    }
    $evidence = (string) ($issueItem['detected_value'] ?? '');
    preg_match('/page=([^|]+)/i', $evidence, $pageMatch);
    preg_match('/type=([^|]+)/i', $evidence, $typeMatch);
    preg_match('/words=(\d+)/i', $evidence, $wordsMatch);
    preg_match('/internal_links=(\d+)/i', $evidence, $linksMatch);
    preg_match('/title=([^|]+)/i', $evidence, $titleMatch);
    preg_match('/description=([^|]+)/i', $evidence, $descMatch);
    preg_match('/h1=([^|]+)/i', $evidence, $h1Match);
    $path = trim((string) ($pageMatch[1] ?? '/'));
    $seoPageProfiles[$path] = [
        'path' => $path,
        'type' => trim((string) ($typeMatch[1] ?? 'page')),
        'words' => (int) ($wordsMatch[1] ?? 0),
        'internal_links' => (int) ($linksMatch[1] ?? 0),
        'has_title' => trim((string) ($titleMatch[1] ?? 'no')) === 'yes',
        'has_description' => trim((string) ($descMatch[1] ?? 'no')) === 'yes',
        'h1_count' => (int) ($h1Match[1] ?? 0),
        'issues' => [],
    ];
}
foreach ($allIssues as $issueItem) {
    if (($issueItem['category'] ?? '') !== 'seo') {
        continue;
    }
    $evidence = (string) ($issueItem['detected_value'] ?? '');
    if (!preg_match('/page=([^|]+)/i', $evidence, $pageMatch)) {
        continue;
    }
    $path = trim((string) ($pageMatch[1] ?? '/'));
    if (!isset($seoPageProfiles[$path])) {
        continue;
    }
    $seoPageProfiles[$path]['issues'][] = $issueItem;
}
$seoPageProfiles = array_values($seoPageProfiles);

$seoChecks = [
    [
        'label' => 'Title Tag',
        'ok_codes' => [],
        'warn_codes' => ['MISSING_TITLE', 'WEAK_TITLE', 'TITLE_TOO_LONG', 'TITLE_MATCHES_H1_EXACTLY'],
        'good' => 'Title length and wording look search-friendly.',
        'fix' => 'Tighten the title to around 50-60 characters and make it distinct from the H1.',
    ],
    [
        'label' => 'Meta Description',
        'ok_codes' => [],
        'warn_codes' => ['MISSING_META_DESC', 'WEAK_META_DESC', 'META_DESC_TOO_LONG'],
        'good' => 'Meta description is present and likely usable in search snippets.',
        'fix' => 'Write a clearer 150-160 character summary with a strong click-through angle.',
    ],
    [
        'label' => 'Heading Structure',
        'ok_codes' => [],
        'warn_codes' => ['MISSING_H1', 'MULTIPLE_H1'],
        'good' => 'Primary heading structure looks clean.',
        'fix' => 'Use one strong H1 and keep supporting headings nested below it.',
    ],
    [
        'label' => 'Canonical URL',
        'ok_codes' => [],
        'warn_codes' => ['MISSING_CANONICAL', 'INVALID_CANONICAL', 'CANONICAL_OTHER_DOMAIN'],
        'good' => 'Canonical setup looks present and reasonable.',
        'fix' => 'Use one valid canonical URL that points to the preferred version of this page.',
    ],
    [
        'label' => 'Indexability',
        'ok_codes' => [],
        'warn_codes' => ['NOINDEX_TAG_FOUND', 'ROBOTS_BLOCKS_SITE'],
        'good' => 'Nothing obvious is blocking search indexing.',
        'fix' => 'Remove noindex or sitewide crawl blocks if this page should rank.',
    ],
    [
        'label' => 'Structured Data',
        'ok_codes' => [],
        'warn_codes' => ['MISSING_STRUCTURED_DATA'],
        'good' => 'Structured data was detected.',
        'fix' => 'Add Schema.org markup like Organization, LocalBusiness, Service, FAQ, or Article where appropriate.',
    ],
    [
        'label' => 'Sitemap and Robots',
        'ok_codes' => [],
        'warn_codes' => ['MISSING_SITEMAP', 'MISSING_ROBOTS', 'ROBOTS_MISSING_SITEMAP_REFERENCE', 'ROBOTS_BLOCKS_SITE'],
        'good' => 'Crawler discovery files look present.',
        'fix' => 'Publish sitemap.xml, keep robots.txt accessible, and reference the sitemap inside robots.txt.',
    ],
    [
        'label' => 'Social Search Snippets',
        'ok_codes' => [],
        'warn_codes' => ['MISSING_OG_TAGS', 'MISSING_TWITTER_CARD', 'MISSING_FAVICON'],
        'good' => 'Sharing/snippet metadata is mostly in place.',
        'fix' => 'Add Open Graph, Twitter Card, and favicon assets so previews are consistent.',
    ],
    [
        'label' => 'Content Depth',
        'ok_codes' => [],
        'warn_codes' => ['THIN_CONTENT', 'LOW_INTERNAL_LINKS', 'KEY_PAGE_THIN_CONTENT', 'KEY_PAGE_LOW_INTERNAL_LINKS'],
        'good' => 'The page has enough content depth and internal linking to support SEO.',
        'fix' => 'Add more useful copy and link to related internal pages to strengthen topical signals.',
    ],
    [
        'label' => 'Technical SEO Baseline',
        'ok_codes' => ['LIGHTHOUSE_SEO_BASELINE'],
        'warn_codes' => ['LIGHTHOUSE_SEO_LOW'],
        'good' => 'Lighthouse SEO baseline looks healthy.',
        'fix' => 'Use Lighthouse SEO findings to improve crawlability, mobile search readiness, and metadata hygiene.',
    ],
    [
        'label' => 'Local SEO Signals',
        'ok_codes' => ['GBP_LINK_PRESENT', 'GBP_FOUND_EXTERNALLY'],
        'warn_codes' => ['NO_ADDRESS', 'NO_MAP', 'NO_HOURS', 'NO_SCHEMA', 'NO_GBP_LINK'],
        'good' => 'Core local business trust signals are present or partially connected.',
        'fix' => 'Add address, hours, map, LocalBusiness schema, and a visible Google Business Profile link.',
    ],
    [
        'label' => 'Homepage Intent',
        'ok_codes' => [],
        'warn_codes' => ['HOMEPAGE_SEARCH_INTENT_WEAK', 'HOMEPAGE_LOCATION_SIGNAL_WEAK'],
        'good' => 'The homepage appears to communicate service intent and local relevance clearly.',
        'fix' => 'Make the homepage clearer about your main services and where you serve.',
    ],
    [
        'label' => 'Service + City Coverage',
        'ok_codes' => [],
        'warn_codes' => ['LIMITED_CITY_SERVICE_COVERAGE', 'NO_LOCAL_LANDING_PAGE_SIGNAL', 'WEAK_TOPIC_RELEVANCE'],
        'good' => 'The site shows signs of pairing services with topics and local markets.',
        'fix' => 'Build out stronger service-area pages and reinforce service + location combinations in key content.',
    ],
];

$formatSeoEvidence = function (array $issue, array $check): array {
    $code = (string) ($issue['code'] ?? '');
    $detected = trim((string) ($issue['detected_value'] ?? ''));
    $explanation = trim((string) ($issue['explanation'] ?? ''));

    return match ($code) {
        'WEAK_TITLE', 'TITLE_TOO_LONG' => [
            'found' => (string) ($issue['title'] ?? $check['fix']),
            'detail' => $detected !== ''
                ? 'Current title: "' . $detected . '" (' . mb_strlen($detected) . ' characters).'
                : $explanation,
        ],
        'TITLE_MATCHES_H1_EXACTLY' => [
            'found' => (string) ($issue['title'] ?? $check['fix']),
            'detail' => $detected !== ''
                ? 'Matching title/H1 text: "' . $detected . '".'
                : $explanation,
        ],
        'WEAK_META_DESC', 'META_DESC_TOO_LONG' => [
            'found' => (string) ($issue['title'] ?? $check['fix']),
            'detail' => $detected !== ''
                ? 'Current description: "' . $detected . '" (' . mb_strlen($detected) . ' characters).'
                : $explanation,
        ],
        'MULTIPLE_H1' => [
            'found' => (string) ($issue['title'] ?? $check['fix']),
            'detail' => $detected !== ''
                ? 'H1 count found: ' . $detected . '. Best practice is 1.'
                : $explanation,
        ],
        'INVALID_CANONICAL', 'CANONICAL_OTHER_DOMAIN' => [
            'found' => (string) ($issue['title'] ?? $check['fix']),
            'detail' => $detected !== ''
                ? 'Canonical URL found: ' . $detected
                : $explanation,
        ],
        'NOINDEX_TAG_FOUND', 'ROBOTS_BLOCKS_SITE' => [
            'found' => (string) ($issue['title'] ?? $check['fix']),
            'detail' => $detected !== '' ? 'Blocking signal found: ' . $detected : $explanation,
        ],
        'LIGHTHOUSE_SEO_LOW' => [
            'found' => (string) ($issue['title'] ?? $check['fix']),
            'detail' => $detected !== '' ? 'Lighthouse SEO data: ' . $detected : $explanation,
        ],
        'LIMITED_CITY_SERVICE_COVERAGE', 'NO_LOCAL_LANDING_PAGE_SIGNAL', 'WEAK_TOPIC_RELEVANCE',
        'HOMEPAGE_SEARCH_INTENT_WEAK', 'HOMEPAGE_LOCATION_SIGNAL_WEAK',
        'KEY_PAGE_THIN_CONTENT', 'KEY_PAGE_LOW_INTERNAL_LINKS',
        'KEY_PAGE_MISSING_TITLE', 'KEY_PAGE_MISSING_META_DESC', 'KEY_PAGE_MISSING_H1' => [
            'found' => (string) ($issue['title'] ?? $check['fix']),
            'detail' => $detected !== '' ? 'Scan evidence: ' . $detected : $explanation,
        ],
        default => [
            'found' => (string) ($issue['title'] ?? $check['fix']),
            'detail' => (string) ($detected !== '' ? $detected : ($explanation !== '' ? $explanation : $check['fix'])),
        ],
    };
};

$buildSeoStatus = function (array $check) use ($issuesByCode, $formatSeoEvidence): array {
    foreach ($check['warn_codes'] as $code) {
        if (isset($issuesByCode[$code])) {
            $issue = $issuesByCode[$code];
            $severity = (string) ($issue['severity'] ?? 'medium');
            $tone = in_array($severity, ['critical', 'high'], true) ? 'danger' : 'warning';
            $evidence = $formatSeoEvidence($issue, $check);
            return [
                'tone' => $tone,
                'label' => $tone === 'danger' ? 'Needs attention' : 'Needs work',
                'found_label' => 'What we found',
                'found' => $evidence['found'],
                'detail' => $evidence['detail'],
            ];
        }
    }

    foreach ($check['ok_codes'] as $code) {
        if (isset($issuesByCode[$code])) {
            return [
                'tone' => 'success',
                'label' => 'Good',
                'found_label' => 'What we found',
                'found' => $check['good'],
                'detail' => 'No major issue was flagged for this SEO check.',
            ];
        }
    }

    return [
        'tone' => 'success',
        'label' => 'Good',
        'found_label' => 'What we found',
        'found' => $check['good'],
        'detail' => 'No major issue was flagged for this SEO check.',
    ];
};

$seoHighlights = array_values(array_filter(
    $allIssues,
    static fn($issue) => ($issue['category'] ?? '') === 'seo' && in_array(($issue['severity'] ?? 'info'), ['critical', 'high', 'medium'], true)
));
$topSeoIssue = $seoHighlights[0] ?? null;
$topBusinessIssue = null;
foreach ($sortedIssues as $issueItem) {
    if (in_array(($issueItem['category'] ?? ''), ['technical', 'conversion', 'accessibility', 'local'], true)) {
        $topBusinessIssue = $issueItem;
        break;
    }
}
$overallSummary = match (true) {
    $overall >= 85 => 'The site is in strong shape overall, with mostly polish and growth opportunities left.',
    $overall >= 70 => 'The site has a solid base, but there are still a few meaningful gaps holding it back.',
    $overall >= 50 => 'The site is workable, but several issues are likely limiting trust, rankings, or conversions.',
    default => 'The site needs attention in a few core areas before it will perform reliably as a lead generator.',
};
$seoSummary = $topSeoIssue
    ? 'Biggest SEO opportunity: ' . ($topSeoIssue['title'] ?? 'SEO issue') . '.'
    : 'SEO basics look fairly stable, so the next gains likely come from deeper content and local coverage.';
$businessSummary = $topBusinessIssue
    ? 'Biggest business impact issue: ' . ($topBusinessIssue['title'] ?? 'Performance or conversion issue') . '.'
    : 'No major non-SEO blocker stood out more than the rest in this scan.';
$fixLaneGroups = [
    'now' => [
        'label' => 'Fix Now',
        'tone' => 'danger',
        'copy' => 'Critical and high-priority issues that can affect visibility, trust, or lead flow first.',
        'issues' => array_slice($issueGroups['high'] ?? [], 0, 3),
    ],
    'next' => [
        'label' => 'Fix Next',
        'tone' => 'warning',
        'copy' => 'Meaningful improvements that can strengthen performance after the urgent items are handled.',
        'issues' => array_slice($issueGroups['medium'] ?? [], 0, 3),
    ],
    'later' => [
        'label' => 'Later',
        'tone' => 'primary',
        'copy' => 'Lower-priority cleanup items and refinements that still improve the experience over time.',
        'issues' => array_slice($issueGroups['low'] ?? [], 0, 3),
    ],
];
?>

<div id="report-export-content">
<!-- Report Hero -->
<section class="report-hero py-5" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);">
    <div class="container">
        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="<?= url('/') ?>" class="text-white-50 text-decoration-none small">
                <i class="bi bi-house me-1"></i>Home
            </a>
            <span class="text-white-50">/</span>
            <span class="text-white-50 small">Audit Report</span>
        </div>

        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h1 class="display-5 fw-bold text-white mb-2">Website Audit Report</h1>
                <p class="text-white-50 fs-5 mb-3">
                    <i class="bi bi-globe2 me-2"></i><?= e($siteUrl) ?>
                </p>
                <p class="text-white-50 mb-4"><?= e($report['summary_text'] ?? '') ?></p>
                <?php if (!empty($seoSalesSummary)): ?>
                <div class="alert border-0 mb-4" style="background:rgba(255,255,255,0.10); color:#fff;">
                    <div class="small text-uppercase fw-semibold mb-1" style="letter-spacing:.08em; opacity:.85;">SEO Opportunity</div>
                    <div><?= e($seoSalesSummary) ?></div>
                </div>
                <?php endif; ?>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-white-10 text-white px-3 py-2">
                        <i class="bi bi-calendar3 me-1"></i><?= e(date('M j, Y', strtotime($report['created_at']))) ?>
                    </span>
                    <span class="badge bg-white-10 text-white px-3 py-2">
                        <i class="bi bi-exclamation-circle me-1"></i><?= count($criticals) ?> critical issues
                    </span>
                    <span class="badge bg-white-10 text-white px-3 py-2">
                        <i class="bi bi-list-check me-1"></i><?= count($allIssues) ?> total findings
                    </span>
                </div>
                <div class="mt-4 d-flex flex-wrap gap-2">
                    <button class="btn-export-pdf no-print" type="button" data-pdf-button onclick="window.SiteScopeDownloadReportPdf && window.SiteScopeDownloadReportPdf(this)">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Export PDF
                    </button>
                    <a href="#request-help" class="btn btn-primary no-print">
                        <i class="bi bi-tools me-2"></i>Fix My Website
                    </a>
                    <?php if (!empty($gbpIssue) && !empty($extractFirstUrl($gbpIssue['detected_value'] ?? ''))): ?>
                    <a href="<?= e($extractFirstUrl($gbpIssue['detected_value'] ?? '')) ?>" class="btn btn-outline-light no-print" target="_blank" rel="noopener">
                        <i class="bi bi-geo-alt me-2"></i>Open Google Profile
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <div class="report-score-card d-inline-block">
                    <!-- Score Ring -->
                    <div class="score-ring-container mx-auto mb-3" style="width:180px;height:180px;position:relative;">
                        <svg viewBox="0 0 180 180" width="180" height="180">
                            <circle cx="90" cy="90" r="80" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="12"/>
                            <circle cx="90" cy="90" r="80" fill="none"
                                    stroke="<?= $gradeInfo['color'] ?>" stroke-width="12"
                                    stroke-dasharray="502.7"
                                    stroke-dashoffset="<?= round(502.7 * (1 - $overall/100)) ?>"
                                    stroke-linecap="round"
                                    transform="rotate(-90 90 90)"/>
                        </svg>
                        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;">
                            <div class="display-4 fw-bold text-white lh-1"><?= $overall ?></div>
                            <div class="text-white-50 small">out of 100</div>
                        </div>
                    </div>
                    <div class="grade-badge" style="background:<?= $gradeInfo['color'] ?>"><?= e($gradeInfo['grade']) ?> - <?= e($gradeInfo['label']) ?></div>
                    <?php if (!empty($comparison)): ?>
                    <div class="mt-3">
                        <span class="badge bg-white-10 text-white px-3 py-2">
                            <i class="bi bi-arrow-left-right me-1"></i>
                            Compared with <?= e(date('M j, Y', strtotime($comparison['created_at'] ?? 'now'))) ?>:
                            <?= ($comparison['score_delta'] ?? 0) >= 0 ? '+' : '' ?><?= (int) ($comparison['score_delta'] ?? 0) ?> points
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="report-tabs-shell py-3 border-bottom bg-white no-print">
    <div class="container">
        <div class="report-tabs-wrap">
            <ul class="nav report-tabs" id="reportSectionTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#report-tab-overview" type="button" role="tab" aria-controls="report-tab-overview" aria-selected="true">
                        <i class="bi bi-grid-1x2 me-2"></i>Overview
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#report-tab-seo" type="button" role="tab" aria-controls="report-tab-seo" aria-selected="false">
                        <i class="bi bi-search me-2"></i>SEO
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="performance-tab" data-bs-toggle="tab" data-bs-target="#report-tab-performance" type="button" role="tab" aria-controls="report-tab-performance" aria-selected="false">
                        <i class="bi bi-speedometer2 me-2"></i>Performance
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="findings-tab" data-bs-toggle="tab" data-bs-target="#report-tab-findings" type="button" role="tab" aria-controls="report-tab-findings" aria-selected="false">
                        <i class="bi bi-list-check me-2"></i>Findings
                    </button>
                </li>
            </ul>
        </div>
    </div>
</section>

<div class="tab-content report-tab-content" id="reportSectionTabContent">
<div class="tab-pane fade show active" id="report-tab-overview" role="tabpanel" aria-labelledby="overview-tab" tabindex="0">

<?php if (!empty($priorityIssues)): ?>
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="report-summary-panel">
            <div class="row g-4 align-items-start">
                <div class="col-lg-4">
                    <div class="summary-kicker">Executive Summary</div>
                    <h2 class="fw-bold mb-2 fs-4">What This Report Means</h2>
                    <p class="text-muted mb-0">A quick read on overall condition, the biggest SEO opportunity, and the biggest business-impact issue.</p>
                    <div class="executive-summary-list mt-3">
                        <div class="executive-summary-item">
                            <div class="executive-summary-label">Overall</div>
                            <div class="text-muted small"><?= e($overallSummary) ?></div>
                        </div>
                        <div class="executive-summary-item">
                            <div class="executive-summary-label">SEO</div>
                            <div class="text-muted small"><?= e($seoSummary) ?></div>
                        </div>
                        <div class="executive-summary-item">
                            <div class="executive-summary-label">Business Impact</div>
                            <div class="text-muted small"><?= e($businessSummary) ?></div>
                        </div>
                    </div>
                    <?php if (!empty($seoSalesSummary)): ?>
                    <div class="mt-3 p-3 rounded-3 bg-white border small">
                        <div class="fw-semibold mb-1">SEO in plain English</div>
                        <div class="text-muted"><?= e($seoSalesSummary) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-8">
                    <div class="row g-3 report-scroll-row">
                        <?php foreach ($priorityIssues as $priorityIssue): ?>
                        <div class="col-md-4">
                            <div class="summary-priority-card">
                                <span class="badge bg-<?= e($severityBadge($priorityIssue['severity'] ?? 'medium')) ?> mb-2 text-capitalize"><?= e($priorityIssue['severity'] ?? 'medium') ?></span>
                                <h3 class="summary-priority-title"><?= e($priorityIssue['title'] ?? 'Priority item') ?></h3>
                                <p class="summary-priority-copy"><?= e(mb_strimwidth((string) ($priorityIssue['explanation'] ?? ''), 0, 115, '...')) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="py-4 bg-light border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="fw-bold mb-1 fs-4">Fix Priority</h2>
                <p class="text-muted mb-0">A simple order of operations so the report feels more actionable.</p>
            </div>
        </div>
        <div class="row g-3">
            <?php foreach ($fixLaneGroups as $lane): ?>
            <div class="col-lg-4">
                <div class="fix-lane-card fix-lane-card--<?= e($lane['tone']) ?>">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                        <h3 class="h6 fw-bold mb-0"><?= e($lane['label']) ?></h3>
                        <span class="badge text-bg-<?= e($lane['tone']) ?>"><?= count($lane['issues']) ?> item<?= count($lane['issues']) !== 1 ? 's' : '' ?></span>
                    </div>
                    <p class="small text-muted mb-3"><?= e($lane['copy']) ?></p>
                    <?php if (!empty($lane['issues'])): ?>
                    <?php foreach ($lane['issues'] as $laneIssue): ?>
                    <a href="#issue-<?= (int) ($laneIssue['id'] ?? 0) ?>" class="fix-lane-item report-findings-link text-decoration-none d-block">
                        <div class="small fw-semibold text-dark"><?= e($laneIssue['title'] ?? 'Issue') ?></div>
                        <div class="small text-muted"><?= e($laneIssue['category'] ?? 'general') ?></div>
                    </a>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="fix-lane-item">
                        <div class="small text-muted mb-0">Nothing significant landed in this bucket on this scan.</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Score Breakdown -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="fw-bold mb-4 fs-4">Score Breakdown</h2>
        <div class="row g-3">
            <?php
            $cats = [
                ['label'=>'SEO',           'score'=>$seo,        'icon'=>'bi-search',          'color'=>'#2563eb'],
                ['label'=>'Accessibility',  'score'=>$a11y,       'icon'=>'bi-universal-access', 'color'=>'#7c3aed'],
                ['label'=>'Conversion',     'score'=>$conversion, 'icon'=>'bi-graph-up-arrow',   'color'=>'#f59e0b'],
                ['label'=>'Technical',      'score'=>$technical,  'icon'=>'bi-lightning',        'color'=>'#10b981'],
                ['label'=>'Local Business', 'score'=>$local,      'icon'=>'bi-geo-alt',          'color'=>'#06b6d4'],
            ];
            foreach ($cats as $cat):
                $catGrade = match(true) {
                    $cat['score'] >= 80 => '#22c55e',
                    $cat['score'] >= 60 => '#f59e0b',
                    default             => '#ef4444',
                };
            ?>
            <div class="col-md-6 col-lg">
                <div class="score-breakdown-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi <?= $cat['icon'] ?>" style="color:<?= $cat['color'] ?>;font-size:1.2rem"></i>
                            <span class="fw-semibold small"><?= $cat['label'] ?></span>
                        </div>
                        <span class="fw-bold" style="color:<?= $catGrade ?>"><?= $cat['score'] ?></span>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar" style="width:<?= $cat['score'] ?>%;background:<?= $cat['color'] ?>;"></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
</div>

<div class="tab-pane fade" id="report-tab-seo" role="tabpanel" aria-labelledby="seo-tab" tabindex="0">

<section class="py-5 bg-white border-top">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1 fs-4">SEO Breakdown</h2>
                <p class="text-muted mb-0">A more detailed view of what is helping or hurting the SEO score.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge text-bg-light border">SEO Score: <?= $seo ?>/100</span>
                <span class="badge text-bg-light border"><?= $seoIssueCount ?> SEO finding<?= $seoIssueCount !== 1 ? 's' : '' ?></span>
                <?php if ($seoCriticalCount > 0 || $seoHighCount > 0): ?>
                <span class="badge bg-warning-subtle text-warning-emphasis border"><?= $seoCriticalCount ?> critical, <?= $seoHighCount ?> high</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <?php foreach ([
                ['label' => 'On-Page SEO', 'score' => $onPageSeoScore, 'copy' => 'Titles, descriptions, headings, content depth, internal links, and topical relevance.'],
                ['label' => 'Technical SEO', 'score' => $technicalSeoScore, 'copy' => 'Indexability, canonical setup, robots/sitemap, structured data, HTTPS, and Lighthouse SEO.'],
                ['label' => 'Local SEO Signals', 'score' => $localSeoSignalScore, 'copy' => 'Address visibility, map/location signals, local schema, and Google Business Profile support.'],
                ['label' => 'Competitive Coverage', 'score' => $competitiveSeoScore, 'copy' => 'Homepage intent, city-service combinations, and local landing page strength.'],
            ] as $seoSplit): ?>
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h3 class="h6 fw-bold mb-0"><?= e($seoSplit['label']) ?></h3>
                            <span class="fw-bold" style="color:<?= e($scoreColor((int) $seoSplit['score'])) ?>"><?= e((string) $seoSplit['score']) ?>/100</span>
                        </div>
                        <div class="progress mb-2" style="height:8px;">
                            <div class="progress-bar" style="width:<?= (int) $seoSplit['score'] ?>%; background:<?= e($scoreColor((int) $seoSplit['score'])) ?>;"></div>
                        </div>
                        <p class="text-muted small mb-0"><?= e($seoSplit['copy']) ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="row g-3">
            <?php foreach ($seoChecks as $seoCheck): ?>
            <?php
            $seoStatus = $buildSeoStatus($seoCheck);
            $seoCardCollapseId = 'seo-check-' . substr(md5(($seoCheck['label'] ?? '') . '|' . ($seoStatus['found'] ?? '')), 0, 10);
            ?>
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100 seo-status-card seo-status-card--<?= e($seoStatus['tone']) ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                            <h3 class="h6 fw-bold mb-0"><?= e($seoCheck['label']) ?></h3>
                            <span class="badge text-bg-<?= e($seoStatus['tone']) ?>"><?= e($seoStatus['label']) ?></span>
                        </div>
                        <div class="seo-status-found mb-2"><?= e($seoStatus['found']) ?></div>
                        <button class="btn btn-link btn-sm seo-status-toggle collapsed p-0 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#<?= e($seoCardCollapseId) ?>" aria-expanded="false" aria-controls="<?= e($seoCardCollapseId) ?>">
                            <span class="seo-status-toggle-label">View details</span>
                            <i class="bi bi-chevron-down seo-status-toggle-icon ms-1"></i>
                        </button>
                        <div class="collapse mt-3" id="<?= e($seoCardCollapseId) ?>">
                            <div class="seo-status-detail-wrap">
                                <div class="seo-status-label"><?= e($seoStatus['found_label']) ?></div>
                                <p class="text-muted small mb-0"><?= e($seoStatus['detail']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($seoHighlights)): ?>
        <div class="mt-4 p-4 rounded-4 bg-light border">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h3 class="fw-bold fs-5 mb-0">Top SEO Priorities</h3>
                <span class="small text-muted">Most impactful issues first</span>
            </div>
            <div class="row g-3">
                <?php foreach (array_slice($seoHighlights, 0, 4) as $seoIssue): ?>
                <div class="col-md-6">
                    <div class="border rounded-4 bg-white h-100 p-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-<?= e($severityBadge($seoIssue['severity'] ?? 'medium')) ?> text-capitalize"><?= e($seoIssue['severity'] ?? 'medium') ?></span>
                            <span class="fw-semibold"><?= e($seoIssue['title'] ?? 'SEO issue') ?></span>
                        </div>
                        <p class="small text-muted mb-0"><?= e($seoIssue['explanation'] ?? '') ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($seoPageProfiles)): ?>
        <div class="mt-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h3 class="fw-bold fs-5 mb-0">Page-By-Page SEO Checks</h3>
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#seoPageChecksCollapse" aria-expanded="false" aria-controls="seoPageChecksCollapse">
                    Show page details
                </button>
            </div>
            <p class="small text-muted mb-3">Homepage, contact, and service pages scanned individually.</p>
            <div class="collapse" id="seoPageChecksCollapse">
            <div class="row g-3">
                <?php foreach ($seoPageProfiles as $pageProfile): ?>
                <?php
                $pageIssues = $pageProfile['issues'] ?? [];
                $pageScore = $scoreFromIssues($pageIssues);
                $pageTypeLabel = match ($pageProfile['type']) {
                    'home' => 'Homepage',
                    'contact' => 'Contact Page',
                    'service' => 'Service Page',
                    'about' => 'About Page',
                    default => 'Page',
                };
                ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                <div>
                                    <div class="small text-muted"><?= e($pageTypeLabel) ?></div>
                                    <h4 class="h6 fw-bold mb-0"><?= e($pageProfile['path']) ?></h4>
                                </div>
                                <span class="badge text-bg-light border"><?= e((string) $pageScore) ?>/100</span>
                            </div>
                            <div class="progress mb-3" style="height:8px;">
                                <div class="progress-bar" style="width:<?= (int) $pageScore ?>%; background:<?= e($scoreColor((int) $pageScore)) ?>;"></div>
                            </div>
                            <div class="small text-muted mb-3">
                                <?= (int) ($pageProfile['words'] ?? 0) ?> words
                                | <?= (int) ($pageProfile['internal_links'] ?? 0) ?> internal links
                                | H1 count: <?= (int) ($pageProfile['h1_count'] ?? 0) ?>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge <?= !empty($pageProfile['has_title']) ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">Title</span>
                                <span class="badge <?= !empty($pageProfile['has_description']) ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">Meta Description</span>
                                <span class="badge <?= (int) ($pageProfile['h1_count'] ?? 0) > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">H1</span>
                            </div>
                            <?php if (!empty($pageIssues)): ?>
                            <div class="small text-muted mb-1">Top issues:</div>
                            <?php foreach (array_slice($pageIssues, 0, 3) as $pageIssue): ?>
                            <div class="small mb-1">
                                <span class="badge bg-<?= e($severityBadge($pageIssue['severity'] ?? 'medium')) ?> me-1 text-capitalize"><?= e($pageIssue['severity'] ?? 'medium') ?></span>
                                <?= e($pageIssue['title'] ?? 'SEO issue') ?>
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <div class="small text-success">No major SEO issues were flagged on this page during the sweep.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
</div>

<div class="tab-pane fade" id="report-tab-performance" role="tabpanel" aria-labelledby="performance-tab" tabindex="0">

<?php if (!empty($lighthouseMetrics) || !empty($comparison)): ?>
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="row g-4">
            <?php if (!empty($lighthouseMetrics)): ?>
            <div class="col-lg-7">
                <h2 class="fw-bold mb-3 fs-4">
                    <i class="bi bi-speedometer2 me-2 text-primary"></i>Performance Snapshot
                </h2>
                <div class="row g-3">
                    <?php foreach ($lighthouseMetrics as $metricLabel => $metricValue): ?>
                    <div class="col-sm-6 col-xl-4">
                        <div class="report-metric-card h-100">
                            <div class="report-metric-label"><?= e($metricLabel) ?></div>
                            <div class="report-metric-value"><?= e($metricValue) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($lighthouseOpportunities)): ?>
                <div class="mt-3 p-3 rounded-3 bg-light border">
                    <div class="small text-uppercase fw-bold text-muted mb-2">Top Performance Opportunities</div>
                    <?php foreach (array_slice($lighthouseOpportunities, 0, 3) as $opportunity): ?>
                    <div class="small mb-2">
                        <span class="fw-semibold"><?= e($opportunity['title']) ?></span>
                        <?php if (!empty($opportunity['detected_value'])): ?>
                        <span class="text-muted">- <?= e($opportunity['detected_value']) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($comparison)): ?>
            <div class="col-lg-5">
                <h2 class="fw-bold mb-3 fs-4">
                    <i class="bi bi-arrow-repeat me-2 text-primary"></i>Rescan Comparison
                </h2>
                <div class="comparison-card h-100">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <div class="text-muted small">Previous scan</div>
                            <div class="fw-semibold"><?= e(date('M j, Y', strtotime($comparison['created_at'] ?? 'now'))) ?></div>
                        </div>
                        <span class="badge <?= ($comparison['score_delta'] ?? 0) >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
                            <?= ($comparison['score_delta'] ?? 0) >= 0 ? '+' : '' ?><?= (int) ($comparison['score_delta'] ?? 0) ?> points
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Current score</span>
                        <span class="fw-bold"><?= $overall ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Previous score</span>
                        <span class="fw-bold"><?= e($comparison['report']['overall_score'] ?? '0') ?></span>
                    </div>
                    <?php if (!empty($comparison['report']['report_token'])): ?>
                    <a href="<?= url('report/' . $comparison['report']['report_token']) ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-clock-history me-1"></i>Open Previous Report
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($primarySpeedData) || !empty($lighthouseMetrics) || !empty($lighthouseOpportunities) || !empty($performanceDiagnostics)): ?>
<section class="py-5 bg-light border-bottom">
    <div class="container">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-2 fs-3">
                    <i class="bi bi-speedometer2 me-2 text-primary"></i>Speed Analysis
                </h2>
                <p class="text-muted mb-0">A quick speed snapshot showing how fast the site feels, what matters most, and the biggest opportunities to improve it.</p>
            </div>
            <div class="text-muted small">
                Captured on <?= e(date('M j, Y, g:i A', strtotime($report['created_at'] ?? 'now'))) ?>
            </div>
        </div>

        <?php if (!empty($primarySpeedData)): ?>
        <?php
            $categories = $primarySpeedData['categories'] ?? [];
            $metricsMap = [
                'First Contentful Paint' => $primarySpeedData['metrics']['fcp'] ?? '',
                'Largest Contentful Paint' => $primarySpeedData['metrics']['lcp'] ?? '',
                'Total Blocking Time' => $primarySpeedData['metrics']['tbt'] ?? '',
                'Cumulative Layout Shift' => $primarySpeedData['metrics']['cls'] ?? '',
                'Speed Index' => $primarySpeedData['metrics']['speed_index'] ?? '',
                'Time to Interactive' => $primarySpeedData['metrics']['tti'] ?? '',
            ];
            $finalSpeedScreenshot = $primarySpeedData['screenshots']['final'] ?? '';
        ?>
        <div class="row g-3 mb-4 report-scroll-row report-scroll-row--cards">
            <div class="col-sm-6 col-xl-3">
                <div class="speed-score-card speed-score-card--performance">
                    <div class="speed-score-label">Performance</div>
                    <div class="speed-score-value"><?= e((string) ($categories['performance'] ?? $primarySpeedData['score'] ?? 0)) ?>/100</div>
                    <div class="speed-score-note"><?= e($formatPsiScoreLabel($categories['performance'] ?? $primarySpeedData['score'] ?? null)) ?> - <?= e(ucfirst((string) $primarySpeedStrategy)) ?> speed snapshot</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="speed-score-card">
                    <div class="speed-score-label">Accessibility</div>
                    <div class="speed-score-value"><?= e((string) ($categories['accessibility'] ?? 'N/A')) ?><?= isset($categories['accessibility']) ? '/100' : '' ?></div>
                    <div class="speed-score-note"><?= e($formatPsiScoreLabel($categories['accessibility'] ?? null)) ?> - overall experience signal</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="speed-score-card">
                    <div class="speed-score-label">Best Practices</div>
                    <div class="speed-score-value"><?= e((string) ($categories['best_practices'] ?? 'N/A')) ?><?= isset($categories['best_practices']) ? '/100' : '' ?></div>
                    <div class="speed-score-note"><?= e($formatPsiScoreLabel($categories['best_practices'] ?? null)) ?> - technical quality checks</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="speed-score-card">
                    <div class="speed-score-label">SEO</div>
                    <div class="speed-score-value"><?= e((string) ($categories['seo'] ?? 'N/A')) ?><?= isset($categories['seo']) ? '/100' : '' ?></div>
                    <div class="speed-score-note"><?= e($formatPsiScoreLabel($categories['seo'] ?? null)) ?> - search visibility basics</div>
                </div>
            </div>
        </div>

        <div class="speed-guidance-strip mb-4">
            <div class="speed-guidance-chip speed-guidance-chip--good">
                <strong>Good</strong>
                <span>Fast, stable, and healthy for most visitors.</span>
            </div>
            <div class="speed-guidance-chip speed-guidance-chip--needs">
                <strong>Needs work</strong>
                <span>Usable, but there is clear room to improve speed.</span>
            </div>
            <div class="speed-guidance-chip speed-guidance-chip--poor">
                <strong>Poor</strong>
                <span>Likely to frustrate visitors and hurt conversions.</span>
            </div>
        </div>

        <?php if (!empty($finalSpeedScreenshot)): ?>
        <div class="psi-final-shot mb-4">
            <img src="<?= e($finalSpeedScreenshot) ?>" alt="<?= e(ucfirst((string) $primarySpeedStrategy)) ?> Lighthouse screenshot" class="img-fluid rounded-3 border">
        </div>
        <?php endif; ?>

        <div class="accordion speed-accordion" id="speedAnalysisAccordion">
            <div class="accordion-item speed-accordion-item">
                <h2 class="accordion-header" id="speedMetricsHeading">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#speedMetricsCollapse" aria-expanded="true">
                        Key Metrics
                    </button>
                </h2>
                <div id="speedMetricsCollapse" class="accordion-collapse collapse show" data-bs-parent="#speedAnalysisAccordion">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <?php foreach ($metricsMap as $metricLabel => $metricValue): ?>
                            <?php if ($metricValue === '') continue; ?>
                            <?php $metricMeta = $metricTone($metricLabel, (string) $metricValue); ?>
                            <div class="col-sm-6 col-xl-4">
                                <div class="speed-metric-detail-card">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div class="speed-metric-title"><?= e($metricLabel) ?></div>
                                        <span class="badge text-bg-<?= e($metricMeta['tone']) ?>"><?= e($metricMeta['label']) ?></span>
                                    </div>
                                    <div class="speed-metric-value"><?= e($metricValue) ?></div>
                                    <?php if (!empty($metricGuides[$metricLabel])): ?>
                                    <div class="speed-metric-copy"><?= e($metricGuides[$metricLabel]) ?></div>
                                    <?php endif; ?>
                                    <?php if (isset($metricThresholds[$metricLabel])): ?>
                                    <div class="speed-metric-range">
                                        Good: <= <?= e((string) $metricThresholds[$metricLabel]['good']) ?>
                                        <?php if ($metricLabel === 'Cumulative Layout Shift'): ?>
                                        | Needs work: <= <?= e((string) $metricThresholds[$metricLabel]['needs']) ?>
                                        <?php elseif (in_array($metricLabel, ['Total Blocking Time'], true)): ?>
                                        ms | Needs work: <= <?= e((string) $metricThresholds[$metricLabel]['needs']) ?> ms
                                        <?php else: ?>
                                        s | Needs work: <= <?= e((string) $metricThresholds[$metricLabel]['needs']) ?> s
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($primarySpeedData['opportunities'])): ?>
            <div class="accordion-item speed-accordion-item">
                <h2 class="accordion-header" id="speedOpportunitiesHeading">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#speedOpportunitiesCollapse" aria-expanded="false">
                        Top Opportunities
                    </button>
                </h2>
                <div id="speedOpportunitiesCollapse" class="accordion-collapse collapse" data-bs-parent="#speedAnalysisAccordion">
                    <div class="accordion-body">
                        <?php foreach (array_slice($primarySpeedData['opportunities'], 0, 4) as $opportunity): ?>
                        <div class="speed-detail-row">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-semibold"><?= e($opportunity['title'] ?? '') ?></div>
                                    <?php if (!empty($opportunity['description'])): ?>
                                    <div class="text-muted small mt-1"><?= e(strip_tags((string) $opportunity['description'])) ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php $oppScore = isset($opportunity['score']) && is_numeric($opportunity['score']) ? (int) round(((float) $opportunity['score']) * 100) : null; ?>
                                <span class="badge text-bg-<?= e($formatPsiScoreTone($oppScore)) ?>"><?= $oppScore !== null ? e((string) $oppScore) . '/100' : 'Audit' ?></span>
                            </div>
                            <?php if (!empty($opportunity['display_value'])): ?>
                            <div class="small text-muted mt-2"><?= e($opportunity['display_value']) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($primarySpeedData['diagnostics']) || !empty($performanceDiagnostics)): ?>
            <div class="accordion-item speed-accordion-item">
                <h2 class="accordion-header" id="speedDiagnosticsHeading">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#speedDiagnosticsCollapse" aria-expanded="false">
                        More Details
                    </button>
                </h2>
                <div id="speedDiagnosticsCollapse" class="accordion-collapse collapse" data-bs-parent="#speedAnalysisAccordion">
                    <div class="accordion-body">
                        <?php foreach (array_slice($primarySpeedData['diagnostics'] ?? [], 0, 4) as $diagnostic): ?>
                        <div class="speed-detail-row">
                            <div class="fw-semibold"><?= e($diagnostic['title'] ?? '') ?></div>
                            <?php if (!empty($diagnostic['description'])): ?>
                            <div class="text-muted small mt-1"><?= e(strip_tags((string) $diagnostic['description'])) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($diagnostic['display_value'])): ?>
                            <div class="small text-muted mt-2"><?= e($diagnostic['display_value']) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        <?php foreach ($performanceDiagnostics as $diagnostic): ?>
                        <div class="speed-detail-row">
                            <div class="fw-semibold"><?= e($diagnostic['title']) ?></div>
                            <?php if (!empty($diagnostic['explanation'])): ?>
                            <div class="text-muted small mt-1"><?= e($diagnostic['explanation']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($diagnostic['detected_value'])): ?>
                            <div class="small text-muted mt-2">Captured value: <?= e($diagnostic['detected_value']) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="row g-3 mb-4">
            <?php if (!empty($lighthouseMetrics['Performance'])): ?>
            <div class="col-sm-6 col-xl-3">
                <div class="speed-score-card speed-score-card--performance">
                    <div class="speed-score-label">Performance</div>
                    <div class="speed-score-value"><?= e($lighthouseMetrics['Performance']) ?></div>
                    <?php preg_match('/(\d+)/', (string) $lighthouseMetrics['Performance'], $fallbackPerfMatch); ?>
                    <?php $fallbackPerfScore = isset($fallbackPerfMatch[1]) ? (int) $fallbackPerfMatch[1] : null; ?>
                    <div class="speed-score-note"><?= e($formatPsiScoreLabel($fallbackPerfScore)) ?> - mobile Lighthouse score</div>
                </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($loadMetricIssue['detected_value'])): ?>
            <div class="col-sm-6 col-xl-3">
                <div class="speed-score-card">
                    <div class="speed-score-label">Response Time</div>
                    <div class="speed-score-value"><?= e($loadMetricIssue['detected_value']) ?></div>
                    <div class="speed-score-note">Server response baseline</div>
                </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($pageWeightIssue['detected_value'])): ?>
            <div class="col-sm-6 col-xl-3">
                <div class="speed-score-card">
                    <div class="speed-score-label">Page Weight</div>
                    <div class="speed-score-value"><?= e($pageWeightIssue['detected_value']) ?></div>
                    <div class="speed-score-note">How heavy the page is</div>
                </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($scriptIssue['detected_value'])): ?>
            <div class="col-sm-6 col-xl-3">
                <div class="speed-score-card">
                    <div class="speed-score-label">Script Load</div>
                    <div class="speed-score-value"><?= e($scriptIssue['detected_value']) ?></div>
                    <div class="speed-score-note">JavaScript load snapshot</div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php if (!empty($lighthouseMetrics)): ?>
        <div class="accordion speed-accordion" id="speedFallbackAccordion">
            <div class="accordion-item speed-accordion-item">
                <h2 class="accordion-header" id="speedFallbackMetricsHeading">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#speedFallbackMetricsCollapse" aria-expanded="true">
                        Key Metrics
                    </button>
                </h2>
                <div id="speedFallbackMetricsCollapse" class="accordion-collapse collapse show" data-bs-parent="#speedFallbackAccordion">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <?php foreach ($lighthouseMetrics as $metricLabel => $metricValue): ?>
                            <?php $metricMeta = $metricTone($metricLabel, (string) $metricValue); ?>
                            <div class="col-sm-6 col-xl-4">
                                <div class="speed-metric-detail-card">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div class="speed-metric-title"><?= e($metricLabel) ?></div>
                                        <span class="badge text-bg-<?= e($metricMeta['tone']) ?>"><?= e($metricMeta['label']) ?></span>
                                    </div>
                                    <div class="speed-metric-value"><?= e($metricValue) ?></div>
                                    <?php if (!empty($metricGuides[$metricLabel])): ?>
                                    <div class="speed-metric-copy"><?= e($metricGuides[$metricLabel]) ?></div>
                                    <?php endif; ?>
                                    <?php if (isset($metricThresholds[$metricLabel])): ?>
                                    <div class="speed-metric-range">
                                        Good: <= <?= e((string) $metricThresholds[$metricLabel]['good']) ?>
                                        <?php if ($metricLabel === 'Cumulative Layout Shift'): ?>
                                        | Needs work: <= <?= e((string) $metricThresholds[$metricLabel]['needs']) ?>
                                        <?php elseif (in_array($metricLabel, ['Total Blocking Time'], true)): ?>
                                        ms | Needs work: <= <?= e((string) $metricThresholds[$metricLabel]['needs']) ?> ms
                                        <?php else: ?>
                                        s | Needs work: <= <?= e((string) $metricThresholds[$metricLabel]['needs']) ?> s
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (!empty($performanceDiagnostics)): ?>
            <div class="accordion-item speed-accordion-item">
                <h2 class="accordion-header" id="speedFallbackDetailsHeading">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#speedFallbackDetailsCollapse" aria-expanded="false">
                        More Details
                    </button>
                </h2>
                <div id="speedFallbackDetailsCollapse" class="accordion-collapse collapse" data-bs-parent="#speedFallbackAccordion">
                    <div class="accordion-body">
                        <?php foreach ($performanceDiagnostics as $diagnostic): ?>
                        <div class="speed-detail-row">
                            <div class="fw-semibold"><?= e($diagnostic['title']) ?></div>
                            <?php if (!empty($diagnostic['explanation'])): ?>
                            <div class="text-muted small mt-1"><?= e($diagnostic['explanation']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($diagnostic['detected_value'])): ?>
                            <div class="small text-muted mt-2">Captured value: <?= e($diagnostic['detected_value']) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

</div>

<div class="tab-pane fade" id="report-tab-findings" role="tabpanel" aria-labelledby="findings-tab" tabindex="0">

<!-- Screenshot Preview -->
<?php if (!empty($screenshotUrl)):
    // Issue codes that are most likely to manifest as visible problems on the screenshot.
    // Keeping this list here makes it easy to extend without touching business logic.
    $visualIssueCodes = [
        'MISSING_FAVICON', 'MISSING_OG_TAGS', 'MISSING_VIEWPORT',
        'MISSING_ALT', 'MISSING_TITLE', 'NO_CTA',
        'NO_PHONE', 'NO_CONTACT_FORM', 'NO_TRUST_BADGES',
    ];
    $visualIssues = array_filter($allIssues, fn($i) => in_array($i['code'], $visualIssueCodes, true));
    $visualMarkers = $buildScreenshotMarkers(array_values($visualIssues));
?>
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
            <h2 class="fw-bold mb-0 fs-4">
                <i class="bi bi-camera me-2 text-primary"></i>Website Screenshot
            </h2>
            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#reportScreenshotPanel" aria-expanded="false">
                View Screenshot
            </button>
        </div>
        <div id="reportScreenshotPanel" class="collapse">
        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <div class="screenshot-wrapper position-relative rounded-3 overflow-hidden shadow"
                     style="border:1px solid #e2e8f0;">
                    <!-- Browser chrome bar -->
                    <div class="screenshot-toolbar d-flex align-items-center gap-2 px-3 py-2"
                         style="background:#f1f5f9;border-bottom:1px solid #e2e8f0;">
                        <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#ef4444;"></span>
                        <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#f59e0b;"></span>
                        <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#22c55e;"></span>
                        <div class="flex-grow-1 rounded px-2 py-1 small text-muted text-truncate ms-2"
                             style="background:#fff;border:1px solid #e2e8f0;font-size:.75rem;">
                            <i class="bi bi-lock-fill me-1 text-success"></i><?= e($siteUrl) ?>
                        </div>
                    </div>
                    <a href="<?= e($screenshotUrl) ?>" target="_blank" rel="noopener" title="Open full screenshot">
                        <img src="<?= e($screenshotUrl) ?>"
                             alt="Screenshot of <?= e($siteUrl) ?>"
                             class="d-block w-100"
                             style="max-height:480px;object-fit:cover;object-position:top;"
                             loading="lazy"
                             onerror="this.closest('.screenshot-wrapper').style.display='none'">
                    </a>
                    <?php if (!empty($visualMarkers)): ?>
                    <div class="screenshot-markers" aria-hidden="true">
                        <?php foreach ($visualMarkers as $marker): ?>
                        <button type="button" class="screenshot-marker"
                                style="top:<?= e($marker['top']) ?>;left:<?= e($marker['left']) ?>;"
                                title="<?= e($marker['title']) ?>">
                            <?= (int) $marker['number'] ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <p class="text-muted small mt-2 no-pdf">
                    <i class="bi bi-info-circle me-1"></i>
                    Screenshot captured at the time of the audit. Some visual elements may differ based on dynamic content.
                    <a href="<?= e($screenshotUrl) ?>" target="_blank" rel="noopener" class="ms-1">View full size <i class="bi bi-box-arrow-up-right"></i></a>
                </p>
                <?php if (!empty($visualIssues)): ?>
                <div class="p-3 rounded-3 bg-light border">
                    <div class="small text-uppercase fw-bold text-muted mb-2">Visual Callouts</div>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach (array_slice($visualIssues, 0, 5) as $vi): ?>
                        <?php $visualLocator = $parseLocatorEvidence($vi['detected_value'] ?? ''); ?>
                        <span class="badge text-bg-light border">
                            <?= e($vi['title']) ?>
                            <?php if (!empty($visualLocator['pages'])): ?>
                            <span class="text-muted"> @ <?= e(implode(', ', $visualLocator['pages'])) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($visualLocator['zones'])): ?>
                            <span class="text-muted"> (<?= e($visualLocator['zones'][0]) ?>)</span>
                            <?php endif; ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($visualMarkers)): ?>
                <div class="annotation-legend card border-0 bg-light mt-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3"><i class="bi bi-crosshair me-2 text-primary"></i>Approximate Screenshot Markers</h6>
                        <?php foreach ($visualMarkers as $marker): ?>
                        <div class="annotation-legend-item">
                            <span class="annotation-badge"><?= (int) $marker['number'] ?></span>
                            <div>
                                <div class="small fw-semibold"><?= e($marker['title']) ?></div>
                                <div class="small text-muted text-break">
                                    <?= !empty($marker['pages']) ? e(implode(', ', $marker['pages'])) : 'Current page' ?>
                                    <?php if (!empty($marker['zone'])): ?>
                                    - <?= e(ucfirst($marker['zone'])) ?> area
                                    <?php endif; ?>
                                    <?php if (!empty($marker['elements'][0])): ?>
                                    - <?= e($marker['elements'][0]) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 bg-light no-pdf">
                    <div class="card-body">
                            <h6 class="fw-semibold mb-3">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Visual Issues Found
                            </h6>
                        <?php if (!empty($gbpIssue) && !empty($extractFirstUrl($gbpIssue['detected_value'] ?? ''))): ?>
                        <a href="<?= e($extractFirstUrl($gbpIssue['detected_value'] ?? '')) ?>" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm w-100 mb-3">
                            <i class="bi bi-geo-alt me-2"></i>View Google Business Profile
                        </a>
                        <?php endif; ?>
                        <?php
                        if (!empty($visualIssues)):
                            foreach (array_slice($visualIssues, 0, 5) as $vi):
                                $viBadge = match($vi['severity']) {
                                    'critical' => 'danger',
                                    'high'     => 'warning',
                                    'medium'   => 'primary',
                                    'low'      => 'info',
                                    default    => 'secondary',
                                };
                        ?>
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <span class="badge bg-<?= $viBadge ?> mt-1 flex-shrink-0"><?= e($vi['severity']) ?></span>
                            <div>
                                <p class="mb-0 small fw-semibold"><?= e($vi['title']) ?></p>
                                <?php if (!empty($vi['explanation'])): ?>
                                <p class="mb-0 text-muted" style="font-size:.75rem;"><?= e(mb_strimwidth($vi['explanation'], 0, 100, '...')) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                            endforeach;
                            if (count($visualIssues) > 5):
                        ?>
                        <p class="text-muted small mb-0">...and <?= count($visualIssues) - 5 ?> more. See findings below.</p>
                        <?php
                            endif;
                        else:
                        ?>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            No common visual issues flagged.
                        </p>
                        <?php endif; ?>
                        <a href="#issues-section" class="btn btn-sm btn-outline-primary mt-3 w-100 report-findings-link">
                            <i class="bi bi-list-check me-1"></i>View All Findings
                        </a>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Issues List -->
<section id="issues-section" class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Issues Column -->
            <div class="col-lg-8">
                <h2 class="fw-bold mb-4 fs-4">
                    <i class="bi bi-list-check me-2 text-primary"></i>
                    All Findings (<?= count($allIssues) ?>)
                </h2>
                <?php if ($pageFlashSuccess && !str_contains((string) $pageFlashSuccess, 'in touch')): ?>
                <div class="alert alert-success"><?= e($pageFlashSuccess) ?></div>
                <?php endif; ?>
                <?php if ($pageFlashError && !str_contains((string) $pageFlashError, 'in touch')): ?>
                <div class="alert alert-danger"><?= e($pageFlashError) ?></div>
                <?php endif; ?>

                <?php if (empty($allIssues)): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Excellent! No significant issues were found on your website.
                </div>
                <?php endif; ?>

                <?php if (!empty($topFindingIssues)): ?>
                <div class="findings-toplist mb-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h3 class="fw-bold fs-5 mb-1">Top 5 Issues To Review First</h3>
                            <p class="text-muted small mb-0">Start here, then open the full findings list if you want every item.</p>
                        </div>
                        <?php if (count($allIssues) > count($topFindingIssues)): ?>
                        <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#allFindingsCollapse" aria-expanded="false" aria-controls="allFindingsCollapse">
                            Show all findings
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="row g-3">
                        <?php foreach ($topFindingIssues as $topIssue): ?>
                        <div class="col-md-6">
                            <a href="#issue-<?= (int) ($topIssue['id'] ?? 0) ?>" class="summary-priority-card findings-top-card report-findings-link text-decoration-none d-block">
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                    <span class="badge bg-<?= e($severityBadge($topIssue['severity'] ?? 'medium')) ?> text-capitalize"><?= e($topIssue['severity'] ?? 'medium') ?></span>
                                    <span class="small text-muted text-capitalize"><?= e($topIssue['category'] ?? 'general') ?></span>
                                </div>
                                <h4 class="summary-priority-title mb-1"><?= e($topIssue['title'] ?? 'Issue') ?></h4>
                                <p class="summary-priority-copy"><?= e(mb_strimwidth((string) ($topIssue['explanation'] ?? ''), 0, 135, '...')) ?></p>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="collapse <?= count($allIssues) <= count($topFindingIssues) ? 'show' : '' ?>" id="allFindingsCollapse">
                <div class="accordion issue-group-accordion" id="issueGroupAccordion">
                <?php
                $groupMeta = [
                    'high' => ['label' => 'High Priority', 'badge' => 'warning'],
                    'medium' => ['label' => 'Medium Priority', 'badge' => 'primary'],
                    'low' => ['label' => 'Low Priority', 'badge' => 'info'],
                ];
                $groupIndex = 0;
                foreach ($issueGroups as $groupKey => $sevIssues):
                    if (empty($sevIssues)) continue;
                    $badgeClass = $groupMeta[$groupKey]['badge'];
                    $isExpanded = $groupIndex === 0;
                ?>
                <div class="accordion-item border rounded-4 overflow-hidden mb-3">
                    <h2 class="accordion-header" id="issue-group-heading-<?= e($groupKey) ?>">
                        <button class="accordion-button issue-group-toggle <?= $isExpanded ? '' : 'collapsed' ?>" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#issue-group-collapse-<?= e($groupKey) ?>"
                                aria-expanded="<?= $isExpanded ? 'true' : 'false' ?>">
                            <span class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-<?= $badgeClass ?>"><?= e($groupMeta[$groupKey]['label']) ?></span>
                                <span class="text-muted small"><?= count($sevIssues) ?> issue<?= count($sevIssues) !== 1 ? 's' : '' ?></span>
                            </span>
                        </button>
                    </h2>
                    <div id="issue-group-collapse-<?= e($groupKey) ?>" class="accordion-collapse collapse <?= $isExpanded ? 'show' : '' ?>" data-bs-parent="#issueGroupAccordion">
                        <div class="accordion-body bg-light-subtle">
                    <?php foreach ($sevIssues as $issue): ?>
                    <?php
                    $issueLocator = $parseLocatorEvidence($issue['detected_value'] ?? '');
                    $issuePaths = $issueLocator['pages'] ?? [];
                    $issueZones = $issueLocator['zones'] ?? [];
                    $issueElements = $issueLocator['elements'] ?? [];
                    $issueFeedback = $feedbackSummary[(int) ($issue['id'] ?? 0)] ?? ['incorrect' => 0, 'helpful' => 0];
                    ?>
                    <div class="issue-card border-start border-<?= $badgeClass ?> border-3 mb-3" id="issue-<?= (int) ($issue['id'] ?? 0) ?>">
                        <div class="d-flex align-items-start gap-3">
                            <div class="issue-icon text-<?= $badgeClass ?>">
                                <i class="bi <?= $categoryIcon($issue['category']) ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                    <h6 class="fw-bold mb-0"><?= e($issue['title']) ?></h6>
                                    <span class="badge bg-light text-muted text-capitalize small"><?= e($issue['category']) ?></span>
                                </div>
                                <p class="text-muted small mb-2"><?= e($issue['explanation']) ?></p>
                                <?php if (!empty($issue['detected_value'])): ?>
                                <div class="detected-value mb-2">
                                    <small class="text-muted"><strong>What we saw:</strong> <?= e($issue['detected_value']) ?></small>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($issuePaths) || !empty($issueZones)): ?>
                                <div class="issue-locator mb-2">
                                    <small class="text-muted d-block mb-1"><strong>Where this likely appears:</strong></small>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($issuePaths as $issuePath): ?>
                                        <span class="badge text-bg-light border"><?= e($issuePath) ?></span>
                                        <?php endforeach; ?>
                                        <?php foreach ($issueZones as $issueZone): ?>
                                        <span class="badge text-bg-light border">Zone: <?= e($issueZone) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($issueElements)): ?>
                                <div class="issue-locator mb-2">
                                    <small class="text-muted d-block mb-1"><strong>Page element hints:</strong></small>
                                    <?php foreach ($issueElements as $issueElement): ?>
                                    <div class="detected-value mb-1"><small class="text-muted"><?= e($issueElement) ?></small></div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                <div class="accordion accordion-sm" id="acc-<?= e($issue['code']) ?>">
                                    <div class="accordion-item border-0 bg-transparent">
                                        <button class="accordion-button collapsed bg-transparent p-0 py-1 text-primary small fw-semibold shadow-none" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#fix-<?= e($issue['code']) ?>-<?= substr(md5(serialize($issue)), 0, 6) ?>">
                                            <i class="bi bi-chevron-right me-1 acc-icon"></i>Why this matters and how to fix it
                                        </button>
                                        <div id="fix-<?= e($issue['code']) ?>-<?= substr(md5(serialize($issue)), 0, 6) ?>" class="collapse">
                                            <div class="pt-2 pb-1 ps-3">
                                                <?php if (!empty($issue['why_it_matters'])): ?>
                                                <p class="small mb-2"><strong>Why it matters:</strong> <?= e($issue['why_it_matters']) ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($issue['how_to_fix'])): ?>
                                                <p class="small mb-2"><strong>How to fix:</strong> <?= e($issue['how_to_fix']) ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($issue['business_impact'])): ?>
                                                <p class="small mb-0 text-warning-emphasis"><i class="bi bi-graph-down me-1"></i><?= e($issue['business_impact']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="issue-feedback mt-3 pt-3 border-top no-pdf">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="small text-muted">
                                            Help us tune the scanner:
                                            <?php if (!empty($issueFeedback['incorrect']) || !empty($issueFeedback['helpful'])): ?>
                                            <span class="ms-1"><?= (int) $issueFeedback['helpful'] ?> helpful, <?= (int) $issueFeedback['incorrect'] ?> incorrect</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <form method="POST" action="<?= url('report/' . $report['report_token'] . '/feedback') ?>" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="issue_id" value="<?= (int) ($issue['id'] ?? 0) ?>">
                                                <input type="hidden" name="feedback_type" value="helpful">
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    <i class="bi bi-hand-thumbs-up me-1"></i>Helpful
                                                </button>
                                            </form>
                                            <form method="POST" action="<?= url('report/' . $report['report_token'] . '/feedback') ?>" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="issue_id" value="<?= (int) ($issue['id'] ?? 0) ?>">
                                                <input type="hidden" name="feedback_type" value="incorrect">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-flag me-1"></i>This looks wrong
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php $groupIndex++; endforeach; ?>
                </div>
                </div>
            </div>

            <!-- Sidebar CTA -->
            <div class="col-lg-4">
                <div class="sticky-top report-sidebar no-pdf" style="top:96px;">

                    <!-- Fix CTA Card -->
                    <div class="cta-sidebar-card mb-4">
                        <div class="cta-card-header">
                            <i class="bi bi-wrench-adjustable-circle-fill fs-2 mb-2"></i>
                            <h5 class="fw-bold">Need Help Fixing These Issues?</h5>
                            <p class="small opacity-75 mb-0">Our team can fix every issue in this report for you.</p>
                        </div>
                        <div class="cta-card-body">
                            <a href="#request-help" class="btn btn-primary w-100 mb-2">
                                <i class="bi bi-wrench-adjustable-circle-fill me-2"></i>Request Help Fixing This
                            </a>
                            <a href="<?= url('fix-my-website') ?>" class="btn btn-outline-primary w-100 mb-2">
                                <i class="bi bi-tools me-2"></i>View Fix Options
                            </a>
                            <a href="<?= url('contact') ?>" class="btn btn-outline-secondary w-100 mb-2">
                                <i class="bi bi-chat-dots me-2"></i>Contact Us
                            </a>
                            <div class="divider-text">or choose a service</div>
                            <?php
                            $ctaServices = [
                                ['icon'=>'bi-search','href'=>'contact','label'=>'Improve My SEO'],
                                ['icon'=>'bi-universal-access','href'=>'contact','label'=>'Accessibility Review'],
                                ['icon'=>'bi-graph-up','href'=>'contact','label'=>'More Leads & Conversions'],
                                ['icon'=>'bi-palette','href'=>'contact','label'=>'Redesign My Site'],
                            ];
                            foreach ($ctaServices as $s): ?>
                            <a href="<?= url($s['href']) ?>?service=<?= urlencode($s['label']) ?>" class="btn btn-light w-100 mb-1 text-start">
                                <i class="bi <?= $s['icon'] ?> me-2 text-primary"></i><?= e($s['label']) ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Share Report -->
                    <div class="card border-0 bg-light mb-4">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-2"><i class="bi bi-share me-2"></i>Share This Report</h6>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="reportUrl" value="<?= url('report/' . $report['report_token']) ?>" readonly>
                                <button class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('reportUrl').value);this.textContent='Copied!'">Copy</button>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3">Quick Stats</h6>
                            <div class="d-flex justify-content-between mb-2 small">
                                <span class="text-muted">Critical Issues</span>
                                <span class="fw-bold text-danger"><?= count($criticals) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 small">
                                <span class="text-muted">High Priority</span>
                                <span class="fw-bold text-warning"><?= count($highs) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 small">
                                <span class="text-muted">Total Findings</span>
                                <span class="fw-bold"><?= count($allIssues) ?></span>
                            </div>
                            <?php if (!empty($loadMetricIssue['detected_value'])): ?>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Response Time</span>
                                <span class="fw-bold"><?= e($loadMetricIssue['detected_value']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($pageWeightIssue['detected_value'])): ?>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Page Weight</span>
                                <span class="fw-bold"><?= e($pageWeightIssue['detected_value']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($scriptIssue['detected_value'])): ?>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Script Load</span>
                                <span class="fw-bold"><?= e($scriptIssue['detected_value']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($lighthouseMetrics['Performance'])): ?>
                            <div class="d-flex justify-content-between small mt-2 pt-2 border-top">
                                <span class="text-muted">Lighthouse</span>
                                <span class="fw-bold"><?= e($lighthouseMetrics['Performance']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($comparison)): ?>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Vs Previous Scan</span>
                                <span class="fw-bold <?= ($comparison['score_delta'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= ($comparison['score_delta'] ?? 0) >= 0 ? '+' : '' ?><?= (int) ($comparison['score_delta'] ?? 0) ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var findingsTabTrigger = document.getElementById('findings-tab');
    if (!findingsTabTrigger || typeof bootstrap === 'undefined') {
        return;
    }

    var activateFindingsTab = function () {
        bootstrap.Tab.getOrCreateInstance(findingsTabTrigger).show();
    };

    document.querySelectorAll('.report-findings-link').forEach(function (link) {
        link.addEventListener('click', function () {
            activateFindingsTab();
        });
    });

    if (window.location.hash === '#issues-section') {
        activateFindingsTab();
    }
});
</script>

<!-- Request Help Form -->
<section id="request-help" class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <?php if ($pageFlashSuccess && str_contains((string)$pageFlashSuccess, 'in touch')): ?>
                <div class="alert alert-success d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div><?= e($pageFlashSuccess) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($pageFlashError && str_contains((string) $pageFlashError, 'name and email')): ?>
                <div class="alert alert-danger"><?= e($pageFlashError) ?></div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-circle mb-3" style="width:56px;height:56px">
                                <i class="bi bi-wrench-adjustable-circle-fill text-primary fs-3"></i>
                            </div>
                            <h3 class="fw-bold mb-1">Request Help Fixing This</h3>
                            <p class="text-muted">Tell us what you need and we'll get back to you with a custom plan.</p>
                        </div>
                        <form method="POST" action="<?= url('report/' . $report['report_token'] . '/help') ?>">
                            <?= csrf_field() ?>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold">Your Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="Jane Smith"
                                           value="<?= e($lead['contact_name'] ?? old('name', '')) ?>" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" placeholder="jane@example.com"
                                           value="<?= e($lead['email'] ?? old('email', '')) ?>" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold">Phone (optional)</label>
                                    <input type="tel" name="phone" class="form-control" placeholder="+1 (555) 000-0000"
                                           value="<?= e($lead['phone'] ?? old('phone', '')) ?>">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold">Business Name (optional)</label>
                                    <input type="text" name="company" class="form-control" placeholder="Acme Inc."
                                           value="<?= e($lead['business_name'] ?? old('company', '')) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Service Needed</label>
                                    <select name="service_type" class="form-select">
                                        <option value="">- Select a service -</option>
                                        <option value="Website Fixes">Fix Issues in This Report</option>
                                        <option value="SEO Cleanup">SEO Cleanup &amp; Improvements</option>
                                        <option value="Accessibility Improvements">Accessibility Review &amp; Fixes</option>
                                        <option value="Conversion Optimization">More Leads &amp; Conversions</option>
                                        <option value="Website Refresh">Website Refresh / Redesign</option>
                                        <option value="Speed Optimization">Speed Optimization</option>
                                        <option value="Ongoing Website Support">Monthly Website Support</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Message (optional)</label>
                                    <textarea name="message" class="form-control" rows="3"
                                              placeholder="Describe what you'd like help with..."><?= e(old('message', '')) ?></textarea>
                                </div>
                                <input type="hidden" name="website_url" value="<?= e($siteUrl) ?>">
                                <input type="hidden" name="report_token" value="<?= e($report['report_token']) ?>">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="bi bi-send me-2"></i>Request Help
                                    </button>
                                    <p class="text-muted small mt-2 mb-0">We'll respond within 1 business day. No obligation.</p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
window.SiteScopeDownloadReportPdf = async function(button) {
    if (typeof window.html2pdf === 'undefined') {
        return;
    }

    var exportRoot = document.getElementById('report-export-content');
    if (!exportRoot) {
        return;
    }

    var trigger = button || document.querySelector('[data-pdf-button]');
    var originalHtml = trigger ? trigger.innerHTML : '';
    if (trigger) {
        trigger.disabled = true;
        trigger.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Preparing PDF...';
    }

    var hostLabel = '';
    try {
        hostLabel = new URL('<?= e($siteUrl) ?>').hostname.replace(/^www\./i, '');
    } catch (error) {
        hostLabel = 'website-audit';
    }

    var filename = 'sitescope-report-' + hostLabel.replace(/[^a-z0-9.-]+/gi, '-').toLowerCase() + '.pdf';

    try {
        await window.html2pdf().set({
            margin: [8, 8, 10, 8],
            filename: filename,
            image: { type: 'jpeg', quality: 0.96 },
            html2canvas: {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff',
                scrollY: 0,
                ignoreElements: function(element) {
                    return element.classList && (element.classList.contains('no-print') || element.classList.contains('no-pdf'));
                }
            },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
            pagebreak: { mode: ['css', 'legacy'] }
        }).from(exportRoot).save();
    } finally {
        if (trigger) {
            trigger.disabled = false;
            trigger.innerHTML = originalHtml;
        }
    }
};
</script>

