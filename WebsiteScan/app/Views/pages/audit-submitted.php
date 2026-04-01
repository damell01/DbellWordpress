<section class="py-6 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-4 p-md-5 text-center">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4"
                             style="width:72px;height:72px;background:rgba(37,99,235,0.12);color:#2563eb;">
                            <i class="bi bi-envelope-paper fs-2"></i>
                        </div>
                        <h1 class="display-6 fw-bold mb-3">Your Audit Is In Progress</h1>
                        <p class="lead text-muted mb-4">
                            We are scanning <strong><?= e($websiteUrl ?? '') ?></strong> now and will send the report to
                            <strong><?= e($email ?? '') ?></strong> as soon as it is ready.
                        </p>

                        <div class="row g-3 text-start mb-4">
                            <div class="col-md-4">
                                <div class="stat-highlight-card h-100">
                                    <div class="small text-uppercase text-muted fw-bold mb-2">Delivery</div>
                                    <div class="fw-semibold">By email</div>
                                    <div class="small text-muted">No need to wait on this page.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-highlight-card h-100">
                                    <div class="small text-uppercase text-muted fw-bold mb-2">What we check</div>
                                    <div class="fw-semibold">SEO, speed, UX</div>
                                    <div class="small text-muted">Plus accessibility, local, and conversion issues.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-highlight-card h-100">
                                    <div class="small text-uppercase text-muted fw-bold mb-2">Need help?</div>
                                    <div class="fw-semibold">We can fix it</div>
                                    <div class="small text-muted">Admins will still see this scan in the dashboard.</div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info text-start mb-4">
                            <div class="d-flex gap-2">
                                <i class="bi bi-info-circle-fill mt-1"></i>
                                <div>
                                    <strong>Next step:</strong> keep an eye on your inbox for the report link.
                                    Please also check spam or promotions if it does not arrive soon.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <a href="<?= url('/') ?>" class="btn btn-primary">
                                <i class="bi bi-house me-2"></i>Back To Home
                            </a>
                            <a href="<?= url('contact') ?>" class="btn btn-outline-primary">
                                <i class="bi bi-chat-dots me-2"></i>Talk To Us
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
