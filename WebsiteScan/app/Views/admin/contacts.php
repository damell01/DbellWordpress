<?php
$statusCounts = $statusCounts ?? ['new' => 0, 'read' => 0, 'replied' => 0, 'archived' => 0];
$totalContacts = array_sum($statusCounts);

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
$statusIcons = [
    'new'      => 'bi-bell-fill',
    'read'     => 'bi-eye-fill',
    'replied'  => 'bi-check-circle-fill',
    'archived' => 'bi-archive-fill',
];
$sourceIcons = [
    'contact_form'   => 'bi-envelope-fill',
    'fix_my_website' => 'bi-wrench-adjustable-fill',
    'website_scan'   => 'bi-search',
];
$currentStatus = $status ?? '';
$currentSearch = $search ?? '';
?>

<!-- ── Status Filter Cards ─────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-6 col-sm-3">
        <a href="<?= url('admin/contacts') ?>" class="text-decoration-none">
            <div class="stat-card <?= $currentStatus === '' && $currentSearch === '' ? 'border-2' : '' ?>"
                 style="<?= $currentStatus === '' && $currentSearch === '' ? 'border-color:#6222CC!important;' : '' ?>">
                <div class="stat-card-icon bg-info-soft text-info"><i class="bi bi-envelope-fill"></i></div>
                <div>
                    <div class="stat-card-value"><?= number_format($totalContacts) ?></div>
                    <div class="stat-card-label">All Messages</div>
                </div>
            </div>
        </a>
    </div>
    <?php foreach (['new', 'read', 'replied', 'archived'] as $s): ?>
    <div class="col-6 col-sm-3">
        <a href="<?= url('admin/contacts') ?>?status=<?= $s ?>" class="text-decoration-none">
            <div class="stat-card <?= $currentStatus === $s ? 'border-2' : '' ?>"
                 style="<?= $currentStatus === $s ? 'border-color:var(--crm-primary)!important;' : '' ?>">
                <div class="stat-card-icon bg-<?= $statusColors[$s] ?>-soft text-<?= $statusColors[$s] ?>">
                    <i class="bi <?= $statusIcons[$s] ?>"></i>
                </div>
                <div>
                    <div class="stat-card-value"><?= number_format($statusCounts[$s]) ?></div>
                    <div class="stat-card-label"><?= $statusLabels[$s] ?></div>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Toolbar ────────────────────────────────── -->
<div class="admin-card mb-4">
    <div class="admin-card-body py-3">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <form method="GET" action="<?= url('admin/contacts') ?>" class="d-flex gap-2 flex-wrap flex-grow-1" style="min-width:0;">
                <?php if ($currentStatus): ?>
                <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
                <?php endif; ?>
                <div class="input-group" style="max-width:340px;">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="search" name="search" class="form-control border-start-0 ps-0"
                           placeholder="Search name, email, message…"
                           value="<?= e($currentSearch) ?>">
                </div>
                <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Search</button>
                <?php if ($currentSearch || $currentStatus): ?>
                <a href="<?= url('admin/contacts') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i>Clear
                </a>
                <?php endif; ?>
            </form>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-funnel me-1"></i><span class="d-none d-sm-inline">Filter</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item <?= $currentStatus === '' ? 'active' : '' ?>"
                               href="<?= url('admin/contacts') ?>">All Messages</a></li>
                        <?php foreach (['new', 'read', 'replied', 'archived'] as $s): ?>
                        <li>
                            <a class="dropdown-item <?= $currentStatus === $s ? 'active' : '' ?>"
                               href="<?= url('admin/contacts') ?>?status=<?= $s ?><?= $currentSearch ? '&search='.urlencode($currentSearch) : '' ?>">
                                <span class="status-dot <?= $s ?> me-2" style="vertical-align:middle;"></span>
                                <?= $statusLabels[$s] ?> <span class="text-muted ms-1">(<?= $statusCounts[$s] ?>)</span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <a href="<?= url('admin/export/contacts') ?><?= $currentStatus ? '?status='.urlencode($currentStatus) : '' ?>"
                   class="btn btn-outline-success btn-sm">
                    <i class="bi bi-download me-1"></i><span class="d-none d-sm-inline">Export</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ── Desktop Table ──────────────────────────── -->
