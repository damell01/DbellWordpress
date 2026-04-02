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
$issueDisplayTitle = function (array $issue): string {
    $code = (string) ($issue['code'] ?? '');
    return match ($code) {
        'NO_CTA' => 'Visitors Are Not Being Told What To Do Next',
        'NO_CONTACT_FORM' => 'Visitors Do Not Have An Easy Way To Reach Out',
        'NO_PHONE' => 'Visitors Cannot Quickly Call The Business',
        'NO_TESTIMONIALS' => 'The Site Is Missing Social Proof',
        'NO_TRUST_BADGES' => 'The Site Is Missing Trust Signals',
        'MISSING_TITLE' => 'Search Engines Are Missing A Clear Page Title',
        'WEAK_TITLE' => 'The Page Title Is Too Weak To Compete Well',
        'TITLE_TOO_LONG' => 'The Page Title Is Likely Too Long For Search Results',
        'MISSING_META_DESC' => 'Search Results Are Missing A Strong Description',
        'WEAK_META_DESC' => 'The Search Description Is Too Thin',
        'META_DESC_TOO_LONG' => 'The Search Description Is Too Long',
        'MISSING_H1' => 'The Page Lacks A Clear Main Heading',
        'MULTIPLE_H1' => 'The Page Has Too Many Main Headings',
        'MISSING_CANONICAL' => 'Search Engines Are Missing A Preferred Page URL',
        'INVALID_CANONICAL' => 'The Preferred Page URL Looks Broken',
        'CANONICAL_OTHER_DOMAIN' => 'The Preferred Page URL Points To Another Domain',
        'NOINDEX_TAG_FOUND' => 'This Page May Be Telling Google Not To Index It',
        'ROBOTS_BLOCKS_SITE' => 'The Site May Be Blocking Search Engines',
        'HOMEPAGE_SEARCH_INTENT_WEAK' => 'The Homepage Does Not Clearly Explain What The Business Does',
        'HOMEPAGE_LOCATION_SIGNAL_WEAK' => 'The Homepage Does Not Clearly Show Where The Business Serves',
        'LIMITED_CITY_SERVICE_COVERAGE' => 'Service Area Coverage Looks Thin',
        'NO_LOCAL_LANDING_PAGE_SIGNAL' => 'The Site Lacks Strong Local Landing Pages',
        'CTA_NOT_PROMINENT' => 'The Main Call To Action Is Not Prominent Enough',
        'WEAK_HERO_MESSAGE' => 'The Hero Section Does Not Clearly Explain The Offer',
        'OFFER_CLARITY_WEAK' => 'The Offer Is Not Clear Enough',
        'CTA_PLACEMENT_THIN' => 'Call-To-Action Placement Looks Too Sparse',
        'TRUST_SIGNAL_DENSITY_LOW' => 'Trust Signals Look Too Thin',
        'HIGH_FORM_FRICTION' => 'The Contact Form May Be Asking Too Much',
        'NAP_CONSISTENCY_HINT' => 'Business Contact Details May Not Be Consistent Everywhere',
        'LOCAL_CONTACT_PROMINENCE_WEAK' => 'Local Contact Details Are Not Prominent Enough',
        'CITY_SERVICE_PAGE_COUNT_LOW' => 'There Are Not Many Strong City + Service Landing Pages',
        'LOCAL_REVIEW_SIGNAL_MISSING' => 'Local Review Signals Look Thin',
        'LOCALBUSINESS_SCHEMA_PRESENT' => 'Local Business Structured Data Was Found',
        'GBP_LOOKUP_CACHED' => 'Google Business Profile Lookup Used Cached Data',
        'PAGESPEED_LOOKUP_PENDING' => 'Performance Lab Data Is Still Pending',
        'PAGESPEED_LOOKUP_FAILED' => 'Performance Lab Data Could Not Be Retrieved',
        default => (string) ($issue['title'] ?? 'Issue'),
    };
};
$issueDisplayExplanation = function (array $issue): string {
    $code = (string) ($issue['code'] ?? '');
    return match ($code) {
        'NO_CTA' => 'The site does not clearly guide visitors toward calling, booking, requesting a quote, or contacting you.',
        'NO_CONTACT_FORM' => 'The site does not appear to offer a simple form for visitors to reach out and become a lead.',
        'NO_PHONE' => 'People may not be able to quickly call the business when they are ready to take action.',
        'NO_TESTIMONIALS' => 'Visitors are not seeing much proof that other customers trust this business.',
        'NO_TRUST_BADGES' => 'The site is missing visible trust signals that help reduce hesitation before someone contacts you.',
        'MISSING_TITLE' => 'Search engines may not get a strong headline for this page in search results.',
        'WEAK_TITLE' => 'The current page title may be too weak or too short to compete well in search.',
        'TITLE_TOO_LONG' => 'The page title may be too long and could get cut off in search results.',
        'MISSING_META_DESC' => 'Search engines may have to guess what description to show under this page in results.',
        'WEAK_META_DESC' => 'The current search description may not be strong enough to earn clicks.',
        'META_DESC_TOO_LONG' => 'The search description may be getting cut off before people see the full message.',
        'MISSING_H1' => 'The page is missing a clear main heading that helps both visitors and search engines understand it.',
        'MULTIPLE_H1' => 'The page may be sending mixed signals about its main topic because it uses too many top-level headings.',
        'MISSING_CANONICAL' => 'Search engines may not be getting a clear signal about which page URL should be treated as the preferred version.',
        'INVALID_CANONICAL' => 'The preferred page URL signal appears broken or incomplete.',
        'CANONICAL_OTHER_DOMAIN' => 'The page appears to be telling search engines that another domain should get the ranking credit. If that domain is your real primary live site, this may be intentional. If not, it can send ranking signals away from this site.',
        'NOINDEX_TAG_FOUND' => 'This page may be telling Google not to include it in search results at all.',
        'ROBOTS_BLOCKS_SITE' => 'The site may be blocking search engines from crawling important pages.',
        'HOMEPAGE_SEARCH_INTENT_WEAK' => 'The homepage may not clearly explain what the business does, which can hurt both rankings and conversions.',
        'HOMEPAGE_LOCATION_SIGNAL_WEAK' => 'The homepage may not clearly show where the business serves, which can weaken local SEO.',
        'LIMITED_CITY_SERVICE_COVERAGE' => 'The site does not yet show strong coverage of service-and-location combinations that often help local rankings.',
        'NO_LOCAL_LANDING_PAGE_SIGNAL' => 'The site appears light on dedicated local landing pages that help capture nearby searches.',
        'CTA_NOT_PROMINENT' => 'Visitors may not be seeing the main next step quickly enough near the top of the page.',
        'WEAK_HERO_MESSAGE' => 'The top section may not quickly explain what the business does or why someone should care.',
        'OFFER_CLARITY_WEAK' => 'The site may not be making the offer or customer benefit obvious enough.',
        'CTA_PLACEMENT_THIN' => 'Calls to action may be too limited across the main selling sections of the site.',
        'TRUST_SIGNAL_DENSITY_LOW' => 'The site looks light on proof points that help visitors feel safe contacting the business.',
        'HIGH_FORM_FRICTION' => 'The main form may ask for more information than a first contact really needs.',
        'NAP_CONSISTENCY_HINT' => 'The site may be showing more than one version of the business phone, email, or address details.',
        'LOCAL_CONTACT_PROMINENCE_WEAK' => 'Important local contact details may not be obvious enough on the pages people check first.',
        'CITY_SERVICE_PAGE_COUNT_LOW' => 'The site only shows a small number of pages that strongly connect services to target cities or service areas.',
        'LOCAL_REVIEW_SIGNAL_MISSING' => 'The site does not show much visible evidence of customer reviews or local proof.',
        'LOCALBUSINESS_SCHEMA_PRESENT' => 'The site is already using local business structured data, which is a good local SEO signal.',
        'GBP_LOOKUP_CACHED' => 'This scan reused a recent Google Business Profile lookup to keep the audit fast.',
        'PAGESPEED_LOOKUP_PENDING' => 'External PageSpeed data was not ready in time for this scan, so the report kept moving instead of waiting.',
        'PAGESPEED_LOOKUP_FAILED' => 'The scan could not retrieve Google PageSpeed lab data on this run.',
        default => trim((string) ($issue['explanation'] ?? '')),
    };
};
$issueDisplayWhy = function (array $issue): string {
    $code = (string) ($issue['code'] ?? '');
    return match ($code) {
        'NO_CTA' => 'If people are not clearly shown the next step, many of them leave without contacting the business.',
        'NO_CONTACT_FORM' => 'A simple form often captures leads who are not ready to call right away.',
        'NO_PHONE' => 'Many service-business visitors prefer to call as soon as they are ready, so hiding that option can cost leads.',
        'NO_TESTIMONIALS' => 'People trust businesses faster when they can see proof from past customers.',
        'NO_TRUST_BADGES' => 'Trust signals help reduce hesitation and make the business feel more established.',
        'MISSING_TITLE' => 'The page title is one of the strongest signals search engines use to understand and rank a page.',
        'WEAK_TITLE', 'TITLE_TOO_LONG' => 'A stronger title can improve both rankings and click-through rate from search results.',
        'MISSING_META_DESC', 'WEAK_META_DESC', 'META_DESC_TOO_LONG' => 'The search description helps influence whether people click your result.',
        'MISSING_H1', 'MULTIPLE_H1' => 'A clear heading structure helps both visitors and search engines understand the page quickly.',
        'MISSING_CANONICAL', 'INVALID_CANONICAL', 'CANONICAL_OTHER_DOMAIN' => 'Canonical setup helps search engines understand which version of a page should get credit.',
        'NOINDEX_TAG_FOUND', 'ROBOTS_BLOCKS_SITE' => 'If Google is blocked from indexing or crawling pages, rankings can drop fast no matter how strong the content is.',
        'HOMEPAGE_SEARCH_INTENT_WEAK', 'HOMEPAGE_LOCATION_SIGNAL_WEAK' => 'The homepage often does the heaviest lifting for both local SEO and first impressions.',
        'LIMITED_CITY_SERVICE_COVERAGE', 'NO_LOCAL_LANDING_PAGE_SIGNAL' => 'Local search visibility usually improves when the site clearly connects services to locations.',
        'CTA_NOT_PROMINENT', 'WEAK_HERO_MESSAGE', 'OFFER_CLARITY_WEAK', 'CTA_PLACEMENT_THIN', 'TRUST_SIGNAL_DENSITY_LOW', 'HIGH_FORM_FRICTION' => 'These issues can quietly lower conversion rate even when traffic is already reaching the site.',
        'NAP_CONSISTENCY_HINT', 'LOCAL_CONTACT_PROMINENCE_WEAK', 'CITY_SERVICE_PAGE_COUNT_LOW', 'LOCAL_REVIEW_SIGNAL_MISSING' => 'Local buyers and search engines both rely on clear trust, location, and business-identity signals.',
        'LOCALBUSINESS_SCHEMA_PRESENT' => 'This is a helpful supporting signal for local SEO rather than a problem.',
        'PAGESPEED_LOOKUP_PENDING', 'PAGESPEED_LOOKUP_FAILED' => 'This does not automatically mean the site is slow, but it does mean lab performance data was not available for this scan.',
        default => trim((string) ($issue['why_it_matters'] ?? '')),
    };
};
$issueDisplayFix = function (array $issue): string {
    $code = (string) ($issue['code'] ?? '');
    return match ($code) {
        'NO_CTA' => 'Add one or two clear actions in key areas like the hero, service sections, and contact area, such as Get a Quote, Call Now, or Book a Consultation.',
        'NO_CONTACT_FORM' => 'Add a simple contact form on the contact page and make sure it is easy to find from the homepage.',
        'NO_PHONE' => 'Place the main phone number in the header, contact section, and footer so it is easy to spot on desktop and mobile.',
        'NO_TESTIMONIALS' => 'Add real customer reviews, testimonials, or proof of results in visible sections of the site.',
        'NO_TRUST_BADGES' => 'Show trust signals like years in business, certifications, guarantees, memberships, or partner badges.',
        'MISSING_TITLE' => 'Add a clear page title that says what the page is about and includes the main service or keyword.',
        'WEAK_TITLE' => 'Rewrite the title so it is clearer, more specific, and more competitive in search.',
        'TITLE_TOO_LONG' => 'Shorten the title so the most important wording shows fully in search results.',
        'MISSING_META_DESC' => 'Write a short description that explains the value of the page and gives people a reason to click.',
        'WEAK_META_DESC' => 'Strengthen the description with clearer value and more intent-focused wording.',
        'META_DESC_TOO_LONG' => 'Trim the description so the key message appears before search engines cut it off.',
        'MISSING_H1' => 'Add one clear main heading near the top of the page.',
        'MULTIPLE_H1' => 'Keep one main heading, then use subheadings underneath it to organize the rest of the page.',
        'MISSING_CANONICAL' => 'Add a preferred page URL so search engines know which version of the page should rank.',
        'INVALID_CANONICAL' => 'Replace the broken canonical with a full valid page URL.',
        'CANONICAL_OTHER_DOMAIN' => 'If the off-site domain is your real primary domain, you may be fine. If not, update the canonical so it points to the correct version of the page on this site.',
        'NOINDEX_TAG_FOUND' => 'Remove the noindex instruction on pages you want to appear in Google.',
        'ROBOTS_BLOCKS_SITE' => 'Review robots.txt and remove any sitewide blocking rules that are stopping search engines from crawling important pages.',
        'HOMEPAGE_SEARCH_INTENT_WEAK' => 'Make the homepage more direct about what the business does, who it serves, and what action a visitor should take next.',
        'HOMEPAGE_LOCATION_SIGNAL_WEAK' => 'Add stronger location wording to the homepage so both visitors and search engines can see where the business serves.',
        'LIMITED_CITY_SERVICE_COVERAGE' => 'Build out stronger service-area coverage by pairing services with the cities or areas you want to rank in.',
        'NO_LOCAL_LANDING_PAGE_SIGNAL' => 'Create stronger local landing pages for the main services and service areas you want to compete in.',
        'CTA_NOT_PROMINENT' => 'Move a stronger CTA higher on the page and make it more visually obvious in the hero and header.',
        'WEAK_HERO_MESSAGE' => 'Rewrite the hero so it says what the business does, who it helps, and what someone should do next within a few seconds.',
        'OFFER_CLARITY_WEAK' => 'Clarify the offer with more direct wording around pricing, estimate, consultation, quote, or the core service promise.',
        'CTA_PLACEMENT_THIN' => 'Repeat the main CTA in more than one strategic section so visitors do not need to hunt for the next step.',
        'TRUST_SIGNAL_DENSITY_LOW' => 'Add testimonials, ratings, guarantees, certifications, years in business, and other proof near key decision points.',
        'HIGH_FORM_FRICTION' => 'Trim the form down to the essentials and keep longer qualification questions for follow-up.',
        'NAP_CONSISTENCY_HINT' => 'Standardize the main phone, email, address, and business name everywhere they appear on the site.',
        'LOCAL_CONTACT_PROMINENCE_WEAK' => 'Make sure the homepage and contact page both show the business phone, location, and core contact details clearly.',
        'CITY_SERVICE_PAGE_COUNT_LOW' => 'Create more useful pages that pair your key services with the cities or areas you actually want to rank in.',
        'LOCAL_REVIEW_SIGNAL_MISSING' => 'Add visible review snippets, star-rating language, or links to your Google reviews on key pages.',
        'LOCALBUSINESS_SCHEMA_PRESENT' => 'Keep the schema updated and aligned with your visible business details.',
        'GBP_LOOKUP_CACHED' => 'No action is needed unless you recently changed your profile and want a fresh lookup.',
        'PAGESPEED_LOOKUP_PENDING', 'PAGESPEED_LOOKUP_FAILED' => 'Re-run the scan later for lab speed data, but still use the current report findings to clean up on-page speed issues.',
        default => trim((string) ($issue['how_to_fix'] ?? '')),
    };
};
$issueDisplayImpact = function (array $issue): string {
    $code = (string) ($issue['code'] ?? '');
    return match ($code) {
        'NO_CTA' => 'This can lower form submissions, calls, and quote requests.',
        'NO_CONTACT_FORM' => 'This can reduce lead capture from visitors who prefer a quick message over a phone call.',
        'NO_PHONE' => 'This can cost high-intent leads who are ready to call right now.',
        'NO_TESTIMONIALS', 'NO_TRUST_BADGES' => 'This can make the business feel less proven and lower trust with new visitors.',
        'MISSING_TITLE', 'WEAK_TITLE', 'TITLE_TOO_LONG', 'MISSING_META_DESC', 'WEAK_META_DESC', 'META_DESC_TOO_LONG', 'MISSING_H1', 'MULTIPLE_H1' => 'This can weaken rankings and reduce clicks from search results.',
        'MISSING_CANONICAL', 'INVALID_CANONICAL', 'CANONICAL_OTHER_DOMAIN', 'NOINDEX_TAG_FOUND', 'ROBOTS_BLOCKS_SITE' => 'This can directly hurt organic visibility if search engines crawl or index the wrong pages.',
        'HOMEPAGE_SEARCH_INTENT_WEAK', 'HOMEPAGE_LOCATION_SIGNAL_WEAK', 'LIMITED_CITY_SERVICE_COVERAGE', 'NO_LOCAL_LANDING_PAGE_SIGNAL' => 'This can make it harder to compete for the searches that matter most locally.',
        'CTA_NOT_PROMINENT', 'WEAK_HERO_MESSAGE', 'OFFER_CLARITY_WEAK', 'CTA_PLACEMENT_THIN', 'TRUST_SIGNAL_DENSITY_LOW', 'HIGH_FORM_FRICTION' => 'This can lower the percentage of visitors who turn into actual leads.',
        'NAP_CONSISTENCY_HINT', 'LOCAL_CONTACT_PROMINENCE_WEAK', 'CITY_SERVICE_PAGE_COUNT_LOW', 'LOCAL_REVIEW_SIGNAL_MISSING' => 'This can weaken local trust, map relevance, or the ability to rank in nearby searches.',
        'PAGESPEED_LOOKUP_PENDING', 'PAGESPEED_LOOKUP_FAILED' => 'This mainly limits how much lab performance detail the report can show right now.',
        default => trim((string) ($issue['business_impact'] ?? '')),
    };
};
$issueImpactChips = function (array $issue): array {
    $code = (string) ($issue['code'] ?? '');
    $category = (string) ($issue['category'] ?? '');
    $chips = [];

    $addChip = static function (array &$list, string $label, string $icon, string $tone): void {
        foreach ($list as $existing) {
            if (($existing['label'] ?? '') === $label) {
                return;
            }
        }
        $list[] = ['label' => $label, 'icon' => $icon, 'tone' => $tone];
    };

    if ($category === 'seo') {
        $addChip($chips, 'Rankings', 'bi-search', 'primary');
    }
    if ($category === 'conversion') {
        $addChip($chips, 'Leads', 'bi-bullseye', 'warning');
    }
    if ($category === 'technical') {
        $addChip($chips, 'Performance', 'bi-speedometer2', 'info');
    }
    if ($category === 'local') {
        $addChip($chips, 'Local SEO', 'bi-geo-alt', 'success');
    }

    if (in_array($code, ['NO_CTA', 'NO_CONTACT_FORM', 'NO_PHONE'], true)) {
        $addChip($chips, 'Urgent Lead Flow', 'bi-telephone-outbound', 'danger');
    }
    if (in_array($code, ['NO_TESTIMONIALS', 'NO_TRUST_BADGES', 'MISSING_HTTPS'], true)) {
        $addChip($chips, 'Trust', 'bi-shield-check', 'secondary');
    }
    if (in_array($code, ['NOINDEX_TAG_FOUND', 'ROBOTS_BLOCKS_SITE', 'CANONICAL_OTHER_DOMAIN', 'MISSING_TITLE', 'MISSING_META_DESC', 'MISSING_H1'], true)) {
        $addChip($chips, 'SEO Visibility', 'bi-graph-up-arrow', 'primary');
    }
    if (in_array($code, ['SLOW_RESPONSE', 'MODERATE_RESPONSE', 'LARGE_PAGE_SIZE', 'LIGHTHOUSE_PERFORMANCE_LOW', 'PAGESPEED_LOOKUP_PENDING', 'PAGESPEED_LOOKUP_FAILED'], true)) {
        $addChip($chips, 'Speed', 'bi-lightning-charge', 'info');
    }
    if (in_array($code, ['CTA_NOT_PROMINENT', 'WEAK_HERO_MESSAGE', 'OFFER_CLARITY_WEAK', 'CTA_PLACEMENT_THIN', 'HIGH_FORM_FRICTION'], true)) {
        $addChip($chips, 'Conversions', 'bi-graph-up-arrow', 'warning');
    }
    if (in_array($code, ['TRUST_SIGNAL_DENSITY_LOW', 'LOCAL_REVIEW_SIGNAL_MISSING', 'NAP_CONSISTENCY_HINT'], true)) {
        $addChip($chips, 'Trust', 'bi-shield-check', 'secondary');
    }

    return array_slice($chips, 0, 3);
};
$issueMetaSummary = function (array $issue): string {
    $parts = [];
    $category = trim((string) ($issue['category'] ?? ''));
    $severity = trim((string) ($issue['severity'] ?? ''));

    if ($category !== '') {
        $parts[] = ucfirst($category);
    }
    if ($severity !== '') {
        $parts[] = ucfirst($severity) . ' priority';
    }

    return implode(' | ', $parts);
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
$competitorAnalysis = $competitorAnalysis ?? ['success' => false, 'competitors' => [], 'queries' => []];
$feedbackSummary = $feedbackSummary ?? [];
$pageSpeedData = $pageSpeedData ?? ['mobile' => null, 'desktop' => null];
$pageFlashSuccess = \App\Core\Session::getFlash('success');
$pageFlashError = \App\Core\Session::getFlash('error');
$criticals     = array_filter($allIssues, fn($i) => $i['severity'] === 'critical');
$highs         = array_filter($allIssues, fn($i) => $i['severity'] === 'high');
$severityOrder = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3, 'info' => 4];
$categoryOrder = ['conversion' => 0, 'seo' => 1, 'local' => 2, 'technical' => 3, 'accessibility' => 9];
$issueCodeBoostOrder = [
    'NO_CTA' => 0,
    'NO_CONTACT_FORM' => 1,
    'NO_PHONE' => 2,
    'MISSING_TITLE' => 3,
    'MISSING_H1' => 4,
    'MISSING_META_DESC' => 5,
    'NOINDEX_TAG_FOUND' => 6,
    'ROBOTS_BLOCKS_SITE' => 7,
    'CANONICAL_OTHER_DOMAIN' => 8,
    'HOMEPAGE_SEARCH_INTENT_WEAK' => 9,
    'HOMEPAGE_LOCATION_SIGNAL_WEAK' => 10,
    'LIMITED_CITY_SERVICE_COVERAGE' => 11,
];
$sortIssuesByPriority = static function (array $issues) use ($severityOrder, $categoryOrder, $issueCodeBoostOrder): array {
    $sorted = array_values($issues);
    usort($sorted, static function (array $left, array $right) use ($severityOrder, $categoryOrder, $issueCodeBoostOrder): int {
        $leftRank = $severityOrder[$left['severity'] ?? 'info'] ?? 99;
        $rightRank = $severityOrder[$right['severity'] ?? 'info'] ?? 99;
        if ($leftRank !== $rightRank) {
            return $leftRank <=> $rightRank;
        }

        $leftBoost = $issueCodeBoostOrder[$left['code'] ?? ''] ?? 99;
        $rightBoost = $issueCodeBoostOrder[$right['code'] ?? ''] ?? 99;
        if ($leftBoost !== $rightBoost) {
            return $leftBoost <=> $rightBoost;
        }

        $leftCategoryRank = $categoryOrder[$left['category'] ?? ''] ?? 4;
        $rightCategoryRank = $categoryOrder[$right['category'] ?? ''] ?? 4;
        if ($leftCategoryRank !== $rightCategoryRank) {
            return $leftCategoryRank <=> $rightCategoryRank;
        }

        return strcmp((string) ($left['title'] ?? ''), (string) ($right['title'] ?? ''));
    });

    return $sorted;
};
$sortedIssues = $allIssues;
$sortedIssues = $sortIssuesByPriority($sortedIssues);
$priorityIssues = array_slice(array_values(array_filter(
    $sortedIssues,
    fn($i) => in_array($i['severity'] ?? 'info', ['critical', 'high', 'medium'], true)
        && !(($i['category'] ?? '') === 'accessibility' && ($i['severity'] ?? '') !== 'critical')
        && !in_array(($i['code'] ?? ''), ['PAGESPEED_LOOKUP_PENDING', 'GBP_LOOKUP_CACHED'], true)
)), 0, 3);
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
$issueEvidenceSummary = function (array $issue) use ($issueDisplayExplanation): string {
    $detected = trim((string) ($issue['detected_value'] ?? ''));
    if ($detected !== '') {
        $parts = preg_split('/\s*\|\s*/', $detected) ?: [$detected];
        $first = trim((string) ($parts[0] ?? ''));
        if ($first !== '') {
            return function_exists('mb_strimwidth') ? mb_strimwidth($first, 0, 110, '...', 'UTF-8') : substr($first, 0, 110);
        }
    }

    $fallback = trim($issueDisplayExplanation($issue));
    return function_exists('mb_strimwidth') ? mb_strimwidth($fallback, 0, 110, '...', 'UTF-8') : substr($fallback, 0, 110);
};
$describeIssueEvidence = function (array $issue) use ($extractFirstUrl): string {
    $code = (string) ($issue['code'] ?? '');
    $detected = trim((string) ($issue['detected_value'] ?? ''));

    return match ($code) {
        'CANONICAL_OTHER_DOMAIN' => $detected !== ''
            ? 'Canonical found: ' . $detected . '. If this is your main live domain, this may be intentional. If not, it can point ranking credit away from the scanned site.'
            : 'A canonical tag appears to point off-site.',
        'MULTIPLE_H1' => $detected !== ''
            ? 'The scan counted ' . $detected . ' H1 headings on the page. Usually 1 main H1 is the cleaner setup.'
            : 'The page appears to have multiple H1 headings.',
        'MISSING_SITEMAP' => 'The scanner checked the standard sitemap location (`/sitemap.xml`) and did not find a working sitemap there.',
        'MISSING_ROBOTS' => 'The scanner checked the standard robots.txt location (`/robots.txt`) and did not find a file there.',
        'NO_GBP_LINK', 'GBP_FOUND_EXTERNALLY', 'GBP_LINK_PRESENT' => $detected !== ''
            ? 'GBP evidence: ' . $detected
            : 'No clear Google Business Profile URL was found in the scan evidence.',
        'NAP_CONSISTENCY_HINT' => $detected !== ''
            ? 'Details found: ' . $detected
            : 'The scan found more than one business contact pattern.',
        default => ($extractFirstUrl($detected) ? 'URL found: ' . $extractFirstUrl($detected) : $detected),
    };
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
$localSeoCodes = [
    'NO_ADDRESS','NO_MAP','NO_HOURS','NO_SCHEMA','NO_GBP_LINK','GBP_FOUND_EXTERNALLY','GBP_LINK_PRESENT',
    'NAP_CONSISTENCY_HINT','LOCAL_CONTACT_PROMINENCE_WEAK','CITY_SERVICE_PAGE_COUNT_LOW',
    'LOCAL_REVIEW_SIGNAL_MISSING','LOCALBUSINESS_SCHEMA_PRESENT','GBP_LOOKUP_CACHED'
];
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

$formatSeoEvidence = function (array $issue, array $check) use ($issueDisplayTitle): array {
    $code = (string) ($issue['code'] ?? '');
    $detected = trim((string) ($issue['detected_value'] ?? ''));
    $explanation = trim((string) ($issue['explanation'] ?? ''));
    $friendlyTitle = $issueDisplayTitle($issue);

    return match ($code) {
        'WEAK_TITLE', 'TITLE_TOO_LONG' => [
            'found' => $friendlyTitle,
            'detail' => $detected !== ''
                ? 'Current title: "' . $detected . '" (' . mb_strlen($detected) . ' characters).'
                : $explanation,
        ],
        'TITLE_MATCHES_H1_EXACTLY' => [
            'found' => $friendlyTitle,
            'detail' => $detected !== ''
                ? 'Matching title/H1 text: "' . $detected . '".'
                : $explanation,
        ],
        'WEAK_META_DESC', 'META_DESC_TOO_LONG' => [
            'found' => $friendlyTitle,
            'detail' => $detected !== ''
                ? 'Current description: "' . $detected . '" (' . mb_strlen($detected) . ' characters).'
                : $explanation,
        ],
        'MULTIPLE_H1' => [
            'found' => $friendlyTitle,
            'detail' => $detected !== ''
                ? 'H1 count found: ' . $detected . '. Best practice is 1.'
                : $explanation,
        ],
        'INVALID_CANONICAL', 'CANONICAL_OTHER_DOMAIN' => [
            'found' => $friendlyTitle,
            'detail' => $detected !== ''
                ? 'Canonical URL found: ' . $detected
                : $explanation,
        ],
        'NOINDEX_TAG_FOUND', 'ROBOTS_BLOCKS_SITE' => [
            'found' => $friendlyTitle,
            'detail' => $detected !== '' ? 'Blocking signal found: ' . $detected : $explanation,
        ],
        'LIGHTHOUSE_SEO_LOW' => [
            'found' => $friendlyTitle,
            'detail' => $detected !== '' ? 'Lighthouse SEO data: ' . $detected : $explanation,
        ],
        'LIMITED_CITY_SERVICE_COVERAGE', 'NO_LOCAL_LANDING_PAGE_SIGNAL', 'WEAK_TOPIC_RELEVANCE',
        'HOMEPAGE_SEARCH_INTENT_WEAK', 'HOMEPAGE_LOCATION_SIGNAL_WEAK',
        'KEY_PAGE_THIN_CONTENT', 'KEY_PAGE_LOW_INTERNAL_LINKS',
        'KEY_PAGE_MISSING_TITLE', 'KEY_PAGE_MISSING_META_DESC', 'KEY_PAGE_MISSING_H1' => [
            'found' => $friendlyTitle,
            'detail' => $detected !== '' ? 'Scan evidence: ' . $detected : $explanation,
        ],
        default => [
            'found' => $friendlyTitle,
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
    if (in_array(($issueItem['category'] ?? ''), ['technical', 'conversion', 'local'], true)
        && !in_array(($issueItem['code'] ?? ''), ['PAGESPEED_LOOKUP_PENDING', 'GBP_LOOKUP_CACHED'], true)) {
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
    ? 'Biggest SEO opportunity: ' . $issueDisplayTitle($topSeoIssue) . '.'
    : 'SEO basics look fairly stable, so the next gains likely come from deeper content and local coverage.';
$businessSummary = $topBusinessIssue
    ? 'Biggest business impact issue: ' . $issueDisplayTitle($topBusinessIssue) . '.'
    : 'No major non-SEO blocker stood out more than the rest in this scan.';
$classifyFixLane = function (array $issue): string {
    $code = (string) ($issue['code'] ?? '');
    $category = (string) ($issue['category'] ?? '');
    $severity = (string) ($issue['severity'] ?? 'info');

    if ($category === 'accessibility' && $severity !== 'critical') {
        return 'later';
    }
    if (in_array($code, ['PAGESPEED_LOOKUP_PENDING', 'GBP_LOOKUP_CACHED', 'LOCALBUSINESS_SCHEMA_PRESENT', 'GBP_LINK_PRESENT', 'GBP_FOUND_EXTERNALLY'], true)) {
        return 'later';
    }
    if (in_array($code, [
        'NO_CTA','NO_CONTACT_FORM','NO_PHONE','CTA_NOT_PROMINENT','WEAK_HERO_MESSAGE',
        'OFFER_CLARITY_WEAK','CTA_PLACEMENT_THIN','TRUST_SIGNAL_DENSITY_LOW',
        'HIGH_FORM_FRICTION','NO_TESTIMONIALS','NO_TRUST_BADGES',
        'LOCAL_CONTACT_PROMINENCE_WEAK','LOCAL_REVIEW_SIGNAL_MISSING'
    ], true)) {
        return 'now';
    }
    if (in_array($category, ['seo', 'local'], true) || in_array($code, ['LIGHTHOUSE_PERFORMANCE_LOW', 'SLOW_RESPONSE', 'MODERATE_RESPONSE', 'LARGE_PAGE_SIZE'], true)) {
        return 'next';
    }
    return 'later';
};
$laneBuckets = ['now' => [], 'next' => [], 'later' => []];
foreach ($sortedIssues as $issueItem) {
    $laneBuckets[$classifyFixLane($issueItem)][] = $issueItem;
}
$fixLaneGroups = [
    'now' => [
        'label' => 'Losing Leads',
        'tone' => 'danger',
        'copy' => 'These are the most urgent problems likely to cost calls, form submissions, or immediate trust.',
        'issues' => array_slice($laneBuckets['now'], 0, 3),
    ],
    'next' => [
        'label' => 'Hurting Rankings',
        'tone' => 'warning',
        'copy' => 'These issues can weaken search visibility and overall site performance once the urgent blockers are handled.',
        'issues' => array_slice($laneBuckets['next'], 0, 3),
    ],
    'later' => [
        'label' => 'Polish Opportunities',
        'tone' => 'primary',
        'copy' => 'These are lower-priority improvements that still help the site feel stronger, cleaner, and more complete over time.',
        'issues' => array_slice($laneBuckets['later'], 0, 3),
    ],
];
$pageSpeedPendingIssue = $firstMatchingIssue(['PAGESPEED_LOOKUP_PENDING', 'PAGESPEED_LOOKUP_FAILED']);
$structuredDataSnapshot = [
    ['label' => 'LocalBusiness', 'icon' => 'bi-shop', 'present' => isset($issuesByCode['LOCALBUSINESS_SCHEMA_PRESENT'])],
    ['label' => 'Organization', 'icon' => 'bi-building', 'present' => !empty($gbpIssue) || !isset($issuesByCode['NO_SCHEMA'])],
    ['label' => 'Service', 'icon' => 'bi-briefcase', 'present' => !isset($issuesByCode['NO_SCHEMA']) && !isset($issuesByCode['MISSING_STRUCTURED_DATA'])],
    ['label' => 'FAQ', 'icon' => 'bi-patch-question', 'present' => !isset($issuesByCode['MISSING_STRUCTURED_DATA'])],
    ['label' => 'Review', 'icon' => 'bi-star', 'present' => isset($issuesByCode['LOCAL_REVIEW_SIGNAL_MISSING']) === false && (isset($issuesByCode['LOCALBUSINESS_SCHEMA_PRESENT']) || isset($issuesByCode['GBP_LINK_PRESENT']) || isset($issuesByCode['GBP_FOUND_EXTERNALLY']))],
];
$schemaErrorIssue = $firstMatchingIssue(['MISSING_STRUCTURED_DATA', 'NO_SCHEMA']);
$localCheckpointCards = [
    [
        'label' => 'NAP Consistency',
        'icon' => 'bi-journal-check',
        'status' => isset($issuesByCode['NAP_CONSISTENCY_HINT']) ? 'Needs review' : 'Looks stable',
        'tone' => isset($issuesByCode['NAP_CONSISTENCY_HINT']) ? 'warning' : 'success',
        'detail' => isset($issuesByCode['NAP_CONSISTENCY_HINT'])
            ? $issueDisplayExplanation($issuesByCode['NAP_CONSISTENCY_HINT'])
            : 'The scan did not find strong signs of conflicting phone, email, or address details.',
        'evidence' => isset($issuesByCode['NAP_CONSISTENCY_HINT']) ? $describeIssueEvidence($issuesByCode['NAP_CONSISTENCY_HINT']) : '',
    ],
    [
        'label' => 'GBP Link Quality',
        'icon' => 'bi-geo-alt',
        'status' => isset($issuesByCode['GBP_LINK_PRESENT']) ? 'Linked on site' : (isset($issuesByCode['GBP_FOUND_EXTERNALLY']) ? 'Found, not linked' : 'Needs attention'),
        'tone' => isset($issuesByCode['GBP_LINK_PRESENT']) ? 'success' : (isset($issuesByCode['GBP_FOUND_EXTERNALLY']) ? 'warning' : 'danger'),
        'detail' => isset($issuesByCode['GBP_LINK_PRESENT'])
            ? $issueDisplayExplanation($issuesByCode['GBP_LINK_PRESENT'])
            : (isset($issuesByCode['GBP_FOUND_EXTERNALLY'])
                ? $issueDisplayExplanation($issuesByCode['GBP_FOUND_EXTERNALLY'])
                : ($issueDisplayExplanation($issuesByCode['NO_GBP_LINK'] ?? ['explanation' => 'No clear Google Business Profile connection was found on this scan.']))),
        'evidence' => isset($issuesByCode['GBP_LINK_PRESENT'])
            ? $describeIssueEvidence($issuesByCode['GBP_LINK_PRESENT'])
            : (isset($issuesByCode['GBP_FOUND_EXTERNALLY'])
                ? $describeIssueEvidence($issuesByCode['GBP_FOUND_EXTERNALLY'])
                : (isset($issuesByCode['NO_GBP_LINK']) ? $describeIssueEvidence($issuesByCode['NO_GBP_LINK']) : '')),
    ],
    [
        'label' => 'Local Landing Pages',
        'icon' => 'bi-map',
        'status' => isset($issuesByCode['CITY_SERVICE_PAGE_COUNT_LOW']) || isset($issuesByCode['NO_LOCAL_LANDING_PAGE_SIGNAL']) ? 'Coverage is thin' : 'Coverage found',
        'tone' => isset($issuesByCode['CITY_SERVICE_PAGE_COUNT_LOW']) || isset($issuesByCode['NO_LOCAL_LANDING_PAGE_SIGNAL']) ? 'warning' : 'success',
        'detail' => isset($issuesByCode['CITY_SERVICE_PAGE_COUNT_LOW'])
            ? $issueDisplayExplanation($issuesByCode['CITY_SERVICE_PAGE_COUNT_LOW'])
            : (isset($issuesByCode['NO_LOCAL_LANDING_PAGE_SIGNAL'])
                ? $issueDisplayExplanation($issuesByCode['NO_LOCAL_LANDING_PAGE_SIGNAL'])
                : 'The scan found enough signs that services and target areas are being paired on the site.'),
        'evidence' => isset($issuesByCode['CITY_SERVICE_PAGE_COUNT_LOW'])
            ? $describeIssueEvidence($issuesByCode['CITY_SERVICE_PAGE_COUNT_LOW'])
            : (isset($issuesByCode['NO_LOCAL_LANDING_PAGE_SIGNAL']) ? $describeIssueEvidence($issuesByCode['NO_LOCAL_LANDING_PAGE_SIGNAL']) : ''),
    ],
    [
        'label' => 'Map + Contact Prominence',
        'icon' => 'bi-telephone',
        'status' => isset($issuesByCode['LOCAL_CONTACT_PROMINENCE_WEAK']) ? 'Needs attention' : 'Visible enough',
        'tone' => isset($issuesByCode['LOCAL_CONTACT_PROMINENCE_WEAK']) ? 'warning' : 'success',
        'detail' => isset($issuesByCode['LOCAL_CONTACT_PROMINENCE_WEAK'])
            ? $issueDisplayExplanation($issuesByCode['LOCAL_CONTACT_PROMINENCE_WEAK'])
            : 'Contact and location signals appear visible enough on the pages people are most likely to check.',
        'evidence' => isset($issuesByCode['LOCAL_CONTACT_PROMINENCE_WEAK']) ? $describeIssueEvidence($issuesByCode['LOCAL_CONTACT_PROMINENCE_WEAK']) : '',
    ],
];
$competitorBenchmarks = [
    [
        'label' => 'Title Clarity',
        'icon' => 'bi-type',
        'you' => isset($issuesByCode['MISSING_TITLE']) || isset($issuesByCode['WEAK_TITLE']) || isset($issuesByCode['TITLE_TOO_LONG']) ? 'Needs work' : 'Solid',
        'competitor' => 'Usually solid',
        'tone' => isset($issuesByCode['MISSING_TITLE']) || isset($issuesByCode['WEAK_TITLE']) || isset($issuesByCode['TITLE_TOO_LONG']) ? 'warning' : 'success',
        'difference' => isset($issuesByCode['MISSING_TITLE']) || isset($issuesByCode['WEAK_TITLE']) || isset($issuesByCode['TITLE_TOO_LONG'])
            ? 'Your title setup looks weaker than what strong local competitors usually publish.'
            : 'Your title setup is in line with what strong local competitors usually have.',
    ],
    [
        'label' => 'CTA Presence',
        'icon' => 'bi-cursor',
        'you' => isset($issuesByCode['NO_CTA']) || isset($issuesByCode['CTA_NOT_PROMINENT']) || isset($issuesByCode['CTA_PLACEMENT_THIN']) ? 'Behind' : 'Competitive',
        'competitor' => 'Usually repeated',
        'tone' => isset($issuesByCode['NO_CTA']) || isset($issuesByCode['CTA_NOT_PROMINENT']) || isset($issuesByCode['CTA_PLACEMENT_THIN']) ? 'warning' : 'success',
        'difference' => isset($issuesByCode['NO_CTA']) || isset($issuesByCode['CTA_NOT_PROMINENT']) || isset($issuesByCode['CTA_PLACEMENT_THIN'])
            ? 'Stronger competitors usually repeat their main CTA in the hero, mid-page, and contact areas more clearly than this site.'
            : 'Your CTA setup looks close to what strong local competitors usually do.',
    ],
    [
        'label' => 'Speed Baseline',
        'icon' => 'bi-lightning',
        'you' => $pageSpeedPendingIssue ? 'Pending' : (($technical >= 75) ? 'Competitive' : 'Needs work'),
        'competitor' => 'Usually mixed',
        'tone' => $pageSpeedPendingIssue ? 'secondary' : (($technical >= 75) ? 'success' : 'warning'),
        'difference' => $pageSpeedPendingIssue
            ? 'This benchmark is incomplete right now because external PageSpeed lab data was still pending during the scan.'
            : (($technical >= 75)
                ? 'Your speed baseline is at least competitive with the average local business site.'
                : 'There is room to improve speed and technical cleanup compared with stronger local competitors.'),
    ],
    [
        'label' => 'Local Trust Signals',
        'icon' => 'bi-shield-check',
        'you' => isset($issuesByCode['LOCAL_REVIEW_SIGNAL_MISSING']) || isset($issuesByCode['NO_SCHEMA']) || isset($issuesByCode['NO_GBP_LINK']) ? 'Behind' : 'Competitive',
        'competitor' => 'Usually visible',
        'tone' => isset($issuesByCode['LOCAL_REVIEW_SIGNAL_MISSING']) || isset($issuesByCode['NO_SCHEMA']) || isset($issuesByCode['NO_GBP_LINK']) ? 'warning' : 'success',
        'difference' => isset($issuesByCode['LOCAL_REVIEW_SIGNAL_MISSING']) || isset($issuesByCode['NO_SCHEMA']) || isset($issuesByCode['NO_GBP_LINK'])
            ? 'Stronger local competitors usually surface reviews, GBP links, schema, and clearer business proof more visibly than this site.'
            : 'Your local trust setup looks competitive against a typical local business site.',
    ],
    [
        'label' => 'Service Area Coverage',
        'icon' => 'bi-signpost-split',
        'you' => isset($issuesByCode['LIMITED_CITY_SERVICE_COVERAGE']) || isset($issuesByCode['CITY_SERVICE_PAGE_COUNT_LOW']) || isset($issuesByCode['NO_LOCAL_LANDING_PAGE_SIGNAL']) ? 'Behind' : 'Competitive',
        'competitor' => 'Usually broad',
        'tone' => isset($issuesByCode['LIMITED_CITY_SERVICE_COVERAGE']) || isset($issuesByCode['CITY_SERVICE_PAGE_COUNT_LOW']) || isset($issuesByCode['NO_LOCAL_LANDING_PAGE_SIGNAL']) ? 'warning' : 'success',
        'difference' => isset($issuesByCode['LIMITED_CITY_SERVICE_COVERAGE']) || isset($issuesByCode['CITY_SERVICE_PAGE_COUNT_LOW']) || isset($issuesByCode['NO_LOCAL_LANDING_PAGE_SIGNAL'])
            ? 'Stronger competitors often create more service + city combinations and clearer local landing pages than this site currently shows.'
            : 'Your service-area coverage looks reasonably competitive based on the pages the scan found.',
    ],
];
$exportSummaryCards = [
    ['label' => 'Overall Score', 'value' => $overall . '/100'],
    ['label' => 'Top SEO Gap', 'value' => $topSeoIssue ? $issueDisplayTitle($topSeoIssue) : 'No major SEO blocker surfaced'],
    ['label' => 'Top Lead Gap', 'value' => $topBusinessIssue ? $issueDisplayTitle($topBusinessIssue) : 'No major lead blocker surfaced'],
];
$actualCompetitors = array_values(array_filter((array) ($competitorAnalysis['competitors'] ?? []), static fn($item) => is_array($item)));
?>

<div id="report-export-content">
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="report-export-brief">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <div class="summary-kicker">Client Snapshot</div>
                    <h2 class="fw-bold fs-4 mb-1">Short Version First</h2>
                    <p class="text-muted mb-0">A summary-first view that also makes the PDF export easier to scan.</p>
                </div>
                <span class="badge text-bg-light border">Prepared <?= e(date('M j, Y', strtotime($report['created_at'] ?? 'now'))) ?></span>
            </div>
            <div class="row g-3">
                <?php foreach ($exportSummaryCards as $summaryCard): ?>
                <div class="col-md-4">
                    <div class="report-export-card h-100">
                        <div class="small text-uppercase text-muted fw-semibold mb-2"><?= e($summaryCard['label']) ?></div>
                        <div class="fw-semibold"><?= e($summaryCard['value']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
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
                                <h3 class="summary-priority-title"><?= e($issueDisplayTitle($priorityIssue)) ?></h3>
                                <p class="summary-priority-copy"><?= e(mb_strimwidth($issueDisplayExplanation($priorityIssue), 0, 115, '...')) ?></p>
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
                    <?php $laneIssueUrl = $extractFirstUrl($laneIssue['detected_value'] ?? ''); ?>
                    <a href="#issue-<?= (int) ($laneIssue['id'] ?? 0) ?>" class="fix-lane-item report-findings-link text-decoration-none d-block">
                        <div class="small fw-semibold text-dark"><?= e($issueDisplayTitle($laneIssue)) ?></div>
                        <div class="small text-muted text-capitalize"><?= e($laneIssue['category'] ?? 'general') ?></div>
                        <div class="small text-muted mt-1"><?= e($issueEvidenceSummary($laneIssue)) ?></div>
                        <?php if (!empty($laneIssueUrl)): ?>
                        <div class="small mt-1">
                            <span class="text-primary">Link found:</span>
                            <span class="text-break"><?= e($laneIssueUrl) ?></span>
                        </div>
                        <?php endif; ?>
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

<section class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <div class="summary-kicker">Competitive Read</div>
                                <h2 class="fw-bold fs-5 mb-1">You Vs A Typical Local Competitor</h2>
                                <p class="text-muted small mb-0">A quick benchmark on the signals local service sites usually get right.</p>
                            </div>
                            <span class="badge text-bg-light border"><?= e((string) $competitiveSeoScore) ?>/100</span>
                        </div>
                            <div class="row g-3">
                                <?php foreach ($competitorBenchmarks as $benchmark): ?>
                                <div class="col-md-6 col-xl-4">
                                    <div class="benchmark-card benchmark-card--<?= e($benchmark['tone']) ?>">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi <?= e($benchmark['icon']) ?> text-primary"></i>
                                        <div class="fw-semibold small"><?= e($benchmark['label']) ?></div>
                                    </div>
                                    <div class="small text-muted">You</div>
                                        <div class="fw-semibold mb-2"><?= e($benchmark['you']) ?></div>
                                        <div class="small text-muted">Typical competitor</div>
                                        <div class="small"><?= e($benchmark['competitor']) ?></div>
                                        <?php if (!empty($benchmark['difference'])): ?>
                                        <div class="small text-muted mt-2 pt-2 border-top"><?= e($benchmark['difference']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="small text-muted mt-3 pt-3 border-top">
                                This benchmark is still summarized from the scan data, and the real competitor domains found for this niche appear below when available.
                            </div>
                        </div>
                    </div>
                </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="summary-kicker">Local Snapshot</div>
                        <h2 class="fw-bold fs-5 mb-3">Local SEO Checkpoints</h2>
                        <?php foreach ($localCheckpointCards as $checkpoint): ?>
                        <div class="local-checkpoint-card local-checkpoint-card--<?= e($checkpoint['tone']) ?>">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-1">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi <?= e($checkpoint['icon']) ?> text-primary"></i>
                                    <span class="fw-semibold"><?= e($checkpoint['label']) ?></span>
                                </div>
                                <span class="badge text-bg-<?= e($checkpoint['tone']) ?>"><?= e($checkpoint['status']) ?></span>
                            </div>
                            <div class="small text-muted"><?= e($checkpoint['detail']) ?></div>
                            <?php if (!empty($checkpoint['evidence'])): ?>
                            <div class="small mt-2 pt-2 border-top text-break"><?= e($checkpoint['evidence']) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php if (!empty($actualCompetitors)): ?>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <div class="summary-kicker">Real Competitors</div>
                        <h2 class="fw-bold fs-5 mb-1">Actual Competitor Domains Found For This Niche</h2>
                        <p class="text-muted small mb-0">These came from Google Places competitor lookups based on the scanned site’s likely service niche and location signals.</p>
                    </div>
                    <span class="badge text-bg-light border"><?= count($actualCompetitors) ?> domain<?= count($actualCompetitors) !== 1 ? 's' : '' ?></span>
                </div>
                <div class="row g-3">
                    <?php foreach ($actualCompetitors as $competitor): ?>
                    <div class="col-lg-4">
                        <div class="benchmark-card h-100">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <div class="fw-semibold"><?= e($competitor['name'] ?? 'Competitor') ?></div>
                                    <div class="small text-muted text-break"><?= e($competitor['domain'] ?? 'Unknown domain') ?></div>
                                </div>
                                <?php if (!empty($competitor['score'])): ?>
                                <span class="badge text-bg-light border"><?= e((string) $competitor['score']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($competitor['address'])): ?>
                            <div class="small text-muted mb-2"><?= e($competitor['address']) ?></div>
                            <?php endif; ?>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <?php if (!empty($competitor['website_url'])): ?>
                                <a href="<?= e($competitor['website_url']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">Open Site</a>
                                <?php endif; ?>
                                <?php if (!empty($competitor['maps_url'])): ?>
                                <a href="<?= e($competitor['maps_url']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">Open Maps</a>
                                <?php endif; ?>
                            </div>
                            <div class="small text-muted mb-2">What looks different:</div>
                            <?php if (!empty($competitor['differences'])): ?>
                            <?php foreach ($competitor['differences'] as $difference): ?>
                            <div class="small mb-1">- <?= e($difference) ?></div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <div class="small text-muted">No major difference stood out more than the rest on the quick comparison.</div>
                            <?php endif; ?>
                            <?php if (!empty($competitor['query'])): ?>
                            <div class="small text-muted mt-3 pt-2 border-top">Search used: <?= e($competitor['query']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($competitorAnalysis['queries'])): ?>
                <div class="small text-muted mt-3 pt-3 border-top">
                    Lookup queries used: <?= e(implode(' | ', array_slice((array) $competitorAnalysis['queries'], 0, 4))) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php elseif (!empty($competitorAnalysis['error'])): ?>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <div class="summary-kicker">Real Competitors</div>
                <h2 class="fw-bold fs-5 mb-2">Actual Competitor Domains</h2>
                <p class="text-muted small mb-0"><?= e($competitorAnalysis['error']) ?></p>
            </div>
        </div>
        <?php endif; ?>
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
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                            <h3 class="fw-bold fs-5 mb-0">Structured Data Coverage</h3>
                            <?php if ($schemaErrorIssue): ?>
                            <span class="badge bg-warning-subtle text-warning-emphasis border">Needs cleanup</span>
                            <?php else: ?>
                            <span class="badge bg-success-subtle text-success border">Signals found</span>
                            <?php endif; ?>
                        </div>
                        <div class="row g-2">
                            <?php foreach ($structuredDataSnapshot as $schemaCard): ?>
                            <div class="col-sm-6">
                                <div class="schema-check-card <?= !empty($schemaCard['present']) ? 'schema-check-card--present' : 'schema-check-card--missing' ?>">
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi <?= e($schemaCard['icon']) ?>"></i>
                                            <span class="fw-semibold small"><?= e($schemaCard['label']) ?></span>
                                        </div>
                                        <span class="badge text-bg-<?= !empty($schemaCard['present']) ? 'success' : 'secondary' ?>"><?= !empty($schemaCard['present']) ? 'Seen' : 'Not clear' ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="small text-muted mt-3">
                            <?= e($schemaErrorIssue ? $issueDisplayExplanation($schemaErrorIssue) : 'The scan found at least some structured data support on the site.') ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                            <h3 class="fw-bold fs-5 mb-0">Local SEO Strength</h3>
                            <span class="badge text-bg-light border"><?= e((string) $localSeoSignalScore) ?>/100</span>
                        </div>
                        <?php foreach ($localCheckpointCards as $checkpoint): ?>
                        <div class="local-checkpoint-card local-checkpoint-card--<?= e($checkpoint['tone']) ?>">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-1">
                                <span class="fw-semibold"><?= e($checkpoint['label']) ?></span>
                                <span class="badge text-bg-<?= e($checkpoint['tone']) ?>"><?= e($checkpoint['status']) ?></span>
                            </div>
                            <div class="small text-muted"><?= e($checkpoint['detail']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
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
                            <span class="fw-semibold"><?= e($issueDisplayTitle($seoIssue)) ?></span>
                        </div>
                        <p class="small text-muted mb-0"><?= e($issueDisplayExplanation($seoIssue)) ?></p>
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
                                <?= e($issueDisplayTitle($pageIssue)) ?>
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

<?php if (!empty($lighthouseMetrics) || !empty($comparison) || !empty($pageSpeedPendingIssue)): ?>
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <?php if (!empty($pageSpeedPendingIssue)): ?>
        <div class="alert alert-secondary border d-flex align-items-start gap-3 mb-4">
            <i class="bi bi-hourglass-split fs-4"></i>
            <div>
                <div class="fw-semibold mb-1"><?= e($issueDisplayTitle($pageSpeedPendingIssue)) ?></div>
                <div class="small mb-0"><?= e($issueDisplayExplanation($pageSpeedPendingIssue)) ?></div>
            </div>
        </div>
        <?php endif; ?>
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
        <?php if (!empty($primarySpeedData['cached'])): ?>
        <div class="alert alert-light border small mb-4">
            This speed snapshot reused a recent cached PageSpeed result to keep the report fast.
        </div>
        <?php endif; ?>
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
                                <div class="issue-impact-row mb-2">
                                    <?php foreach ($issueImpactChips($topIssue) as $chip): ?>
                                    <span class="issue-impact-chip issue-impact-chip--<?= e($chip['tone']) ?>">
                                        <i class="bi <?= e($chip['icon']) ?>"></i><?= e($chip['label']) ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                                <h4 class="summary-priority-title mb-1"><?= e($issueDisplayTitle($topIssue)) ?></h4>
                                <p class="summary-priority-copy"><?= e(mb_strimwidth($issueDisplayExplanation($topIssue), 0, 135, '...')) ?></p>
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
                    $impactChips = $issueImpactChips($issue);
                    ?>
                    <div class="issue-card border-start border-<?= $badgeClass ?> border-3 mb-3" id="issue-<?= (int) ($issue['id'] ?? 0) ?>">
                        <div class="d-flex align-items-start gap-3">
                            <div class="issue-icon text-<?= $badgeClass ?>">
                                <i class="bi <?= $categoryIcon($issue['category']) ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                    <h6 class="fw-bold mb-0"><?= e($issueDisplayTitle($issue)) ?></h6>
                                    <span class="badge bg-light text-muted text-capitalize small"><?= e($issue['category']) ?></span>
                                </div>
                                <div class="issue-meta-line"><?= e($issueMetaSummary($issue)) ?></div>
                                <?php if (!empty($impactChips)): ?>
                                <div class="issue-impact-row">
                                    <?php foreach ($impactChips as $chip): ?>
                                    <span class="issue-impact-chip issue-impact-chip--<?= e($chip['tone']) ?>">
                                        <i class="bi <?= e($chip['icon']) ?>"></i><?= e($chip['label']) ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                <p class="text-muted small mb-2"><?= e($issueDisplayExplanation($issue)) ?></p>
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
                                                <?php if ($issueDisplayWhy($issue) !== ''): ?>
                                                <p class="small mb-2"><strong>Why it matters:</strong> <?= e($issueDisplayWhy($issue)) ?></p>
                                                <?php endif; ?>
                                                <?php if ($issueDisplayFix($issue) !== ''): ?>
                                                <p class="small mb-2"><strong>How to fix:</strong> <?= e($issueDisplayFix($issue)) ?></p>
                                                <?php endif; ?>
                                                <?php if ($issueDisplayImpact($issue) !== ''): ?>
                                                <p class="small mb-0 text-warning-emphasis"><i class="bi bi-graph-down me-1"></i><?= e($issueDisplayImpact($issue)) ?></p>
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

