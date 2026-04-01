<!-- Page Hero -->
<section class="py-6" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); position:relative; overflow:hidden;">
    <div style="position:absolute;top:-80px;right:-80px;width:400px;height:400px;background:radial-gradient(circle,rgba(37,99,235,.18) 0%,transparent 70%);border-radius:50%;"></div>
    <div class="container position-relative text-center py-5">
        <div class="badge bg-white-10 text-white px-3 py-2 rounded-pill mb-4 d-inline-block" style="border:1px solid rgba(255,255,255,.15);">
            <i class="bi bi-award-fill me-2"></i>Professional Web Services
        </div>
        <h1 class="display-4 fw-bold text-white mb-3">Let's Fix Your Website</h1>
        <p class="lead mb-5" style="color:rgba(255,255,255,.75);max-width:600px;margin:0 auto 2rem;">
            Our team will review your audit results and craft a personalised plan to resolve every issue - from quick wins to complete overhauls.
        </p>
        <a href="#get-in-touch" class="btn btn-primary btn-lg px-5 me-3 shadow">
            <i class="bi bi-send me-2"></i>Get in Touch
        </a>
        <a href="<?= url('audit') ?>" class="btn btn-outline-light btn-lg px-5">
            <i class="bi bi-search me-2"></i>Run Free Audit First
        </a>
    </div>
</section>

