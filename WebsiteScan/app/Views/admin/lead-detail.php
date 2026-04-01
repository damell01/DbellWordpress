<!-- Lead Detail -->
<div class="row g-4">
    <div class="col-lg-7">
        <div class="admin-card mb-4">
            <div class="admin-card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0">Lead Information</h6>
                <a href="<?= url('admin/leads') ?>" class="btn btn-sm btn-outline-secondary">← Back</a>
            </div>
            <div class="admin-card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted small">Name</dt>
                    <dd class="col-sm-8 small"><?= e($lead['contact_name'] ?: '—') ?></dd>
                    <dt class="col-sm-4 text-muted small">Email</dt>
                    <dd class="col-sm-8 small"><a href="mailto:<?= e($lead['email']) ?>"><?= e($lead['email'] ?: '—') ?></a></dd>
                    <dt class="col-sm-4 text-muted small">Phone</dt>
                    <dd class="col-sm-8 small"><a href="tel:<?= e($lead['phone']) ?>"><?= e($lead['phone'] ?: '—') ?></a></dd>
                    <dt class="col-sm-4 text-muted small">Business</dt>
                    <dd class="col-sm-8 small"><?= e($lead['business_name'] ?: '—') ?></dd>
                    <dt class="col-sm-4 text-muted small">Website</dt>
                    <dd class="col-sm-8 small"><a href="<?= e($lead['website_url']) ?>" target="_blank"><?= e($lead['website_url']) ?></a></dd>
                    <dt class="col-sm-4 text-muted small">Notes</dt>
                    <dd class="col-sm-8 small"><?= e($lead['notes'] ?: '—') ?></dd>
                    <dt class="col-sm-4 text-muted small">Source</dt>
                    <dd class="col-sm-8 small"><?= e($lead['source'] ?? 'audit') ?></dd>
                    <dt class="col-sm-4 text-muted small">Created</dt>
                    <dd class="col-sm-8 small"><?= e($lead['created_at']) ?></dd>
                </dl>
            </div>
        </div>

        <!-- Update Status -->
        <div class="admin-card mb-4">
            <div class="admin-card-header"><h6 class="fw-semibold mb-0">Update Status</h6></div>
            <div class="admin-card-body">
                <form method="POST" action="<?= url('admin/leads/' . $lead['id']) ?>" class="d-flex gap-2">
                    <?= csrf_field() ?>
                    <select name="status" class="form-select form-select-sm">
                        <?php
                        $statusOptions = [
                            'new'         => 'New',
                            'reviewed'    => 'Reviewed',
                            'contacted'   => 'Contacted',
                            'quote_sent'  => 'Quote Sent',
                            'closed_won'  => 'Closed Won',
                            'closed_lost' => 'Closed Lost',
                        ];
                        foreach ($statusOptions as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($lead['status'] ?? 'new') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Update</button>
                </form>
            </div>
        </div>

        <!-- Internal Notes -->
        <div class="admin-card">
            <div class="admin-card-header"><h6 class="fw-semibold mb-0">Internal Notes</h6></div>
            <div class="admin-card-body">
                <form method="POST" action="<?= url('admin/leads/' . $lead['id'] . '/note') ?>" class="mb-3">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <textarea name="note" class="form-control form-control-sm" rows="3" placeholder="Add an internal note..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-secondary btn-sm">Add Note</button>
                </form>
                <?php if (!empty($notes)): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($notes as $noteItem): ?>
                    <li class="list-group-item px-0">
                        <div class="small text-muted mb-1">
                            <?= e($noteItem['admin_name'] ?? 'Admin') ?> <span aria-hidden="true">&middot;</span> <?= timeAgo($noteItem['created_at']) ?>
                        </div>
                        <div class="small"><?= nl2br(e($noteItem['note'])) ?></div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p class="text-muted small mb-0">No notes yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Audit History -->
    <div class="col-lg-5">
        <div class="admin-card">
            <div class="admin-card-header"><h6 class="fw-semibold mb-0">Audit History</h6></div>
            <div class="admin-card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($audits ?? [] as $audit): ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-truncate me-2"><?= e($audit['website_url']) ?></span>
                            <?php if ($audit['overall_score'] !== null): ?>
                            <span class="badge <?= $audit['overall_score'] >= 70 ? 'bg-success' : ($audit['overall_score'] >= 50 ? 'bg-warning text-dark' : 'bg-danger') ?>"><?= $audit['overall_score'] ?></span>
                            <?php else: ?>
                            <span class="badge bg-secondary"><?= e($audit['status']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($audit['report_token'])): ?>
                        <a href="<?= url('report/' . $audit['report_token']) ?>" class="small text-primary" target="_blank">View Report →</a>
                        <?php endif; ?>
                        <div class="text-muted small"><?= timeAgo($audit['requested_at']) ?></div>
                    </li>
                    <?php endforeach; ?>
                    <?php if (empty($audits)): ?>
                    <li class="list-group-item text-muted small py-3">No audits found</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
