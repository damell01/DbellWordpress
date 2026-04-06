<?php
$statusColors = [
    'new'         => ['bg' => 'primary',   'label' => 'New',        'hex' => '#6222CC'],
    'reviewed'    => ['bg' => 'info',      'label' => 'Reviewed',   'hex' => '#06b6d4'],
    'contacted'   => ['bg' => 'warning',   'label' => 'Contacted',  'hex' => '#FBA504'],
    'quote_sent'  => ['bg' => 'warning',   'label' => 'Quote Sent', 'hex' => '#f59e0b'],
    'closed_won'  => ['bg' => 'success',   'label' => 'Won',        'hex' => '#10b981'],
    'closed_lost' => ['bg' => 'secondary', 'label' => 'Lost',       'hex' => '#94a3b8'],
];
$srcColors = ['audit_form' => 'primary', 'report_help' => 'success', 'contact_form' => 'warning', 'manual' => 'info'];
$srcLabels = ['audit_form' => 'Audit', 'report_help' => 'Report CTA', 'contact_form' => 'Contact', 'manual' => 'Manual'];
?>

<!-- ── Toolbar ────────────────────────────────── -->
<div class="d-flex flex-wrap gap-2 align-items-center mb-4 justify-content-between">
    <form class="d-flex flex-wrap gap-2 align-items-center flex-grow-1" method="GET">
        <div class="input-group input-group-sm" style="max-width:220px;">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" name="search" class="form-control border-start-0 ps-0"
                   placeholder="Search leads…" value="<?= e($search ?? '') ?>">
        </div>
        <select name="status" class="form-select form-select-sm" style="max-width:160px;">
            <option value="">All Status</option>
            <?php foreach ($statusColors as $val => $sc): ?>
            <option value="<?= $val ?>" <?= ($status ?? '') === $val ? 'selected' : '' ?>><?= $sc['label'] ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm px-3">
            <i class="bi bi-funnel me-1"></i>Filter
        </button>
        <?php if (!empty($search) || !empty($status)): ?>
        <a href="<?= url('admin/leads') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-x-lg me-1"></i>Clear
        </a>
        <?php endif; ?>
    </form>
    <a href="<?= url('admin/export/leads') ?>" class="btn btn-outline-success btn-sm px-3">
        <i class="bi bi-download me-1"></i><span class="d-none d-sm-inline">Export CSV</span>
    </a>
</div>

<p class="text-muted small mb-3">
    <i class="bi bi-people me-1"></i>
    Showing <strong><?= count($leadsData['items'] ?? []) ?></strong> of <strong><?= number_format($leadsData['total'] ?? 0) ?></strong> total leads
    <?php if (!empty($search)): ?> — "<strong><?= e($search) ?></strong>"<?php endif; ?>
    <?php if (!empty($status)): ?> — Status: <strong><?= $statusColors[$status]['label'] ?? e($status) ?></strong><?php endif; ?>
</p>

<!-- ── Desktop Table ─────────────────────────── -->
<div class="admin-card crm-mobile-hide">
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width:36px;">#</th>
                        <th>Contact</th>
                        <th>Business</th>
                        <th>Website</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Follow-Up</th>
                        <th>Date</th>
                        <th class="pe-3" style="width:50px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leadsData['items'] ?? [] as $lead):
                        $isAnonymous = empty($lead['contact_name']) && empty($lead['email']);
                        $initials = $isAnonymous ? '?' : strtoupper(substr($lead['contact_name'] ?: $lead['email'], 0, 1));
                        $sc = $statusColors[$lead['status'] ?? 'new'] ?? ['bg'=>'secondary','label'=>ucfirst($lead['status'] ?? ''),'hex'=>'#94a3b8'];
                    ?>
                    <tr>
                        <td class="ps-3 text-muted" style="font-size:.72rem;"><?= $lead['id'] ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="lead-avatar"><?= $initials ?></div>
                                <div>
                                    <div class="fw-600" style="font-size:.85rem;color:#1e0a3c;">
                                        <?= e($lead['contact_name'] ?: ($isAnonymous ? 'Anonymous' : '-')) ?>
                                    </div>
                                    <div class="text-muted" style="font-size:.76rem;">
                                        <?= $lead['email'] ? e($lead['email']) : '<span class="fst-italic">No email</span>' ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:.84rem;color:#1e0a3c;"><?= e($lead['business_name'] ?: '—') ?></td>
                        <td>
                            <a href="<?= e($lead['website_url']) ?>" target="_blank" rel="noopener"
                               class="text-decoration-none text-muted text-truncate-200 d-block"
                               style="font-size:.8rem;" title="<?= e($lead['website_url']) ?>">
                                <?= e($lead['website_url'] ? (parse_url($lead['website_url'], PHP_URL_HOST) ?: $lead['website_url']) : '—') ?>
                            </a>
                        </td>
                        <td>
                            <?php $src = $lead['source'] ?? 'audit'; $srcColor = $srcColors[$src] ?? 'secondary'; $srcLabel = $srcLabels[$src] ?? ucfirst($src); ?>
                            <span class="badge bg-<?= $srcColor ?>-soft text-<?= $srcColor ?>" style="font-size:.68rem;"><?= e($srcLabel) ?></span>
                        </td>
                        <td>
                            <span class="d-inline-flex align-items-center gap-1">
                                <span style="width:8px;height:8px;border-radius:50%;background:<?= $sc['hex'] ?>;flex-shrink:0;"></span>
                                <span class="badge bg-<?= $sc['bg'] ?>" style="font-size:.72rem;"><?= $sc['label'] ?></span>
                            </span>
                        </td>
                        <td>
                            <?php $fStage = (int)($lead['follow_up_stage'] ?? 0); ?>
                            <div class="d-flex gap-1 align-items-center">
                                <?php for ($i = 1; $i <= 4; $i++): ?>
                                <div style="width:14px;height:5px;border-radius:3px;background:<?= $i <= $fStage ? '#6222CC' : '#e8e2f4' ?>;"></div>
                                <?php endfor; ?>
                            </div>
                        </td>
                        <td class="text-muted" style="font-size:.77rem;white-space:nowrap;"><?= timeAgo($lead['created_at']) ?></td>
                        <td class="pe-3">
                            <a href="<?= url('admin/leads/' . $lead['id']) ?>" class="btn-action" title="View lead">
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($leadsData['items'])): ?>
                    <tr>
                        <td colspan="9">
                            <div class="empty-state py-5">
                                <i class="bi bi-people"></i>
                                <p>No leads found.
                                    <?php if (!empty($search) || !empty($status)): ?>
                                    <a href="<?= url('admin/leads') ?>">Clear filters</a>
                                    <?php else: ?>
                                    Leads appear here when website audits are run.
                                    <?php endif; ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── Mobile Card List ───────────────────────── -->