<section class="py-6 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill mb-3">What We Offer</div>
            <h2 class="display-6 fw-bold">Services Tailored to Your Needs</h2>
            <p class="text-muted lead mx-auto" style="max-width:600px;">Every website is different. We'll discuss your specific situation and provide a custom solution - no generic packages, no surprise bills.</p>
        </div>
        <div class="row g-4 mb-5">
            <?php
            $services = [
                [
                    'title' => 'Quick Fixes',
                    'icon' => 'bi-tools',
                    'color' => 'primary',
                    'desc' => 'Tackle the most impactful issues fast. We focus on the high-priority findings from your audit and implement fixes that move the needle quickly.',
                    'items' => ['Meta tags & SEO basics', 'Contact form setup', 'Mobile responsiveness', 'Image optimisation', 'Delivered rapidly'],
                ],
                [
                    'title' => 'Full Audit Fix',
                    'icon' => 'bi-stars',
                    'color' => 'purple',
                    'badge' => 'Most Requested',
                    'desc' => 'We address every single issue flagged in your audit report - SEO, accessibility, conversion, technical, and local business signals - leaving nothing behind.',
                    'items' => ['All audit issues resolved', 'Complete SEO overhaul', 'Accessibility improvements', 'Conversion optimisation', 'Social proof additions'],
                ],
                [
                    'title' => 'Ongoing Maintenance',
                    'icon' => 'bi-arrow-repeat',
                    'color' => 'success',
                    'desc' => 'Keep your website in top shape with monthly audits, continuous improvements, security monitoring, and priority support so nothing slips through the cracks.',
                    'items' => ['Monthly audit & fixes', 'Ongoing SEO maintenance', 'Security monitoring', 'Speed optimisation', 'Priority support'],
                ],
                [
                    'title' => 'Website Redesign',
                    'icon' => 'bi-palette',
                    'color' => 'warning',
                    'desc' => 'Sometimes a fresh start is the best move. We\'ll redesign your site from the ground up - modern design, optimised code, and built to convert.',
                    'items' => ['Modern, conversion-focused design', 'Mobile-first development', 'SEO baked in from day one', 'Fast load times', 'Handover & training'],
                ],
                [
                    'title' => 'SEO Growth',
                    'icon' => 'bi-graph-up-arrow',
                    'color' => 'info',
                    'desc' => 'Climb the search rankings with a data-driven SEO strategy built around your audit findings, your industry, and your target audience.',
                    'items' => ['Keyword research & strategy', 'On-page optimisation', 'Technical SEO fixes', 'Local SEO setup', 'Monthly reporting'],
                ],
                [
                    'title' => 'Accessibility Review',
                    'icon' => 'bi-universal-access',
                    'color' => 'danger',
                    'desc' => 'Ensure your website is usable by everyone. We\'ll fix WCAG compliance issues and implement best practices so every visitor can access your content.',
                    'items' => ['WCAG 2.1 AA compliance', 'Screen reader compatibility', 'Keyboard navigation fixes', 'Colour contrast corrections', 'Compliance documentation'],
                ],
            ];
            foreach ($services as $svc): ?>
            <div class="col-md-6 col-lg-4">
                <div class="service-offering-card h-100 position-relative <?= isset($svc['badge']) ? 'featured' : '' ?>">
                    <?php if (isset($svc['badge'])): ?>
                    <div class="service-badge"><?= e($svc['badge']) ?></div>
                    <?php endif; ?>
                    <div class="service-icon-wrap bg-<?= $svc['color'] ?>-soft text-<?= $svc['color'] ?> mb-3">
                        <i class="bi <?= $svc['icon'] ?> fs-4"></i>
                    </div>
                    <h4 class="fw-bold mb-2"><?= e($svc['title']) ?></h4>
                    <p class="text-muted small mb-3"><?= e($svc['desc']) ?></p>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($svc['items'] as $item): ?>
                        <li class="mb-2 small d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-<?= $svc['color'] ?>" style="flex-shrink:0;"></i>
                            <?= e($item) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-6 bg-white">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill mb-3">Why Work With Us</div>
                <h2 class="fw-bold display-6 mb-4">Real results, no fluff</h2>
                <div class="d-flex flex-column gap-4">
                    <?php
                    $whys = [
                        ['icon' => 'bi-clipboard-data', 'color' => 'primary', 'title' => 'Audit-Driven', 'text' => 'We start from your actual report. Every fix is based on real data, not guesswork.'],
                        ['icon' => 'bi-person-check', 'color' => 'success', 'title' => 'Transparent Communication', 'text' => 'You\'ll know exactly what we\'re doing and why. No jargon, no surprises.'],
                        ['icon' => 'bi-patch-check', 'color' => 'warning', 'title' => 'Measurable Results', 'text' => 'We re-run the audit after our work so you can see exactly how much your scores improved.'],
                        ['icon' => 'bi-headset', 'color' => 'info', 'title' => 'Ongoing Support', 'text' => 'We\'re here after the project is done. Questions? Changes? We\'ve got you covered.'],
                    ];
                    foreach ($whys as $w): ?>
                    <div class="d-flex gap-3">
                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3 bg-<?= $w['color'] ?>-soft text-<?= $w['color'] ?>" style="width:44px;height:44px;font-size:1.2rem;">
                            <i class="bi <?= $w['icon'] ?>"></i>
                        </div>
                        <div>
                            <div class="fw-semibold"><?= e($w['title']) ?></div>
                            <div class="text-muted small"><?= e($w['text']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="bg-light rounded-4 p-4 p-lg-5 text-center">
                    <div class="row g-4">
                        <?php
                        $proofStats = [
                            ['val' => '50+', 'label' => 'Audit checks per site'],
                            ['val' => '5', 'label' => 'Score categories analysed'],
                            ['val' => '100%', 'label' => 'Free to run an audit'],
                            ['val' => '24h', 'label' => 'Typical response time'],
                        ];
                        foreach ($proofStats as $ps): ?>
                        <div class="col-6">
                            <div class="fw-bold display-6 text-primary"><?= e($ps['val']) ?></div>
                            <div class="text-muted small"><?= e($ps['label']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-6 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill mb-3">How It Works</div>
            <h2 class="fw-bold display-6">From Audit to Action</h2>
            <p class="text-muted">A simple, transparent process from start to finish</p>
        </div>
        <div class="row g-4 justify-content-center">
            <?php
            $steps = [
                ['n' => '1', 'icon' => 'bi-search', 'title' => 'Run Your Free Audit', 'text' => 'Use our tool to generate a detailed report on your website\'s SEO, accessibility, conversion, and technical health.'],
                ['n' => '2', 'icon' => 'bi-chat-dots', 'title' => 'Get in Touch', 'text' => 'Share your report link with us (or just your URL) and tell us what you need. We\'ll review it and get back to you quickly.'],
                ['n' => '3', 'icon' => 'bi-file-earmark-text', 'title' => 'Receive a Custom Proposal', 'text' => 'We\'ll outline exactly what we\'ll fix, how long it will take, and what it will cost - no hidden fees.'],
                ['n' => '4', 'icon' => 'bi-rocket-takeoff', 'title' => 'We Get to Work', 'text' => 'Once you\'re happy, we implement all fixes, re-run the audit to prove the improvements, and hand everything back.'],
            ];
            foreach ($steps as $s): ?>
            <div class="col-md-6 col-lg-3 text-center">
                <div class="process-step-card h-100">
                    <div class="step-circle mb-3"><?= $s['n'] ?></div>
                    <i class="bi <?= $s['icon'] ?> fs-3 text-primary mb-2 d-block"></i>
                    <h5 class="fw-bold"><?= e($s['title']) ?></h5>
                    <p class="text-muted small mb-0"><?= e($s['text']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="get-in-touch" class="py-6 bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5">
                <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill mb-3">Get in Touch</div>
                <h2 class="fw-bold display-6 mb-3">Let's Talk About Your Website</h2>
                <p class="text-muted lead">Tell us about your situation and we'll come back to you with an honest assessment and a clear proposal - completely free, no obligation.</p>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <form action="<?= url('contact') ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="redirect_to" value="fix-my-website#get-in-touch">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Your Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="Jane Smith" value="<?= old('name') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" placeholder="jane@example.com" value="<?= old('email') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control" placeholder="+1 (555) 000-0000" value="<?= old('phone') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Your Website</label>
                                    <input type="url" name="website_url" class="form-control" placeholder="https://yoursite.com" value="<?= old('website_url') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Service You're Interested In</label>
                                    <select name="service_type" class="form-select">
                                        <option value="">Not sure yet - happy to discuss...</option>
                                        <?php foreach (['Quick Fixes', 'Full Audit Fix', 'Ongoing Maintenance', 'Website Redesign', 'SEO Growth', 'Accessibility Review'] as $service): ?>
                                        <option value="<?= e($service) ?>" <?= old('service_type') === $service ? 'selected' : '' ?>><?= e($service) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Tell Us About Your Situation <span class="text-danger">*</span></label>
                                    <textarea name="message" class="form-control" rows="5" placeholder="What's the main challenge with your website right now? Feel free to paste your audit report link if you have one." required><?= old('message') ?></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">
                                        <i class="bi bi-send me-2"></i>Send My Enquiry
                                    </button>
                                    <p class="text-muted small text-center mt-3 mb-0">
                                        <i class="bi bi-shield-check text-success me-1"></i>
                                        We'll get back to you within 24 hours. No spam, no pressure.
                                    </p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