<div class="admin-card crm-mobile-hide">
    <div class="admin-card-header">
        <h6 class="fw-semibold mb-0">
            <i class="bi bi-envelope-fill me-2 text-primary"></i>Contact Submissions
            <?php if ($currentSearch): ?>
            <span class="text-muted fw-normal small ms-2">— "<?= e($currentSearch) ?>"</span>
            <?php endif; ?>
        </h6>
        <span class="badge bg-light text-dark border"><?= number_format($contactsData['total'] ?? 0) ?> total</span>
    </div>
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:.84rem;">
                <thead>
                    <tr>
                        <th class="ps-3">Sender</th>
                        <th>Service</th>
                        <th>Message Preview</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="pe-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contactsData['items'] ?? [] as $cr): ?>
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="lead-avatar">
                                    <?= strtoupper(substr($cr['name'] ?? 'U', 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="fw-600" style="color:#1e0a3c;">
                                        <?= e($cr['name'] ?? '—') ?>
                                        <?php if (($cr['status'] ?? 'new') === 'new'): ?>
                                        <span class="status-dot new ms-1" style="vertical-align:middle;"></span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="mailto:<?= e($cr['email'] ?? '') ?>" class="text-muted small text-decoration-none">
                                        <?= e($cr['email'] ?? '—') ?>
                                    </a>
                                    <?php if (!empty($cr['phone'])): ?>
                                    <div class="text-muted small"><?= e($cr['phone']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($cr['service_type'])): ?>
                            <span class="badge bg-light text-dark border" style="font-size:.7rem;"><?= e($cr['service_type']) ?></span>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="max-width:240px;">
                            <span class="text-truncate d-block" style="max-width:220px;font-size:.82rem;" title="<?= e($cr['message'] ?? '') ?>">
                                <?= e(mb_strimwidth($cr['message'] ?? '', 0, 75, '…')) ?>
                            </span>
                        </td>
                        <td>
                            <?php $src = $cr['source'] ?? 'website';
                            $srcIcon = $sourceIcons[$src] ?? 'bi-globe'; ?>
                            <span class="badge bg-light text-dark border" style="font-size:.7rem;">
                                <i class="bi <?= $srcIcon ?> me-1"></i><?= e($src) ?>
                            </span>
                        </td>
                        <td>
                            <?php $st = $cr['status'] ?? 'new'; ?>
                            <span class="d-inline-flex align-items-center gap-1">
                                <span class="status-dot <?= $st ?>"></span>
                                <span style="font-size:.78rem;font-weight:600;color:#1e0a3c;"><?= $statusLabels[$st] ?? e($st) ?></span>
                            </span>
                        </td>
                        <td class="text-muted" style="font-size:.78rem;"><?= timeAgo($cr['created_at']) ?></td>
                        <td class="pe-3">
                            <a href="<?= url('admin/contacts/' . $cr['id']) ?>"
                               class="btn btn-sm btn-outline-primary btn-action" style="width:auto;padding:.25rem .6rem;">
                                <i class="bi bi-eye me-1"></i>View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($contactsData['items'])): ?>
                    <tr>
                        <td colspan="7" class="py-5">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <p><?= $currentSearch ? 'No contacts match your search.' : 'No contact requests yet.' ?></p>
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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="fw-600 text-muted small">
            <?= number_format($contactsData['total'] ?? 0) ?> message<?= ($contactsData['total'] ?? 0) !== 1 ? 's' : '' ?>
        </span>
    </div>
    <?php foreach ($contactsData['items'] ?? [] as $cr):
        $isNew = ($cr['status'] ?? 'new') === 'new';
        $st = $cr['status'] ?? 'new';
        $src = $cr['source'] ?? 'website';
        $srcIcon = $sourceIcons[$src] ?? 'bi-globe';
    ?>
    <a href="<?= url('admin/contacts/' . $cr['id']) ?>" class="text-decoration-none">
        <div class="crm-card-item" style="<?= $isNew ? 'border-left:3px solid #FBA504;' : '' ?>">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="lead-avatar"><?= strtoupper(substr($cr['name'] ?? 'U', 0, 1)) ?></div>
                    <div>
                        <div class="fw-600" style="color:#1e0a3c;font-size:.9rem;">
                            <?= e($cr['name'] ?? '—') ?>
                            <?php if ($isNew): ?>
                            <span class="badge ms-1" style="background:#FBA504;font-size:.6rem;">New</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted" style="font-size:.75rem;"><?= e($cr['email'] ?? '') ?></div>
                    </div>
                </div>
                <div class="text-muted" style="font-size:.72rem;flex-shrink:0;"><?= timeAgo($cr['created_at']) ?></div>
            </div>
            <div class="text-muted mb-2" style="font-size:.82rem;line-height:1.5;">
                <?= e(mb_strimwidth($cr['message'] ?? '', 0, 100, '…')) ?>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <?php if (!empty($cr['service_type'])): ?>
                <span class="badge bg-light text-dark border" style="font-size:.68rem;"><?= e($cr['service_type']) ?></span>
                <?php endif; ?>
                <span class="badge bg-light text-dark border" style="font-size:.68rem;">
                    <i class="bi <?= $srcIcon ?> me-1"></i><?= e($src) ?>
                </span>
                <span class="d-inline-flex align-items-center gap-1">
                    <span class="status-dot <?= $st ?>"></span>
                    <span style="font-size:.72rem;font-weight:600;color:#6b5b8f;"><?= $statusLabels[$st] ?? e($st) ?></span>
                </span>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
    <?php if (empty($contactsData['items'])): ?>
    <div class="empty-state py-5">
        <i class="bi bi-inbox"></i>
        <p><?= $currentSearch ? 'No contacts match your search.' : 'No contact requests yet.' ?></p>
    </div>
    <?php endif; ?>
</div>

<!-- ── Pagination ────────────────────────────── -->
<?php if (($contactsData['last_page'] ?? 1) > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-center flex-wrap gap-1">
        <?php for ($p = 1; $p <= $contactsData['last_page']; $p++): ?>
        <li class="page-item <?= $p === ($contactsData['current_page'] ?? 1) ? 'active' : '' ?>">
            <a class="page-link rounded-2" href="?page=<?= $p ?><?= $currentStatus ? '&status='.urlencode($currentStatus) : '' ?><?= $currentSearch ? '&search='.urlencode($currentSearch) : '' ?>">
                <?= $p ?>
            </a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
