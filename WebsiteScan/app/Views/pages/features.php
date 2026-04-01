<section class="py-6">
    <div class="container">
        <div class="text-center mb-5">
            <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill mb-3">Features</div>
            <h1 class="display-5 fw-bold">Everything You Need to Know About Your Website</h1>
        <p class="lead text-muted">VerityScan performs over 50 checks across 5 critical categories</p>
        </div>
        <div class="row g-5">
            <?php
            $features = [
                ['icon'=>'bi-search','color'=>'primary','title'=>'SEO Analysis',
                 'items'=>['Page title tag quality','Meta description analysis','H1/H2/H3 structure check','Canonical URL detection','Open Graph & Twitter Card tags','Sitemap & robots.txt presence','HTTPS / SSL detection','Missing favicon check']],
                ['icon'=>'bi-universal-access','color'=>'purple','title'=>'Accessibility Checks',
                 'items'=>['HTML language attribute','Image alt text coverage','Form label associations','Button text accessibility','Skip navigation links','Heading hierarchy quality','Link text quality check','Viewport meta tag']],
                ['icon'=>'bi-graph-up-arrow','color'=>'warning','title'=>'Conversion & Trust',
                 'items'=>['Phone number visibility','Email address detection','Contact form presence','CTA button detection','Testimonial/review section','Social media link presence','Trust badges detection','Lead capture analysis']],
                ['icon'=>'bi-lightning','color'=>'success','title'=>'Technical Checks',
                 'items'=>['Page response time','HTTP status code','Page size estimation','Script count analysis','SSL certificate','Render-blocking resources','Image optimization hints','Mobile viewport check']],
                ['icon'=>'bi-geo-alt','color'=>'info','title'=>'Local Business Readiness',
                 'items'=>['Physical address detection','Google Maps embed','Business hours presence','Schema.org markup','Review/testimonial signals','Service area detection','Local trust indicators','NAP consistency hints']],
            ];
            foreach ($features as $f): ?>
            <div class="col-md-6 col-xl-4">
                <div class="feature-detail-card">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="feature-icon bg-<?= $f['color'] ?>-soft text-<?= $f['color'] ?>">
                            <i class="bi <?= $f['icon'] ?>"></i>
                        </div>
                        <h5 class="fw-bold mb-0"><?= e($f['title']) ?></h5>
                    </div>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($f['items'] as $item): ?>
                        <li class="d-flex align-items-center gap-2 mb-2 small">
                            <i class="bi bi-check-circle-fill text-success flex-shrink-0"></i>
                            <?= e($item) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Scoring Section -->
        <div class="mt-6 text-center">
            <h2 class="fw-bold display-6 mb-3">How Scoring Works</h2>
            <p class="text-muted lead mb-5">Each issue has a severity level that affects your score</p>
            <div class="row g-3 justify-content-center">
                <?php
                $severities = [
                    ['label'=>'Critical','color'=>'danger','pts'=>20,'desc'=>'Must fix immediately'],
                    ['label'=>'High','color'=>'warning','pts'=>10,'desc'=>'Fix as soon as possible'],
                    ['label'=>'Medium','color'=>'primary','pts'=>5,'desc'=>'Plan to address soon'],
                    ['label'=>'Low','color'=>'info','pts'=>2,'desc'=>'Nice to have'],
                    ['label'=>'Info','color'=>'secondary','pts'=>0,'desc'=>'Informational only'],
                ];
                foreach ($severities as $s): ?>
                <div class="col-6 col-md-auto">
                    <div class="severity-pill bg-<?= $s['color'] ?>-soft border border-<?= $s['color'] ?> rounded px-3 py-3 text-center">
                        <div class="badge bg-<?= $s['color'] ?> mb-1"><?= $s['label'] ?></div>
                        <div class="fw-bold fs-5">-<?= $s['pts'] ?></div>
                        <div class="text-muted small"><?= $s['desc'] ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- CTA -->
        <div class="text-center mt-6">
            <a href="<?= url('audit') ?>" class="btn btn-primary btn-lg px-5">
                <i class="bi bi-search me-2"></i>Run Your Free Audit Now
            </a>
        </div>
    </div>
</section>
