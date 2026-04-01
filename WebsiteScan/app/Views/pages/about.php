<section class="py-6 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5">
                <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill mb-3">About</div>
                <h1 class="display-5 fw-bold">Why Every Business Needs a Website Audit</h1>
                <p class="lead text-muted">Most businesses don't know what's holding their website back.</p>
            </div>
        </div>
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <h2 class="fw-bold mb-4">Your website is your 24/7 salesperson.</h2>
                <p class="text-muted mb-3">Most small business websites have critical issues that silently cost them customers every single day. These issues range from missing SEO metadata that prevents Google from showing them, to accessibility problems that exclude visitors, to missing contact forms that make it hard for leads to reach out.</p>
                    <p class="text-muted mb-3">We built VerityScan to give every business owner a free, honest audit of their website with clear explanations and actionable recommendations.</p>
                <p class="text-muted mb-4">You deserve to know why your website isn't converting, and you deserve a plan to fix it.</p>
                <a href="<?= url('audit') ?>" class="btn btn-primary btn-lg">
                    <i class="bi bi-search me-2"></i>Audit My Website Free
                </a>
            </div>
            <div class="col-lg-6">
                <div class="row g-3">
                    <?php
                    $stats = [
                        ['num'=>'70%','desc'=>'of small business websites have at least one critical SEO issue','color'=>'primary'],
                        ['num'=>'96%','desc'=>'of websites fail basic accessibility standards','color'=>'purple'],
                        ['num'=>'68%','desc'=>'of visitors leave a site with no clear CTA without contacting the business','color'=>'warning'],
                        ['num'=>'53%','desc'=>'of mobile users leave if a site takes more than 3 seconds to load','color'=>'danger'],
                    ];
                    foreach ($stats as $s): ?>
                    <div class="col-6">
                        <div class="stat-highlight-card">
                            <div class="display-5 fw-bold text-<?= $s['color'] ?>"><?= e($s['num']) ?></div>
                            <p class="text-muted small mb-0"><?= e($s['desc']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-6">
    <div class="container text-center">
        <h2 class="fw-bold mb-3">We're Here to Help You Fix It</h2>
        <p class="text-muted lead mb-5">The audit is free. If you want help fixing what we find, that's what we do.</p>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="service-card">
                    <i class="bi bi-search text-primary fs-2 mb-3 d-block"></i>
                    <h5 class="fw-bold">SEO & Visibility</h5>
                    <p class="text-muted">Get found on Google for the keywords your customers are searching.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card">
                    <i class="bi bi-universal-access text-purple fs-2 mb-3 d-block"></i>
                    <h5 class="fw-bold">Accessibility</h5>
                    <p class="text-muted">Make your site usable for all visitors and avoid potential legal exposure.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card">
                    <i class="bi bi-graph-up text-success fs-2 mb-3 d-block"></i>
                    <h5 class="fw-bold">More Leads</h5>
                    <p class="text-muted">Turn more website visitors into customers with conversion improvements.</p>
                </div>
            </div>
        </div>
        <div class="mt-5">
            <a href="<?= url('contact') ?>" class="btn btn-primary btn-lg me-3">Contact Us</a>
            <a href="<?= url('audit') ?>" class="btn btn-outline-primary btn-lg">Free Audit</a>
        </div>
    </div>
</section>
