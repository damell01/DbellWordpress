<?php $all = $all ?? []; ?>
<div class="row g-4">
    <div class="col-lg-8">
        <form method="POST" action="<?= url('admin/settings') ?>">
            <?= csrf_field() ?>

            <!-- Branding -->
            <div class="admin-card mb-4">
                <div class="admin-card-header"><h6 class="fw-semibold mb-0"><i class="bi bi-palette me-2"></i>Branding</h6></div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Site Name</label>
                            <input type="text" name="site_name" class="form-control" value="<?= e($all['site_name'] ?? 'VerityScan') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control" value="<?= e($all['contact_email'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Homepage Hero -->
            <div class="admin-card mb-4">
                <div class="admin-card-header"><h6 class="fw-semibold mb-0"><i class="bi bi-house me-2"></i>Homepage Hero Text</h6></div>
                <div class="admin-card-body">
                    <div class="mb-3">
                        <label class="form-label">Hero Headline</label>
                        <input type="text" name="hero_headline" class="form-control" value="<?= e($all['hero_headline'] ?? 'Find Out What\'s Holding Your Website Back') ?>">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Hero Subheadline</label>
                        <textarea name="hero_subheadline" class="form-control" rows="2"><?= e($all['hero_subheadline'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- CTA Text -->
            <div class="admin-card mb-4">
                <div class="admin-card-header"><h6 class="fw-semibold mb-0"><i class="bi bi-megaphone me-2"></i>CTA Settings</h6></div>
                <div class="admin-card-body">
                    <div class="mb-3">
                        <label class="form-label">Main CTA Text</label>
                        <input type="text" name="cta_text" class="form-control" value="<?= e($all['cta_text'] ?? 'Need help fixing these issues? Contact us today.') ?>">
                    </div>
                    <div>
                        <label class="form-label">CTA Sub-text</label>
                        <input type="text" name="cta_subtext" class="form-control" value="<?= e($all['cta_subtext'] ?? 'We can improve your website and help you get more leads.') ?>">
                    </div>
                </div>
            </div>

            <!-- Rate Limiting -->
            <div class="admin-card mb-4">
                <div class="admin-card-header"><h6 class="fw-semibold mb-0"><i class="bi bi-shield me-2"></i>Rate Limiting</h6></div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Max Audits Per IP</label>
                            <input type="number" name="rate_limit_audits" class="form-control" value="<?= e($all['rate_limit_audits'] ?? '5') ?>" min="1" max="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Window (seconds)</label>
                            <input type="number" name="rate_limit_window" class="form-control" value="<?= e($all['rate_limit_window'] ?? '3600') ?>" min="60">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Options -->
            <div class="admin-card mb-4">
                <div class="admin-card-header"><h6 class="fw-semibold mb-0"><i class="bi bi-file-text me-2"></i>Report Options</h6></div>
                <div class="admin-card-body">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="requireEmail" name="require_email_for_report"
                               value="1" <?= !empty($all['require_email_for_report']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="requireEmail">Require email address to view full report</label>
                    </div>
                </div>
            </div>

            <div class="admin-card mb-4">
                <div class="admin-card-header"><h6 class="fw-semibold mb-0"><i class="bi bi-bug me-2"></i>Debug Mode</h6></div>
                <div class="admin-card-body">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="debugMode" name="debug_mode"
                               value="1" <?= !empty($all['debug_mode']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="debugMode">Show detailed application errors</label>
                    </div>
                    <div class="form-text mt-2">
                        Turn this on only while troubleshooting. When enabled, server errors will show full exception details in the browser.
                    </div>
                </div>
            </div>

            <!-- Screenshot Settings -->
            <div class="admin-card mb-4">
                <div class="admin-card-header"><h6 class="fw-semibold mb-0"><i class="bi bi-camera me-2"></i>Screenshot Settings</h6></div>
                <div class="admin-card-body">
                    <div class="mb-3">
                        <label class="form-label">Screenshot Provider</label>
                        <select name="screenshot_provider" class="form-select">
                            <option value="mshots" <?= ($all['screenshot_provider'] ?? 'mshots') === 'mshots' ? 'selected' : '' ?>>
                                WordPress mshots (free, no key required) – recommended
                            </option>
                            <option value="thum_io" <?= ($all['screenshot_provider'] ?? '') === 'thum_io' ? 'selected' : '' ?>>
                                thum.io (free tier, no key required)
                            </option>
                            <option value="custom" <?= ($all['screenshot_provider'] ?? '') === 'custom' ? 'selected' : '' ?>>
                                Custom API (enter template URL below)
                            </option>
                        </select>
                        <div class="form-text">Screenshots are embedded in audit reports and captured at scan time.</div>
                    </div>
                    <div class="mb-3" id="customApiUrlRow" style="display:<?= ($all['screenshot_provider'] ?? '') === 'custom' ? 'block' : 'none' ?>">
                        <label class="form-label">Custom API URL Template</label>
                        <input type="url" name="screenshot_api_url" class="form-control"
                               value="<?= e($all['screenshot_api_url'] ?? '') ?>"
                               placeholder="https://your-api.com/screenshot?url={url}">
                        <div class="form-text">Use <code>{url}</code> as a placeholder for the encoded website URL.</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="screenshotVerify"
                               name="screenshot_verify" value="1"
                               <?= !empty($all['screenshot_verify']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="screenshotVerify">
                            Verify screenshot is reachable before storing URL
                            <span class="text-muted small">(adds ~8 s to scan time)</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="admin-card mb-4">
                <div class="admin-card-header"><h6 class="fw-semibold mb-0"><i class="bi bi-plug me-2"></i>API Integrations</h6></div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="enableGooglePlaces"
                                       name="enable_google_places_lookup" value="1"
                                       <?= !isset($all['enable_google_places_lookup']) || !empty($all['enable_google_places_lookup']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="enableGooglePlaces">Enable Google Business Profile lookup</label>
                            </div>
                            <label class="form-label">Google Maps / Places API Key</label>
                            <input type="text" name="google_maps_api_key" class="form-control"
                                   value="<?= e($all['google_maps_api_key'] ?? '') ?>"
                                   placeholder="AIzaSy...">
                            <div class="form-text">Used to search for a matching Google Business Profile even if the website does not link to one.</div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="enablePageSpeed"
                                       name="enable_pagespeed_lookup" value="1"
                                       <?= !isset($all['enable_pagespeed_lookup']) || !empty($all['enable_pagespeed_lookup']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="enablePageSpeed">Enable Lighthouse / PageSpeed analysis</label>
                            </div>
                            <label class="form-label">Google PageSpeed API Key</label>
                            <input type="text" name="google_pagespeed_api_key" class="form-control"
                                   value="<?= e($all['google_pagespeed_api_key'] ?? '') ?>"
                                   placeholder="AIzaSy...">
                            <div class="form-text">Optional. If blank, the app falls back to the Maps key when possible.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-card mb-4">
                <div class="admin-card-header"><h6 class="fw-semibold mb-0"><i class="bi bi-envelope me-2"></i>Mail Settings</h6></div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Mail Driver</label>
                            <select name="mail_driver" class="form-select">
                                <option value="mail" <?= ($all['mail_driver'] ?? 'mail') === 'mail' ? 'selected' : '' ?>>PHP mail()</option>
                                <option value="smtp" <?= ($all['mail_driver'] ?? '') === 'smtp' ? 'selected' : '' ?>>SMTP</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Admin Notification Email</label>
                            <input type="email" name="admin_email" class="form-control" value="<?= e($all['admin_email'] ?? '') ?>" placeholder="owner@yourdomain.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">From Email</label>
                            <input type="email" name="mail_from" class="form-control" value="<?= e($all['mail_from'] ?? '') ?>" placeholder="yourgmail@gmail.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">From Name</label>
                            <input type="text" name="mail_from_name" class="form-control" value="<?= e($all['mail_from_name'] ?? 'VerityScan') ?>" placeholder="VerityScan">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SMTP Host</label>
                            <input type="text" name="smtp_host" class="form-control" value="<?= e($all['smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">SMTP Port</label>
                            <input type="number" name="smtp_port" class="form-control" value="<?= e($all['smtp_port'] ?? '587') ?>" placeholder="587">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Encryption</label>
                            <select name="smtp_encryption" class="form-select">
                                <option value="tls" <?= ($all['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                <option value="ssl" <?= ($all['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                <option value="" <?= ($all['smtp_encryption'] ?? '') === '' ? 'selected' : '' ?>>None</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SMTP Username</label>
                            <input type="text" name="smtp_user" class="form-control" value="<?= e($all['smtp_user'] ?? '') ?>" placeholder="yourgmail@gmail.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SMTP Password / App Password</label>
                            <input type="password" name="smtp_pass" class="form-control" value="<?= e($all['smtp_pass'] ?? '') ?>" placeholder="Google app password">
                        </div>
                    </div>
                    <div class="form-text mt-3">
                        For Gmail, use <code>smtp.gmail.com</code>, port <code>587</code>, encryption <code>TLS</code>, and a Google app password instead of your normal password.
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary px-5">
                <i class="bi bi-check-circle me-2"></i>Save Settings
            </button>
            <a href="<?= url('admin/schema-upgrade') ?>" class="btn btn-outline-secondary px-4 ms-2">
                <i class="bi bi-database-gear me-2"></i>Run Schema Upgrade
            </a>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-header"><h6 class="fw-semibold mb-0">Quick Links</h6></div>
            <div class="admin-card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><a href="<?= url('/') ?>" target="_blank" class="text-decoration-none small"><i class="bi bi-box-arrow-up-right me-2"></i>View Public Site</a></li>
                    <li class="mb-2"><a href="<?= url('admin/export/leads') ?>" class="text-decoration-none small"><i class="bi bi-download me-2"></i>Export All Leads</a></li>
                    <li><a href="<?= url('admin/export/scans') ?>" class="text-decoration-none small"><i class="bi bi-download me-2"></i>Export All Scans</a></li>
                </ul>
                <hr>
                <p class="small text-muted mb-2">Browser schema upgrade link:</p>
                <div class="small mb-3">
                    <a href="<?= url('admin/schema-upgrade') ?>" class="text-decoration-none"><?= e(url('admin/schema-upgrade')) ?></a>
                </div>
                <hr>
                <p class="small text-muted mb-2">If you prefer using environment variables, add these keys to the project root <code>.env</code> file:</p>
                <pre class="small bg-light border rounded p-3 mb-0"><code>GOOGLE_MAPS_API_KEY=your_google_maps_key
GOOGLE_PAGESPEED_API_KEY=your_pagespeed_key</code></pre>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var providerSelect = document.querySelector('select[name="screenshot_provider"]');
    var customRow = document.getElementById('customApiUrlRow');
    if (providerSelect && customRow) {
        providerSelect.addEventListener('change', function () {
            customRow.style.display = this.value === 'custom' ? 'block' : 'none';
        });
    }
}());
</script>
