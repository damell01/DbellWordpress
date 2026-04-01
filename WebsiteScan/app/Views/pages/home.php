<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="badge rounded-pill px-3 py-2" style="background:rgba(98,34,204,0.18);color:#e9dcff;border:1px solid rgba(255,255,255,0.25);font-size:.8rem;">
                        <i class="bi bi-lightning-charge-fill me-1"></i>100% Free Website Audit
                    </span>
                    <span class="badge rounded-pill px-3 py-2" style="background:rgba(251,165,4,0.2);color:#ffe2a0;border:1px solid rgba(251,165,4,0.35);font-size:.8rem;">
                        <i class="bi bi-shield-check me-1"></i>No signup needed
                    </span>
                </div>
                <h1 class="hero-headline display-4 fw-bold mb-4">
                    <?= e($headline ?? 'Find Out What\'s Holding Your Website Back') ?>
                </h1>
                <p class="hero-sub lead mb-4" style="color:rgba(255,255,255,.7);">
                    <?= e($subheadline ?? 'Get a free, instant audit of your website – SEO, accessibility, performance, and conversion issues revealed in seconds.') ?>
                </p>
                <div class="d-flex flex-wrap gap-3 mb-5">
                    <div class="d-flex align-items-center gap-2" style="color:rgba(255,255,255,.65);font-size:.875rem;">
                        <i class="bi bi-check-circle-fill" style="color:#fba504;"></i> 50+ automated checks
                    </div>
                    <div class="d-flex align-items-center gap-2" style="color:rgba(255,255,255,.65);font-size:.875rem;">
                        <i class="bi bi-check-circle-fill" style="color:#fba504;"></i> Instant results
                    </div>
                    <div class="d-flex align-items-center gap-2" style="color:rgba(255,255,255,.65);font-size:.875rem;">
                        <i class="bi bi-check-circle-fill" style="color:#fba504;"></i> Shareable PDF report
                    </div>
                </div>
                <!-- Audit Form -->
                <form action="<?= url('audit') ?>" method="POST" class="hero-form audit-submit-form" data-loading-message="Scanning your site and preparing your report..." onsubmit="return window.SiteScopeHandleAuditSubmit ? window.SiteScopeHandleAuditSubmit(event, this) : true;">
                    <?= csrf_field() ?>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-globe2 text-muted"></i>
                        </span>
                        <input type="url" name="website_url" class="form-control border-start-0 ps-0"
                               placeholder="https://yourbusiness.com" required
                               aria-label="Your website URL"
                               style="font-size:.95rem;">
                        <button class="btn btn-primary px-4 fw-semibold" type="submit" style="min-width:130px;">
                            <span class="submit-label"><i class="bi bi-search me-2 d-none d-sm-inline"></i>Scan My Site</span>
                            <span class="submit-loading d-none"><span class="spinner-border spinner-border-sm me-2"></span>Scanning...</span>
                        </button>
                    </div>
                    <p class="mt-2 mb-0" style="color:rgba(255,255,255,.45);font-size:.8rem;">
                        <i class="bi bi-lock-fill me-1"></i>Secure & private – we never store your login credentials
                    </p>
                    <div class="mt-3">
                        <button type="button"
                                class="btn btn-link text-decoration-none p-0"
                                style="color:rgba(255,255,255,.78);font-size:.9rem;"
                                data-bs-toggle="collapse"
                                data-bs-target="#heroOptionalInfo"
                                aria-expanded="false"
                                aria-controls="heroOptionalInfo">
                            <i class="bi bi-plus-circle me-1"></i>Add your email to also receive the report there (optional)
                        </button>
                    </div>
                    <div class="collapse mt-3" id="heroOptionalInfo">
                        <div class="card border-0" style="background:rgba(255,255,255,.08);backdrop-filter:blur(8px);">
                            <div class="card-body p-3 p-md-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-white-50 small mb-1" for="hero_contact_name">Name (optional)</label>
                                        <input type="text" class="form-control" id="hero_contact_name" name="contact_name"
                                               placeholder="Jane Smith" value="<?= old('contact_name') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-white-50 small mb-1" for="hero_email">Email (optional)</label>
                                        <input type="email" class="form-control" id="hero_email" name="email"
                                               placeholder="jane@yourbusiness.com" value="<?= old('email') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-white-50 small mb-1" for="hero_phone">Phone (optional)</label>
                                        <input type="tel" class="form-control" id="hero_phone" name="phone"
                                               placeholder="(555) 123-4567" value="<?= old('phone') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-white-50 small mb-1" for="hero_business_name">Business Name (optional)</label>
                                        <input type="text" class="form-control" id="hero_business_name" name="business_name"
                                               placeholder="Your Business LLC" value="<?= old('business_name') ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label text-white-50 small mb-1" for="hero_notes">What do you need help with? (optional)</label>
                                        <textarea class="form-control" id="hero_notes" name="notes" rows="2"
                                                  placeholder="SEO improvements, accessibility fixes, speed issues..."><?= old('notes') ?></textarea>
                                    </div>
                                </div>
                                <p class="mb-0 mt-3" style="color:rgba(255,255,255,.58);font-size:.8rem;">
                                    <i class="bi bi-envelope-check me-1"></i>The report still opens right away in the browser. If email is configured, we can send a copy there too.
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-6">
                <div class="hero-score-preview">
                    <div class="score-card-demo">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <div style="color:rgba(255,255,255,.5);font-size:.7rem;letter-spacing:.08em;text-transform:uppercase;">Website Audit</div>
                                <div style="color:rgba(255,255,255,.8);font-size:.8rem;font-weight:600;">example-business.com</div>
                            </div>
                            <span style="background:rgba(245,158,11,0.2);color:#fcd34d;border:1px solid rgba(245,158,11,.25);border-radius:20px;padding:.2rem .65rem;font-size:.7rem;font-weight:600;">C – Fair</span>
                        </div>
                        <div class="score-ring-demo mb-4">
                            <svg viewBox="0 0 120 120" class="score-svg">
                                <circle cx="60" cy="60" r="50" fill="none" stroke="rgba(255,255,255,.1)" stroke-width="10"/>
                                <circle cx="60" cy="60" r="50" fill="none" stroke="url(#scoreGrad)" stroke-width="10"
                                        stroke-dasharray="314.2" stroke-dashoffset="94.3"
                                        stroke-linecap="round" transform="rotate(-90 60 60)"/>
                                <defs>
                                    <linearGradient id="scoreGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" style="stop-color:#7a2fe0"/>
                                        <stop offset="100%" style="stop-color:#6222cc"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                            <div class="score-center">
                                <div class="score-num">70</div>
                                <div class="score-label">/ 100</div>
                            </div>
                        </div>
                        <div class="score-bars">
                            <div class="score-bar-row"><span>SEO</span><div class="bar"><div class="fill" style="width:65%;background:linear-gradient(90deg,#6222cc,#7a2fe0)"></div></div><span class="val">65</span></div>
                            <div class="score-bar-row"><span>Accessibility</span><div class="bar"><div class="fill" style="width:72%;background:linear-gradient(90deg,#7a2fe0,#9b5cf0)"></div></div><span class="val">72</span></div>
                            <div class="score-bar-row"><span>Conversion</span><div class="bar"><div class="fill" style="width:58%;background:linear-gradient(90deg,#f08f00,#fba504)"></div></div><span class="val">58</span></div>
                            <div class="score-bar-row"><span>Technical</span><div class="bar"><div class="fill" style="width:80%;background:linear-gradient(90deg,#059669,#10b981)"></div></div><span class="val">80</span></div>
                            <div class="score-bar-row"><span>Local</span><div class="bar"><div class="fill" style="width:45%;background:linear-gradient(90deg,#0891b2,#06b6d4)"></div></div><span class="val">45</span></div>
                        </div>
                        <div class="mt-3 pt-3" style="border-top:1px solid rgba(255,255,255,.08);">
                            <div class="d-flex justify-content-between" style="font-size:.75rem;color:rgba(255,255,255,.5);">
                                <span><i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>3 Critical Issues</span>
                                <span><i class="bi bi-list-check me-1"></i>18 Total Findings</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-wave">
        <svg viewBox="0 0 1440 70" preserveAspectRatio="none"><path d="M0,35 C240,70 480,0 720,35 C960,70 1200,0 1440,35 L1440,70 L0,70 Z" fill="#f8fafc"/></svg>
    </div>