<div class="crm-card-list">
    <?php foreach ($leadsData['items'] ?? [] as $lead):
        $isAnonymous = empty($lead['contact_name']) && empty($lead['email']);
        $initials = $isAnonymous ? '?' : strtoupper(substr($lead['contact_name'] ?: $lead['email'], 0, 1));
        $sc = $statusColors[$lead['status'] ?? 'new'] ?? ['bg'=>'secondary','label'=>ucfirst($lead['status'] ?? ''),'hex'=>'#94a3b8'];
        $fStage = (int)($lead['follow_up_stage'] ?? 0);
    ?>
    <a href="<?= url('admin/leads/' . $lead['id']) ?>" class="text-decoration-none">
        <div class="crm-card-item">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="lead-avatar"><?= $initials ?></div>
                    <div>
                        <div class="fw-600" style="color:#1e0a3c;font-size:.9rem;">
                            <?= e($lead['contact_name'] ?: ($isAnonymous ? 'Anonymous' : '-')) ?>
                        </div>
                        <div class="text-muted" style="font-size:.75rem;"><?= e($lead['email'] ?: '—') ?></div>
                    </div>
                </div>
                <span class="badge bg-<?= $sc['bg'] ?>" style="font-size:.7rem;"><?= $sc['label'] ?></span>
            </div>
            <?php if (!empty($lead['business_name'])): ?>
            <div class="text-muted mb-1" style="font-size:.8rem;"><i class="bi bi-building me-1"></i><?= e($lead['business_name']) ?></div>
            <?php endif; ?>
            <?php if (!empty($lead['website_url'])): ?>
            <div class="text-muted mb-2" style="font-size:.78rem;"><i class="bi bi-globe me-1"></i><?= e(parse_url($lead['website_url'], PHP_URL_HOST) ?: $lead['website_url']) ?></div>
            <?php endif; ?>
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex gap-1">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                    <div style="width:18px;height:5px;border-radius:3px;background:<?= $i <= $fStage ? '#6222CC' : '#e8e2f4' ?>;"></div>
                    <?php endfor; ?>
                    <span class="text-muted" style="font-size:.7rem;margin-left:4px;">Stage <?= $fStage ?>/4</span>
                </div>
                <span class="text-muted" style="font-size:.72rem;"><?= timeAgo($lead['created_at']) ?></span>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
    <?php if (empty($leadsData['items'])): ?>
    <div class="empty-state py-5">
        <i class="bi bi-people"></i>
        <p>No leads found.</p>
    </div>
    <?php endif; ?>
</div>

<!-- ── Pagination ────────────────────────────── -->
<?php if (($leadsData['last_page'] ?? 1) > 1): ?>
<nav class="mt-3" aria-label="Leads pagination">
    <ul class="pagination pagination-sm justify-content-center flex-wrap gap-1">
        <?php for ($p = 1; $p <= $leadsData['last_page']; $p++): ?>
        <li class="page-item <?= $p === ($leadsData['current_page'] ?? 1) ? 'active' : '' ?>">
            <a class="page-link rounded-2" href="?page=<?= $p ?>&search=<?= urlencode($search ?? '') ?>&status=<?= urlencode($status ?? '') ?>">
                <?= $p ?>
            </a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
