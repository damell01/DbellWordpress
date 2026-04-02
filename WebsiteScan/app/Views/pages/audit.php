<!-- Audit Form Page -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill mb-3">
                <i class="bi bi-lightning-charge-fill me-1"></i>Free Website Audit
            </div>
            <h1 class="display-5 fw-bold">Audit Your Website for Free</h1>
            <p class="lead text-muted">View the report right away in your browser. Add your email and we'll send your report there too.</p>
        </div>

        <?php $errors = get_flash('errors') ?? []; ?>
        <?php $flashError = get_flash('error'); ?>
        <?php if ($flashError): ?>
        <div class="alert alert-danger"><?= e($flashError) ?></div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        <form action="<?= url('audit') ?>" method="POST" id="auditForm" class="audit-submit-form" data-loading-message="Scanning your site and building your report..." onsubmit="return window.SiteScopeHandleAuditSubmit ? window.SiteScopeHandleAuditSubmit(event, this) : true;">
                            <?= csrf_field() ?>

                            <div class="mb-4">
                                <label for="website_url" class="form-label fw-semibold fs-5">
                                    <i class="bi bi-globe2 text-primary me-2"></i>Website URL <span class="text-danger">*</span>
                                </label>
                                <input type="url" class="form-control form-control-lg <?= isset($errors['website_url']) ? 'is-invalid' : '' ?>"
                                       id="website_url" name="website_url"
                                       placeholder="https://yourbusiness.com"
                                       value="<?= old('website_url') ?>"
                                       required aria-label="Website URL to audit">
                                <?php if (isset($errors['website_url'])): ?>
                                <div class="invalid-feedback d-block"><?= e($errors['website_url'][0]) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold fs-5">
                                    <i class="bi bi-envelope text-primary me-2"></i>Email Address <span class="text-muted">(optional)</span>
                                </label>
                                <div class="input-group input-group-lg">
                                    <input type="email" class="form-control"
                                           id="email" name="email"
                                           placeholder="jane@yourbusiness.com"
                                           value="<?= old('email') ?>"
                                           aria-label="Email address for the audit report">
                                    <button class="btn btn-primary" type="submit" id="submitBtn">
                                        <span id="btnText"><i class="bi bi-search me-2"></i>Run Free Audit</span>
                                        <span id="btnLoading" class="d-none">
                                            <span class="spinner-border spinner-border-sm me-2"></span>Scanning...
                                        </span>
                                    </button>
                                </div>
                                <div class="form-text">If you enter your email, the finished report will be sent there automatically after the scan completes.</div>
                            </div>

                            <div class="optional-info-toggle mb-3">
                                <button type="button" class="btn btn-link text-decoration-none p-0 text-muted" data-bs-toggle="collapse" data-bs-target="#optionalInfo">
                                    <i class="bi bi-plus-circle me-1"></i>Add your name, phone, and business details (optional)
                                </button>
                            </div>

                            <div class="collapse" id="optionalInfo">
                                <div class="bg-light rounded p-4 mb-3">
                                    <h6 class="fw-semibold mb-3 text-muted">
                                        <i class="bi bi-person me-2"></i>Optional Contact Details
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="contact_name">Your Name (optional)</label>
                                            <input type="text" class="form-control" id="contact_name" name="contact_name"
                                                   placeholder="Jane Smith" value="<?= old('contact_name') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="business_name">Business Name (optional)</label>
                                            <input type="text" class="form-control" id="business_name" name="business_name"
                                                   placeholder="Your Business LLC" value="<?= old('business_name') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="phone">Phone Number (optional)</label>
                                            <input type="tel" class="form-control" id="phone" name="phone"
                                                   placeholder="(555) 123-4567" value="<?= old('phone') ?>">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="notes">What do you need help with? (optional)</label>
                                            <textarea class="form-control" id="notes" name="notes" rows="2"
                                                      placeholder="SEO improvements, accessibility fixes, full redesign..."><?= old('notes') ?></textarea>
                                        </div>
                                    </div>
                                    <p class="text-muted small mt-2 mb-0"><i class="bi bi-shield-check me-1 text-success"></i>The report still opens in your browser right away, and admins can always see completed scans in the dashboard.</p>
                                </div>
                            </div>

                            <div class="text-muted small">
                                <i class="bi bi-shield-lock me-1 text-success"></i>By submitting, you agree to our
                                <a href="<?= url('terms') ?>">Terms</a> and <a href="<?= url('privacy') ?>">Privacy Policy</a>.
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center mt-5">
            <div class="col-lg-8">
                <h5 class="fw-bold text-center mb-4">What We Check</h5>
                <div class="row g-3 text-center">
                    <?php
                    $checks = [
                        ['icon' => 'bi-search', 'label' => 'SEO'],
                        ['icon' => 'bi-universal-access', 'label' => 'Accessibility'],
                        ['icon' => 'bi-graph-up', 'label' => 'Conversion'],
                        ['icon' => 'bi-lightning', 'label' => 'Performance'],
                        ['icon' => 'bi-geo-alt', 'label' => 'Local Business'],
                    ];
                    foreach ($checks as $c): ?>
                    <div class="col-4 col-md">
                        <div class="check-pill">
                            <i class="bi <?= $c['icon'] ?> fs-5 mb-1 d-block"></i>
                            <span class="small"><?= e($c['label']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