</section>

<!-- Trust Bar -->
<section class="py-4" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
    <div class="container">
        <div class="row g-3 text-center justify-content-center">
            <div class="col-6 col-md-3">
                <div class="fw-800 display-6" style="font-size:2rem;font-weight:800;color:#2563eb;letter-spacing:-1px;">50+</div>
                <div class="text-muted small fw-500">Audit Checks</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="fw-800 display-6" style="font-size:2rem;font-weight:800;color:#7c3aed;letter-spacing:-1px;">5</div>
                <div class="text-muted small fw-500">Score Categories</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="fw-800 display-6" style="font-size:2rem;font-weight:800;color:#10b981;letter-spacing:-1px;">100%</div>
                <div class="text-muted small fw-500">Free Forever</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="fw-800 display-6" style="font-size:2rem;font-weight:800;color:#f59e0b;letter-spacing:-1px;">&lt;60s</div>
                <div class="text-muted small fw-500">Instant Results</div>
            </div>
        </div>
    </div>
</section>

<!-- Audit Categories -->
<section class="py-6 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill mb-3">What We Check</span>
            <h2 class="fw-bold display-6">Everything Graded Across 5 Key Areas</h2>
            <p class="text-muted lead mx-auto" style="max-width:580px;">Our audit engine performs 50+ checks to uncover every issue affecting your website's performance, visibility, and conversions.</p>
        </div>
        <div class="row g-4">
            <?php
            $cats = [
                ['icon'=>'bi-search',          'color'=>'primary', 'title'=>'SEO Analysis',
                 'desc'=>'Title tags, meta descriptions, headings, sitemap, robots.txt, Open Graph, canonical URLs, and structured data.',
                 'checks'=>['Title tag quality','Meta descriptions','H1/H2 structure','XML sitemap','Robots.txt','Open Graph tags']],
                ['icon'=>'bi-universal-access', 'color'=>'purple',  'title'=>'Accessibility',
                 'desc'=>'Alt text, form labels, heading structure, skip links, language attributes, and WCAG compliance checks.',
                 'checks'=>['Image alt text','Form labels','Skip navigation','Colour contrast','ARIA landmarks','Language attribute']],
                ['icon'=>'bi-graph-up-arrow',   'color'=>'warning', 'title'=>'Conversion & Trust',
                 'desc'=>'Phone visibility, contact forms, CTAs, testimonials, social proof, trust signals, and lead capture.',
                 'checks'=>['Phone number visible','Contact form present','Clear CTAs','Trust badges','Testimonials','Social proof']],
                ['icon'=>'bi-lightning-charge', 'color'=>'success', 'title'=>'Technical Quality',
                 'desc'=>'Page speed, HTTP status, mobile viewport, image optimisation, script loading, and SSL security.',
                 'checks'=>['Mobile viewport','HTTPS/SSL','Image optimisation','Script loading','404 errors','Page speed']],
                ['icon'=>'bi-geo-alt-fill',     'color'=>'info',    'title'=>'Local Business',
                 'desc'=>'Address visibility, Google Maps, business hours, schema markup, and local trust signals for SMBs.',
                 'checks'=>['Address visible','Google Maps','Business hours','Schema markup','Local keywords','NAP consistency']],
                ['icon'=>'bi-file-earmark-bar-graph','color'=>'danger','title'=>'Detailed Reporting',
                 'desc'=>'Every issue explained in plain English with why it matters, its business impact, and exactly how to fix it.',
                 'checks'=>['Severity ratings','Business impact','Fix instructions','Priority order','Score breakdown','Shareable link']],
            ];
            foreach ($cats as $cat): ?>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card h-100 hover-lift">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="feature-icon bg-<?= $cat['color'] ?>-soft text-<?= $cat['color'] ?>" style="flex-shrink:0;">
                            <i class="bi <?= $cat['icon'] ?>"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1"><?= e($cat['title']) ?></h5>
                            <p class="text-muted small mb-0"><?= e($cat['desc']) ?></p>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($cat['checks'] as $chk): ?>
                        <span class="badge bg-<?= $cat['color'] ?>-soft text-<?= $cat['color'] ?>" style="font-size:.7rem;font-weight:500;"><?= e($chk) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-6" style="background:linear-gradient(180deg,#f8fafc 0%,#fff 100%);">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill mb-3">How It Works</span>
            <h2 class="fw-bold display-6">Get Your Full Audit in Under 60 Seconds</h2>
            <p class="text-muted">No account, no credit card, no catch</p>
        </div>
        <div class="row g-0 justify-content-center position-relative">
            <!-- Connector line (desktop only) -->
            <div class="d-none d-md-block" style="position:absolute;top:28px;left:calc(12.5% + 28px);right:calc(12.5% + 28px);height:2px;background:linear-gradient(90deg,#2563eb,#7c3aed,#10b981,#f59e0b);opacity:.25;z-index:0;"></div>
            <?php
            $steps = [
                ['n'=>'1','icon'=>'bi-globe2','color'=>'primary','title'=>'Enter Your URL','text'=>'Type your website address into the audit form. No account or signup needed.'],
                ['n'=>'2','icon'=>'bi-cpu','color'=>'purple','title'=>'We Scan Your Site','text'=>'Our engine runs 50+ automated checks across SEO, accessibility, technical, and conversion factors.'],
                ['n'=>'3','icon'=>'bi-file-earmark-bar-graph','color'=>'success','title'=>'Get Your Report','text'=>'Receive a detailed, scored report with every issue prioritised by severity and business impact.'],
                ['n'=>'4','icon'=>'bi-rocket-takeoff','color'=>'warning','title'=>'Fix & Improve','text'=>'Fix issues yourself using our step-by-step guides, or contact us to fix everything for you.'],
            ];
            foreach ($steps as $s): ?>
            <div class="col-6 col-md-3 text-center px-3 mb-4 mb-md-0 position-relative" style="z-index:1;">
                <div class="step-circle mx-auto mb-3" style="background:linear-gradient(135deg,#2563eb,#7c3aed);"><?= $s['n'] ?></div>
                <i class="bi <?= $s['icon'] ?> text-<?= $s['color'] ?> fs-4 mb-2 d-block"></i>
                <h6 class="fw-bold"><?= e($s['title']) ?></h6>
                <p class="text-muted small mb-0"><?= e($s['text']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="<?= url('audit') ?>" class="btn btn-primary btn-lg px-5 shadow-sm fw-semibold">
                <i class="bi bi-search me-2"></i>Start My Free Audit
            </a>
        </div>
    </div>
</section>

<!-- What You Get in the Report -->
<section class="py-6 bg-light">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill mb-3">Sample Report</span>
                <h2 class="fw-bold display-6 mb-4">A Report Your Team Can Actually Use</h2>
                <div class="d-flex flex-column gap-3">
                    <?php
                    $reportFeatures = [
                        ['icon'=>'bi-bar-chart-steps','color'=>'primary','title'=>'Overall + Category Scores','desc'=>'One score out of 100 with breakdowns for SEO, Accessibility, Conversion, Technical, and Local Business.'],
                        ['icon'=>'bi-exclamation-triangle-fill','color'=>'danger','title'=>'Issues Ranked by Severity','desc'=>'Critical, High, Medium, and Low priority issues so you know exactly what to tackle first.'],
                        ['icon'=>'bi-wrench','color'=>'success','title'=>'Step-by-Step Fix Instructions','desc'=>'Plain-English explanations of every issue plus exactly how to resolve it.'],
                        ['icon'=>'bi-currency-dollar','color'=>'warning','title'=>'Business Impact Analysis','desc'=>'Understand the real-world cost of each issue – traffic lost, leads missed, rankings hurt.'],
                        ['icon'=>'bi-camera','color'=>'info','title'=>'Live Website Screenshot','desc'=>'A screenshot of your site at the time of the audit so visual issues are easy to spot.'],
                        ['icon'=>'bi-share','color'=>'purple','title'=>'Shareable Report Link','desc'=>'Every report gets a unique, shareable link so you can send it to your team or developer.'],
                    ];
                    foreach ($reportFeatures as $rf): ?>
                    <div class="d-flex gap-3">
                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3 bg-<?= $rf['color'] ?>-soft text-<?= $rf['color'] ?>" style="width:40px;height:40px;font-size:1.1rem;">
                            <i class="bi <?= $rf['icon'] ?>"></i>
                        </div>
                        <div>
                            <div class="fw-semibold small"><?= e($rf['title']) ?></div>
                            <div class="text-muted small"><?= e($rf['desc']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <!-- Mock report card -->
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div style="background:linear-gradient(135deg,#0f172a,#1e3a5f);padding:1.5rem;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <div style="color:rgba(255,255,255,.5);font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;">Audit Report</div>
                                <div style="color:#fff;font-weight:700;font-size:.9rem;">yourbusiness.com</div>
                            </div>
                            <span style="background:rgba(245,158,11,.2);color:#fcd34d;border:1px solid rgba(245,158,11,.25);border-radius:20px;padding:.2rem .7rem;font-size:.75rem;font-weight:700;">C – 70/100</span>
                        </div>
                        <div class="row g-2">
                            <?php
                            $mockScores = [
                                ['label'=>'SEO','val'=>65,'color'=>'#3b82f6'],
                                ['label'=>'Access.','val'=>72,'color'=>'#a855f7'],
                                ['label'=>'Conversion','val'=>58,'color'=>'#f59e0b'],
                                ['label'=>'Technical','val'=>80,'color'=>'#10b981'],
                                ['label'=>'Local','val'=>45,'color'=>'#06b6d4'],
                            ];
                            foreach ($mockScores as $ms): ?>
                            <div class="col">
                                <div style="background:rgba(255,255,255,.06);border-radius:8px;padding:.5rem .4rem;text-align:center;">
                                    <div style="font-size:1rem;font-weight:800;color:#fff;"><?= $ms['val'] ?></div>
                                    <div style="font-size:.65rem;color:rgba(255,255,255,.5);"><?= $ms['label'] ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="p-3">
                        <?php
                        $mockIssues = [
                            ['sev'=>'Critical','color'=>'danger', 'title'=>'Missing meta description'],
                            ['sev'=>'High',    'color'=>'warning','title'=>'No H1 tag found on page'],
                            ['sev'=>'High',    'color'=>'warning','title'=>'Images missing alt text (14)'],
                            ['sev'=>'Medium',  'color'=>'primary', 'title'=>'No contact phone number visible'],
                            ['sev'=>'Medium',  'color'=>'primary', 'title'=>'Missing robots.txt file'],
                        ];
                        foreach ($mockIssues as $mi): ?>
                        <div class="d-flex align-items-center gap-2 py-2" style="border-bottom:1px solid #f1f5f9;">
                            <span class="badge bg-<?= $mi['color'] ?>" style="min-width:60px;font-size:.65rem;"><?= $mi['sev'] ?></span>
                            <span style="font-size:.8rem;color:#334155;"><?= $mi['title'] ?></span>
                        </div>
                        <?php endforeach; ?>
                        <div class="text-center pt-2">
                            <span class="text-muted" style="font-size:.75rem;">+ 13 more findings</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services -->
<section class="py-6 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill mb-3">Professional Services</span>
            <h2 class="fw-bold display-6">Need Help Fixing What We Find?</h2>
            <p class="text-muted lead mx-auto" style="max-width:560px;">Run the free audit, then get in touch. We'll fix every issue and improve your website professionally.</p>
        </div>
        <div class="row g-4">
            <?php
            $services = [
                ['icon'=>'bi-tools','color'=>'primary','title'=>'Quick Fixes','desc'=>'Fix your top priority issues fast – SEO basics, mobile, meta tags, and more. Delivered rapidly.'],
                ['icon'=>'bi-stars','color'=>'purple','title'=>'Full Audit Fix','desc'=>'We address every single issue flagged in your report – nothing left behind. A complete overhaul.'],
                ['icon'=>'bi-arrow-repeat','color'=>'success','title'=>'Monthly Care','desc'=>'Ongoing monthly audits, maintenance, security monitoring, and priority support.'],
                ['icon'=>'bi-search','color'=>'info','title'=>'SEO Growth','desc'=>'Climb search rankings with a data-driven strategy built around your audit findings.'],
                ['icon'=>'bi-universal-access','color'=>'warning','title'=>'Accessibility','desc'=>'WCAG-compliant fixes so every visitor can access and use your website effectively.'],
                ['icon'=>'bi-palette','color'=>'danger','title'=>'Website Redesign','desc'=>'Modern, conversion-focused redesign built from scratch with SEO baked in from day one.'],
            ];
            foreach ($services as $service): ?>
            <div class="col-md-6 col-lg-4">
                <div class="service-card hover-lift">
                    <div class="service-icon text-<?= $service['color'] ?>"><i class="bi <?= $service['icon'] ?>"></i></div>
                    <h5 class="fw-bold mt-3"><?= e($service['title']) ?></h5>
                    <p class="text-muted small"><?= e($service['desc']) ?></p>
                    <a href="<?= url('fix-my-website') ?>#get-in-touch" class="btn btn-outline-<?= $service['color'] ?> btn-sm mt-auto">
                        Get in Touch <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?= url('fix-my-website') ?>" class="btn btn-outline-primary btn-lg">
                View All Services <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-6 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill mb-3">Social Proof</span>
            <h2 class="fw-bold display-6">What Business Owners Say</h2>
            <p class="text-muted">Real results for real businesses</p>
        </div>
        <div class="row g-4">
            <?php
            $testimonials = [
                ['name'=>'Sarah M.','role'=>'Restaurant Owner','text'=>'The free audit found 3 critical issues I had no idea about. Got them fixed and my Google rankings improved within weeks!','rating'=>5,'init'=>'S'],
                ['name'=>'John D.','role'=>'Contractor','text'=>'I had no idea my site wasn\'t mobile-friendly. The audit was eye-opening and the fix team was brilliant to work with.','rating'=>5,'init'=>'J'],
                ['name'=>'Lisa K.','role'=>'Hair Salon Owner','text'=>'My accessibility score was terrible. They fixed everything and now I get way more calls from my website. Highly recommend.','rating'=>5,'init'=>'L'],
            ];
            foreach ($testimonials as $t): ?>
            <div class="col-md-4">
                <div class="testimonial-card hover-lift">
                    <div class="d-flex mb-3">
                        <?php for ($i = 0; $i < $t['rating']; $i++): ?>
                        <i class="bi bi-star-fill text-warning" style="font-size:.9rem;"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="mb-4 text-dark">"<?= e($t['text']) ?>"</p>
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-circle"><?= $t['init'] ?></div>
                        <div>
                            <div class="fw-semibold small"><?= e($t['name']) ?></div>
                            <div class="text-muted" style="font-size:.775rem;"><?= e($t['role']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Final CTA -->
<section class="cta-section py-6" style="background:linear-gradient(135deg,#060c1a 0%,#0f172a 40%,#1a2d4f 100%);position:relative;overflow:hidden;">
    <div style="position:absolute;top:-50%;right:-10%;width:600px;height:600px;background:radial-gradient(circle,rgba(37,99,235,.15) 0%,transparent 65%);border-radius:50%;pointer-events:none;"></div>
    <div class="container text-center position-relative">
        <h2 class="display-5 fw-bold text-white mb-3">
            Ready to See How Your Website Scores?
        </h2>
        <p class="lead mb-5" style="color:rgba(255,255,255,.6);">
            100% free audit. Instant results. No registration required.
        </p>
        <div class="d-flex flex-wrap gap-3 justify-content-center">
            <a href="<?= url('audit') ?>" class="btn btn-primary btn-lg px-5 fw-semibold shadow">
                <i class="bi bi-search me-2"></i>Get My Free Audit
            </a>
            <a href="<?= url('fix-my-website') ?>#get-in-touch" class="btn btn-outline-light btn-lg px-5">
                <i class="bi bi-chat-dots me-2"></i>Talk to Us
            </a>
        </div>
        <p class="mt-3 mb-0" style="color:rgba(255,255,255,.35);font-size:.8rem;">
            <i class="bi bi-shield-check me-1"></i>No credit card · No signup · Instant results
        </p>
    </div>
</section>
