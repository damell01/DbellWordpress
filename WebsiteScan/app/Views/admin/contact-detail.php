<?php
$statusColors = [
    'new'      => 'primary',
    'read'     => 'info',
    'replied'  => 'success',
    'archived' => 'secondary',
];
$statusLabels = [
    'new'      => 'New',
    'read'     => 'Read',
    'replied'  => 'Replied',
    'archived' => 'Archived',
];
$cr = $contact ?? [];
$st = $cr['status'] ?? 'new';
?>

<!-- ── Breadcrumb back ────────────────────────── -->
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= url('admin/contacts') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
        <i class="bi bi-arrow-left me-1"></i>Back to Inbox
    </a>
    <span class="text-muted" style="font-size:.82rem;"><i class="bi bi-chevron-right"></i></span>
    <span class="fw-600" style="font-size:.88rem;color:#1e0a3c;"><?= e($cr['name'] ?? 'Contact') ?></span>
</div>

<div class="row g-4">
    <!-- ── Left: Contact Info + Message ─────────── -->
    <div class="col-lg-7">

        <!-- Contact Header Card -->
        <div class="admin-card mb-4">
            <div class="admin-card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-800 flex-shrink-0"
                         style="width:58px;height:58px;font-size:1.4rem;background:linear-gradient(135deg,#6222CC,#9333ea);box-shadow:0 4px 14px rgba(98,34,204,0.35);">
                        <?= strtoupper(substr($cr['name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <h5 class="mb-1 fw-bold" style="color:#1e0a3c;"><?= e($cr['name'] ?? '—') ?></h5>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <a href="mailto:<?= e($cr['email'] ?? '') ?>" class="text-decoration-none" style="color:#6222CC;font-size:.88rem;">
                                <i class="bi bi-envelope-fill me-1"></i><?= e($cr['email'] ?? '—') ?>
                            </a>
                            <?php if (!empty($cr['phone'])): ?>
                            <a href="tel:<?= e($cr['phone']) ?>" class="text-decoration-none" style="color:#10b981;font-size:.88rem;">
                                <i class="bi bi-telephone-fill me-1"></i><?= e($cr['phone']) ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="d-inline-flex align-items-center gap-1 px-3 py-2 rounded-pill"
                              style="background:rgba(98,34,204,0.1);font-size:.8rem;font-weight:700;color:#6222CC;">
                            <span class="status-dot <?= $st ?>"></span>
                            <?= $statusLabels[$st] ?? e($st) ?>
                        </span>
                    </div>
                </div>

                <!-- Meta info -->
                <div class="row g-2 mt-3">
                    <?php if (!empty($cr['company'])): ?>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background:#faf8ff;">
                            <i class="bi bi-building-fill text-primary" style="font-size:.9rem;"></i>
                            <div>
                                <div style="font-size:.67rem;color:#6b5b8f;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Company</div>
                                <div style="font-size:.82rem;font-weight:600;color:#1e0a3c;"><?= e($cr['company']) ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($cr['website_url'])): ?>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background:#faf8ff;">
                            <i class="bi bi-globe text-info" style="font-size:.9rem;"></i>
                            <div style="min-width:0;">
                                <div style="font-size:.67rem;color:#6b5b8f;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Website</div>
                                <a href="<?= e($cr['website_url']) ?>" target="_blank" rel="noopener"
                                   class="d-block text-truncate text-decoration-none" style="font-size:.82rem;font-weight:600;color:#06b6d4;max-width:180px;">
                                    <?= e(parse_url($cr['website_url'], PHP_URL_HOST) ?: $cr['website_url']) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($cr['service_type'])): ?>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background:#faf8ff;">
                            <i class="bi bi-stars" style="font-size:.9rem;color:#FBA504;"></i>
                            <div>
                                <div style="font-size:.67rem;color:#6b5b8f;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Interested In</div>
                                <div style="font-size:.82rem;font-weight:600;color:#1e0a3c;"><?= e($cr['service_type']) ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background:#faf8ff;">
                            <i class="bi bi-clock-fill text-muted" style="font-size:.9rem;"></i>
                            <div>
                                <div style="font-size:.67rem;color:#6b5b8f;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Submitted</div>
                                <div style="font-size:.82rem;font-weight:600;color:#1e0a3c;">
                                    <?= !empty($cr['created_at']) ? timeAgo($cr['created_at']) : '—' ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    $src = $cr['source'] ?? 'website';
                    $srcLabel = [
                        'contact_form'   => 'Contact Form',
                        'fix_my_website' => 'Fix My Website',
                        'website_scan'   => 'Website Scan',
                    ][$src] ?? ucfirst(str_replace('_',' ',$src));
                    ?>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background:#faf8ff;">
                            <i class="bi bi-funnel-fill text-warning" style="font-size:.9rem;"></i>
                            <div>
                                <div style="font-size:.67rem;color:#6b5b8f;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Source</div>
                                <div style="font-size:.82rem;font-weight:600;color:#1e0a3c;"><?= e($srcLabel) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message Card -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h6 class="fw-semibold mb-0"><i class="bi bi-chat-text-fill me-2 text-success"></i>Message</h6>
                <?php if (!empty($cr['email'])): ?>
                <a href="mailto:<?= e($cr['email'] ?? '') ?>?subject=<?= urlencode('Re: Your inquiry with DBell Creations') ?>"
                   class="btn btn-sm btn-primary" style="font-size:.75rem;padding:.3rem .65rem;">
                    <i class="bi bi-reply-fill me-1"></i>Reply
                </a>
                <?php endif; ?>
            </div>
            <div class="admin-card-body">
                <?php if (!empty($cr['message'])): ?>
                <div class="rounded-3 p-3 mb-3" style="background:#faf8ff;border:1px solid #e8e2f4;white-space:pre-wrap;font-size:.9rem;line-height:1.75;color:#1e0a3c;">
                    <?= e($cr['message']) ?>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="mailto:<?= e($cr['email'] ?? '') ?>?subject=<?= urlencode('Re: Your inquiry with DBell Creations') ?>"
                       class="btn btn-primary btn-sm">
                        <i class="bi bi-reply-fill me-1"></i>Reply via Email
                    </a>
                    <?php if (!empty($cr['phone'])): ?>
                    <a href="tel:<?= e($cr['phone']) ?>" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-telephone-fill me-1"></i>Call Now
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($cr['email'])): ?>
                    <a href="mailto:<?= e($cr['email']) ?>?subject=<?= urlencode('Quote from DBell Creations') ?>"
                       class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-file-earmark-text me-1"></i>Send Quote
                    </a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <p class="text-muted mb-0 fst-italic">No message content.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Admin Notes History -->
        <?php if (!empty($cr['notes'])): ?>
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h6 class="fw-semibold mb-0"><i class="bi bi-sticky-fill me-2 text-warning"></i>Admin Notes</h6>
            </div>
            <div class="admin-card-body">
                <div class="rounded-3 p-3" style="background:#fffbea;border:1px solid #fde68a;white-space:pre-wrap;font-size:.88rem;line-height:1.7;">
                    <?= e($cr['notes']) ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- ── Right: Actions + Status + Lead ─────── -->
    <div class="col-lg-5">

        <!-- Quick Actions -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h6 class="fw-semibold mb-0"><i class="bi bi-lightning-charge-fill me-2" style="color:#FBA504;"></i>Quick Actions</h6>
            </div>
            <div class="admin-card-body d-grid gap-2">
                <a href="mailto:<?= e($cr['email'] ?? '') ?>?subject=<?= urlencode('Following up on your inquiry — DBell Creations') ?>"
                   class="btn btn-primary">
                    <i class="bi bi-envelope-fill me-2"></i>Send Follow-Up Email
                </a>
                <?php if (!empty($cr['phone'])): ?>
                <a href="tel:<?= e($cr['phone']) ?>" class="btn btn-outline-success">
                    <i class="bi bi-telephone-fill me-2"></i>Call <?= e($cr['phone']) ?>
                </a>
                <?php endif; ?>
                <?php if (!empty($cr['website_url'])): ?>
                <a href="<?= url('audit') ?>?url=<?= urlencode($cr['website_url']) ?>" target="_blank"
                   class="btn btn-outline-info">
                    <i class="bi bi-search me-2"></i>Run Website Scan
                </a>
                <?php endif; ?>
                <?php if (!empty($cr['lead_id'])): ?>
                <a href="<?= url('admin/leads/' . (int)$cr['lead_id']) ?>"
                   class="btn btn-outline-primary">
                    <i class="bi bi-person-fill me-2"></i>View Full Lead Profile
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Update Status + Notes -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h6 class="fw-semibold mb-0"><i class="bi bi-pencil-square me-2 text-primary"></i>Update Status</h6>
            </div>
            <div class="admin-card-body">
                <form method="POST" action="<?= url('admin/contacts/' . (int)($cr['id'] ?? 0)) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label small fw-600" style="color:#6b5b8f;">Status</label>
                        <select name="status" class="form-select" style="border-color:#d1c4e9;">
                            <?php foreach ($statusLabels as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($cr['status'] ?? 'new') === $val ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-600" style="color:#6b5b8f;">Internal Notes</label>
                        <textarea name="notes" class="form-control" rows="4"
                                  style="border-color:#d1c4e9;resize:vertical;"
                                  placeholder="Add notes about this contact, next steps, etc…"><?= e($cr['notes'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle me-1"></i>Save Changes
                    </button>
                </form>
            </div>
        </div>

        <!-- Associated Lead Profile -->
        <?php if (!empty($cr['lead_id'])): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-semibold mb-0"><i class="bi bi-person-badge-fill me-2 text-success"></i>Lead Profile</h6>
                <a href="<?= url('admin/leads/' . (int)$cr['lead_id']) ?>"
                   class="btn btn-sm btn-outline-success" style="font-size:.72rem;padding:.2rem .5rem;">
                    Open
                </a>
            </div>
            <div class="admin-card-body p-0">
                <ul class="info-list px-3">
                    <li>
                        <span class="il-label">Lead Status</span>
                        <?php $ls = $cr['lead_status'] ?? 'new'; ?>
                        <span class="badge bg-light text-dark border" style="font-size:.72rem;"><?= e(ucfirst(str_replace('_',' ',$ls))) ?></span>
                    </li>
                    <?php if (!empty($cr['business_name'])): ?>
                    <li>
                        <span class="il-label">Business</span>
                        <span style="font-size:.84rem;font-weight:600;color:#1e0a3c;"><?= e($cr['business_name']) ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($cr['lead_website'])): ?>
                    <li>
                        <span class="il-label">Website</span>
                        <a href="<?= e($cr['lead_website']) ?>" target="_blank" class="text-truncate d-block text-info text-decoration-none" style="max-width:160px;font-size:.82rem;">
                            <?= e(parse_url($cr['lead_website'], PHP_URL_HOST) ?: $cr['lead_website']) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($cr['service_interest'])): ?>
                    <li>
                        <span class="il-label">Interest</span>
                        <span style="font-size:.84rem;color:#1e0a3c;"><?= e($cr['service_interest']) ?></span>
                    </li>
                    <?php endif; ?>
                    <li>
                        <span class="il-label">Follow-Up</span>
                        <div class="d-flex align-items-center gap-1">
                            <?php $stage = (int)($cr['follow_up_stage'] ?? 0); ?>
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                            <div style="width:16px;height:6px;border-radius:3px;background:<?= $i <= $stage ? '#6222CC' : '#e8e2f4' ?>;"></div>
                            <?php endfor; ?>
                            <span class="text-muted" style="font-size:.72rem;">Stage <?= $stage ?>/4</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
