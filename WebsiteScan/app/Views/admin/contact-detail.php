<?php
$statusColors = [
    'new'      => 'danger',
    'read'     => 'primary',
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
?>

<div class="row g-4">
    <!-- Left: Contact Info + Message -->
    <div class="col-lg-7">
        <div class="admin-card mb-4">
            <div class="admin-card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0"><i class="bi bi-person-fill me-2 text-primary"></i>Sender Details</h6>
                <a href="<?= url('admin/contacts') ?>" class="btn btn-sm btn-outline-secondary">← Back</a>
            </div>
            <div class="admin-card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                         style="width:54px;height:54px;font-size:1.3rem;">
                        <?= strtoupper(substr($cr['name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div>
                        <div class="fw-bold fs-5"><?= e($cr['name'] ?? '—') ?></div>
                        <a href="mailto:<?= e($cr['email'] ?? '') ?>" class="text-primary text-decoration-none">
                            <i class="bi bi-envelope me-1"></i><?= e($cr['email'] ?? '—') ?>
                        </a>
                    </div>
                    <div class="ms-auto">
                        <?php $st = $cr['status'] ?? 'new'; ?>
                        <span class="badge bg-<?= $statusColors[$st] ?? 'secondary' ?> fs-6 px-3 py-2">
                            <?= $statusLabels[$st] ?? e($st) ?>
                        </span>
                    </div>
                </div>

                <dl class="row mb-0">
                    <?php if (!empty($cr['phone'])): ?>
                    <dt class="col-sm-4 text-muted small">Phone</dt>
                    <dd class="col-sm-8 small">
                        <a href="tel:<?= e($cr['phone']) ?>" class="text-decoration-none">
                            <i class="bi bi-telephone me-1"></i><?= e($cr['phone']) ?>
                        </a>
                    </dd>
                    <?php endif; ?>

                    <?php if (!empty($cr['company'])): ?>
                    <dt class="col-sm-4 text-muted small">Company</dt>
                    <dd class="col-sm-8 small"><?= e($cr['company']) ?></dd>
                    <?php endif; ?>

                    <?php if (!empty($cr['website_url'])): ?>
                    <dt class="col-sm-4 text-muted small">Website</dt>
                    <dd class="col-sm-8 small">
                        <a href="<?= e($cr['website_url']) ?>" target="_blank" rel="noopener">
                            <i class="bi bi-box-arrow-up-right me-1"></i><?= e($cr['website_url']) ?>
                        </a>
                    </dd>
                    <?php endif; ?>

                    <?php if (!empty($cr['service_type'])): ?>
                    <dt class="col-sm-4 text-muted small">Service</dt>
                    <dd class="col-sm-8 small">
                        <span class="badge bg-light text-dark border"><?= e($cr['service_type']) ?></span>
                    </dd>
                    <?php endif; ?>

                    <dt class="col-sm-4 text-muted small">Source</dt>
                    <dd class="col-sm-8 small">
                        <?php $src = $cr['source'] ?? 'website'; ?>
                        <span class="badge bg-<?= $src === 'contact_form' ? 'warning text-dark' : ($src === 'fix_my_website' ? 'info' : 'secondary') ?>">
                            <?= e($src) ?>
                        </span>
                    </dd>

                    <dt class="col-sm-4 text-muted small">Submitted</dt>
                    <dd class="col-sm-8 small text-muted">
                        <?= e($cr['created_at'] ?? '—') ?>
                        <?php if (!empty($cr['created_at'])): ?>
                        <span class="text-muted ms-1">(<?= timeAgo($cr['created_at']) ?>)</span>
                        <?php endif; ?>
                    </dd>

                    <?php if (!empty($cr['lead_id'])): ?>
                    <dt class="col-sm-4 text-muted small">Lead</dt>
                    <dd class="col-sm-8 small">
                        <a href="<?= url('admin/leads/' . (int)$cr['lead_id']) ?>" class="btn btn-sm btn-outline-primary" style="font-size:.75rem;padding:.15rem .5rem;">
                            <i class="bi bi-person-fill me-1"></i>View Lead #<?= (int)$cr['lead_id'] ?>
                        </a>
                    </dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Message -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h6 class="fw-semibold mb-0"><i class="bi bi-chat-text-fill me-2 text-success"></i>Message</h6>
            </div>
            <div class="admin-card-body">
                <?php if (!empty($cr['message'])): ?>
                <div class="p-3 bg-light rounded" style="white-space:pre-wrap;font-size:.9rem;line-height:1.7;">
                    <?= e($cr['message']) ?>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <a href="mailto:<?= e($cr['email'] ?? '') ?>?subject=Re: <?= urlencode('Your inquiry with DBell Creations') ?>"
                       class="btn btn-primary btn-sm">
                        <i class="bi bi-reply-fill me-1"></i>Reply via Email
                    </a>
                    <?php if (!empty($cr['phone'])): ?>
                    <a href="tel:<?= e($cr['phone']) ?>" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-telephone-fill me-1"></i>Call
                    </a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <p class="text-muted mb-0">No message content.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Admin Notes -->
        <?php if (!empty($cr['notes'])): ?>
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h6 class="fw-semibold mb-0"><i class="bi bi-sticky-fill me-2 text-warning"></i>Admin Notes</h6>
            </div>
            <div class="admin-card-body">
                <div class="p-3 bg-warning-subtle rounded" style="white-space:pre-wrap;font-size:.9rem;">
                    <?= e($cr['notes']) ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right: Update Status + Lead Info -->
    <div class="col-lg-5">
        <!-- Update Status -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h6 class="fw-semibold mb-0"><i class="bi bi-pencil-fill me-2 text-warning"></i>Update Status</h6>
            </div>
            <div class="admin-card-body">
                <form method="POST" action="<?= url('admin/contacts/' . (int)($cr['id'] ?? 0)) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach ($statusLabels as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($cr['status'] ?? 'new') === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Internal Notes</label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Add internal notes about this contact…"><?= e($cr['notes'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle me-1"></i>Save Changes
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h6 class="fw-semibold mb-0"><i class="bi bi-lightning-fill me-2 text-warning"></i>Quick Actions</h6>
            </div>
            <div class="admin-card-body d-grid gap-2">
                <a href="mailto:<?= e($cr['email'] ?? '') ?>?subject=<?= urlencode('Following up on your inquiry') ?>"
                   class="btn btn-outline-primary">
                    <i class="bi bi-envelope-fill me-2"></i>Send Follow-Up Email
                </a>
                <?php if (!empty($cr['website_url'])): ?>
                <a href="<?= url('audit') ?>?url=<?= urlencode($cr['website_url']) ?>" target="_blank"
                   class="btn btn-outline-info">
                    <i class="bi bi-search me-2"></i>Run Website Scan
                </a>
                <?php endif; ?>
                <?php if (!empty($cr['lead_id'])): ?>
                <a href="<?= url('admin/leads/' . (int)$cr['lead_id']) ?>"
                   class="btn btn-outline-success">
                    <i class="bi bi-person-fill me-2"></i>View Full Lead Profile
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Associated Lead Info -->
        <?php if (!empty($cr['lead_id'])): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-semibold mb-0"><i class="bi bi-people-fill me-2 text-success"></i>Lead Profile</h6>
            </div>
            <div class="admin-card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Lead Status</dt>
                    <dd class="col-7">
                        <?php $ls = $cr['lead_status'] ?? 'new'; ?>
                        <span class="badge bg-secondary"><?= e($ls) ?></span>
                    </dd>
                    <?php if (!empty($cr['business_name'])): ?>
                    <dt class="col-5 text-muted">Business</dt>
                    <dd class="col-7"><?= e($cr['business_name']) ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($cr['lead_website'])): ?>
                    <dt class="col-5 text-muted">Website</dt>
                    <dd class="col-7">
                        <a href="<?= e($cr['lead_website']) ?>" target="_blank" class="text-truncate d-block" style="max-width:160px;">
                            <?= e(parse_url($cr['lead_website'], PHP_URL_HOST) ?: $cr['lead_website']) ?>
                        </a>
                    </dd>
                    <?php endif; ?>
                    <?php if (!empty($cr['service_interest'])): ?>
                    <dt class="col-5 text-muted">Interest</dt>
                    <dd class="col-7"><?= e($cr['service_interest']) ?></dd>
                    <?php endif; ?>
                    <dt class="col-5 text-muted">Follow-Up</dt>
                    <dd class="col-7">Stage <?= (int)($cr['follow_up_stage'] ?? 0) ?> / 4</dd>
                </dl>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
