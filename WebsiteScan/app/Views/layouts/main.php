<?php
$isEmbed = (($_GET['embed'] ?? '') === '1');
$resolvedTitle = $title ?? $appName ?? 'DBell Website Scanner';
$resolvedDescription = $metaDescription ?? 'Run a free website audit to uncover SEO, performance, UX, and mobile issues that may be hurting your rankings and lead generation.';
$resolvedCanonical = $canonicalUrl ?? url(trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/'));
$resolvedRobots = $robots ?? 'index,follow';
$schemaPayload = $schema ?? [
    '@context' => 'https://schema.org',
    '@type' => 'WebPage',
    'name' => $resolvedTitle,
    'description' => $resolvedDescription,
    'url' => $resolvedCanonical,
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($resolvedTitle) ?></title>
    <meta name="description" content="<?= e($resolvedDescription) ?>">
    <meta name="robots" content="<?= e($resolvedRobots) ?>">
    <link rel="canonical" href="<?= e($resolvedCanonical) ?>">
    <link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/img/WebsiteSmallIcon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;700&family=Jost:wght@500;600;700;800&display=swap" rel="stylesheet">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($resolvedTitle) ?>">
    <meta property="og:description" content="<?= e($resolvedDescription) ?>">
    <meta property="og:url" content="<?= e($resolvedCanonical) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($resolvedTitle) ?>">
    <meta name="twitter:description" content="<?= e($resolvedDescription) ?>">
    <script type="application/ld+json"><?= json_encode($schemaPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="<?= $isEmbed ? 'scan-embed' : '' ?>">

<?php if (!$isEmbed): ?>
<nav class="navbar navbar-expand-lg navbar-dark ss-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= url('/') ?>">
            <span class="brand-icon"><i class="bi bi-bar-chart-line-fill"></i></span>
            <span class="brand-stack">
                <span class="brand-top">DBELL CREATIONS</span>
                <span class="brand-sub">WEBSITE SCANNER</span>
            </span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav ms-auto align-items-center gap-1">
                <li class="nav-item"><a class="nav-link" href="/index.html">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="/about.html">About</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Services</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/webDesign.html">Web Design</a></li>
                        <li><a class="dropdown-item" href="/software.html">Custom Software</a></li>
                        <li><a class="dropdown-item" href="/automations.html">Business Automation</a></li>
                        <li><a class="dropdown-item" href="/marketing.html">Digital Marketing</a></li>
                        <li><a class="dropdown-item" href="/seo.html">SEO Optimization</a></li>
                        <li><a class="dropdown-item" href="<?= url('audit') ?>">Free Website Scanner</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="/project.html">Portfolio</a></li>
                <li class="nav-item"><a class="nav-link" href="/frequentlyaskedquestions.html">FAQ</a></li>
                <li class="nav-item"><a class="nav-link" href="/contact.html">Contact</a></li>
                <li class="nav-item">
                    <a class="btn btn-primary btn-sm ms-lg-2 px-3 fw-semibold" href="/contact.html">
                        Contact Us
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>

<?php $flashSuccess = get_flash('success'); $flashError = get_flash('error'); ?>
<?php if (!$isEmbed && $flashSuccess): ?>
<div class="alert alert-success alert-dismissible fade show mb-0 rounded-0 border-0 border-start border-4 border-success" role="alert" style="border-radius:0!important;">
    <div class="container d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill text-success"></i>
        <?= e($flashSuccess) ?>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!$isEmbed && $flashError): ?>
<div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0 border-0 border-start border-4 border-danger" role="alert" style="border-radius:0!important;">
    <div class="container d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-circle-fill text-danger"></i>
        <?= e($flashError) ?>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<main id="main-content">
    <?= $content ?>
</main>

<?php if (!$isEmbed): ?>
<footer class="ss-footer py-5 mt-5">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-4 mb-3 mb-lg-0">
                <h5 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-bar-chart-line-fill text-primary"></i><?= e($appName ?? 'DBell Website Scanner') ?>
                </h5>
                <p class="footer-muted mb-3">Free website audit tool helping businesses understand and fix what's holding their website back.</p>
                <div class="d-flex gap-2">
                    <a href="<?= url('audit') ?>" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-search me-1"></i>Free Audit
                    </a>
                    <a href="<?= url('fix-my-website') ?>" class="btn btn-outline-light btn-sm px-3">
                        Services
                    </a>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <h6 class="text-white fw-semibold mb-3">Platform</h6>
                <ul class="list-unstyled mb-0">
                    <li><a href="<?= url('audit') ?>" class="footer-link">Free Audit</a></li>
                    <li><a href="<?= url('features') ?>" class="footer-link">Features</a></li>
                    <li><a href="<?= url('about') ?>" class="footer-link">About</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-6">
                <h6 class="text-white fw-semibold mb-3">Services</h6>
                <ul class="list-unstyled mb-0">
                    <li><a href="<?= url('fix-my-website') ?>" class="footer-link">Fix My Website</a></li>
                    <li><a href="<?= url('contact') ?>" class="footer-link">Contact Us</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-6">
                <h6 class="text-white fw-semibold mb-3">Legal</h6>
                <ul class="list-unstyled mb-0">
                    <li><a href="<?= url('privacy') ?>" class="footer-link">Privacy Policy</a></li>
                    <li><a href="<?= url('terms') ?>" class="footer-link">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        <hr class="footer-divider mt-4 mb-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <p class="text-muted small mb-0">&copy; <?= date('Y') ?> <?= e($appName ?? 'DBell Website Scanner') ?>. All rights reserved.</p>
            <p class="text-muted small mb-0">Built to help your business grow online.</p>
        </div>
    </div>
</footer>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<script>
if (!window.SiteScopeHandleAuditSubmit) {
    window.SiteScopeShowAuditLoading = function(form) {
        var existingOverlay = document.querySelector('.audit-loading-overlay');
        if (existingOverlay) return existingOverlay;

        var overlay = document.createElement('div');
        overlay.className = 'audit-loading-overlay';
        overlay.innerHTML =
            '<div class="audit-loading-card">' +
                '<div class="audit-loading-icon mb-3" data-loading-icon>' +
                    '<div class="spinner-border text-primary" role="status" aria-hidden="true"></div>' +
                '</div>' +
                '<h3 data-loading-title>Building Your Report</h3>' +
                '<p data-loading-copy>' + ((form && form.getAttribute('data-loading-message')) || 'Scanning your site...') + '</p>' +
                '<div class="audit-loading-progress-wrap">' +
                    '<div class="audit-loading-progress-meta">' +
                        '<span>Progress</span>' +
                        '<strong data-loading-progress-text>0%</strong>' +
                    '</div>' +
                    '<div class="audit-loading-progress-bar">' +
                        '<span data-loading-progress-fill style="width:0%"></span>' +
                    '</div>' +
                '</div>' +
                '<div class="audit-loading-steps">' +
                    '<div class="audit-loading-step is-active" data-step>Checking SEO</div>' +
                    '<div class="audit-loading-step" data-step>Checking speed</div>' +
                    '<div class="audit-loading-step" data-step>Checking contact info</div>' +
                    '<div class="audit-loading-step" data-step>Finalizing your report</div>' +
                '</div>' +
            '</div>';
        document.body.appendChild(overlay);
        return overlay;
    };

    window.SiteScopeRunAuditLoadingSequence = function(overlay) {
        if (!overlay || overlay.dataset.sequenceStarted === '1') return;
        overlay.dataset.sequenceStarted = '1';

        var steps = overlay.querySelectorAll('[data-step]');
        var title = overlay.querySelector('[data-loading-title]');
        var copy = overlay.querySelector('[data-loading-copy]');
        var icon = overlay.querySelector('[data-loading-icon]');
        var progressText = overlay.querySelector('[data-loading-progress-text]');
        var progressFill = overlay.querySelector('[data-loading-progress-fill]');
        var stepMessages = [
            'Reviewing the page structure and SEO basics.',
            'Checking performance signals and speed insights.',
            'Looking for contact details, forms, and trust signals.',
            'Packaging everything into a clean report.'
        ];
        var stepPercents = [18, 46, 74, 92];

        var setProgress = function(percent) {
            var safePercent = Math.max(0, Math.min(100, percent));
            if (progressText) progressText.textContent = safePercent + '%';
            if (progressFill) progressFill.style.width = safePercent + '%';
        };

        setProgress(4);

        steps.forEach(function(step, index) {
            setTimeout(function() {
                steps.forEach(function(otherStep, otherIndex) {
                    otherStep.classList.toggle('is-active', otherIndex === index);
                    if (otherIndex < index) {
                        otherStep.classList.add('is-complete');
                    }
                });

                if (copy && stepMessages[index]) {
                    copy.textContent = stepMessages[index];
                }
                setProgress(stepPercents[index] || 0);
            }, index * 420);
        });

        setTimeout(function() {
            steps.forEach(function(step) {
                step.classList.remove('is-active');
                step.classList.add('is-complete');
            });
            overlay.classList.add('is-complete');
            if (title) title.textContent = 'Report Ready';
            if (copy) copy.textContent = 'Opening your audit results now.';
            if (icon) {
                icon.innerHTML = '<div class="audit-loading-complete-mark"><i class="bi bi-check2"></i></div>';
            }
            setProgress(100);
        }, 1800);
    };

    window.SiteScopeHandleAuditSubmit = function(event, form) {
        form = form || (event ? event.target : null);
        if (!form) return true;
        if (event) event.preventDefault();
        if (form.dataset.submitting === '1' || form.dataset.submitting === '2') return false;

        form.dataset.submitting = '1';
        var submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            var label = submitButton.querySelector('.submit-label, #btnText');
            var loading = submitButton.querySelector('.submit-loading, #btnLoading');
            if (label) label.classList.add('d-none');
            if (loading) loading.classList.remove('d-none');
        }

        var overlay = window.SiteScopeShowAuditLoading(form);
        window.SiteScopeRunAuditLoadingSequence(overlay);

        setTimeout(function() {
            form.dataset.submitting = '2';
            form.submit();
        }, 2250);

        return false;
    };
}
</script>
</body>
</html>
