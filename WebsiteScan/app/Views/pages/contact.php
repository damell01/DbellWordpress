<?php $selectedService = old('service_type', $_GET['service'] ?? ''); ?>
<section class="py-6 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold">Contact Us</h1>
            <p class="lead text-muted">Ready to fix your website? We'd love to help.</p>
        </div>

        <?php $flashSuccess = get_flash('success'); $flashError = get_flash('error'); ?>
        <?php if ($flashSuccess): ?>
        <div class="alert alert-success text-center"><i class="bi bi-check-circle-fill me-2"></i><?= e($flashSuccess) ?></div>
        <?php endif; ?>
        <?php if ($flashError): ?>
        <div class="alert alert-danger"><?= e($flashError) ?></div>
        <?php endif; ?>

        <div class="row g-5 justify-content-center">
            <div class="col-lg-7">
                <div class="card border-0 shadow">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-4">Send Us a Message</h4>
                        <form action="<?= url('contact') ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="redirect_to" value="contact">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name"
                                           value="<?= old('name') ?>" required placeholder="Your full name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?= old('email') ?>" required placeholder="your@email.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="phone">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                           value="<?= old('phone') ?>" placeholder="(555) 123-4567">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="company">Company</label>
                                    <input type="text" class="form-control" id="company" name="company"
                                           value="<?= old('company') ?>" placeholder="Your Business Name">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="website_url">Website</label>
                                    <input type="url" class="form-control" id="website_url" name="website_url"
                                           value="<?= old('website_url') ?>" placeholder="https://yourbusiness.com">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="service_type">How can we help?</label>
                                    <select class="form-select" id="service_type" name="service_type">
                                        <option value="">Select a service...</option>
                                        <?php foreach ([
                                            'Website Fixes',
                                            'SEO Improvements',
                                            'Accessibility Review',
                                            'Website Redesign',
                                            'Conversion Optimization',
                                            'Monthly Support',
                                            'Other',
                                        ] as $service): ?>
                                        <option value="<?= e($service) ?>" <?= $selectedService === $service ? 'selected' : '' ?>><?= e($service) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="message">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="message" name="message" rows="5"
                                              required placeholder="Tell us about your project or questions..."><?= old('message') ?></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg w-100">
                                        <i class="bi bi-send me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="mb-4">
                    <h5 class="fw-bold mb-3">Why Work With Us?</h5>
                    <ul class="list-unstyled">
                        <li class="mb-3 d-flex gap-3">
                            <i class="bi bi-check-circle-fill text-success fs-5 flex-shrink-0"></i>
                            <span>We fix every issue identified in your audit report</span>
                        </li>
                        <li class="mb-3 d-flex gap-3">
                            <i class="bi bi-check-circle-fill text-success fs-5 flex-shrink-0"></i>
                            <span>Transparent pricing with no surprises</span>
                        </li>
                        <li class="mb-3 d-flex gap-3">
                            <i class="bi bi-check-circle-fill text-success fs-5 flex-shrink-0"></i>
                            <span>SEO, accessibility, and conversion expertise</span>
                        </li>
                        <li class="mb-3 d-flex gap-3">
                            <i class="bi bi-check-circle-fill text-success fs-5 flex-shrink-0"></i>
                            <span>Fast turnaround times</span>
                        </li>
                    </ul>
                </div>
                <div class="card border-0 bg-primary text-white">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Not sure what you need?</h5>
                        <p class="opacity-75 mb-3">Run a free website audit first and we'll know exactly what to fix.</p>
                        <a href="<?= url('audit') ?>" class="btn btn-light w-100">
                            <i class="bi bi-search me-2"></i>Get Free Audit First
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
