<?php
$statusCounts = $statusCounts ?? ['new' => 0, 'read' => 0, 'replied' => 0, 'archived' => 0];
$totalContacts = array_sum($statusCounts);

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
$currentStatus = $status ?? '';
$currentSearch = $search ?? '';
?>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <a href="<?= url('admin/contacts') ?>" class="text-decoration-none">
            <div class="stat-card <?= $currentStatus === '' && $currentSearch === '' ? 'border border-primary' : '' ?>">
                <div class="stat-card-icon bg-info-soft text-info"><i class="bi bi-envelope-fill"></i></div>
                <div>
                    <div class="stat-card-value"><?= number_format($totalContacts) ?></div>
                    <div class="stat-card-label">All Contacts</div>
                </div>
            </div>
        </a>
    </div>
    <?php foreach (['new', 'read', 'replied', 'archived'] as $s): ?>
    <div class="col-sm-6 col-xl-3">
        <a href="<?= url('admin/contacts') ?>?status=<?= $s ?>" class="text-decoration-none">
            <div class="stat-card <?= $currentStatus === $s ? 'border border-' . $statusColors[$s] : '' ?>">
                <div class="stat-card-icon bg-<?= $statusColors[$s] ?>-soft text-<?= $statusColors[$s] ?>">
                    <i class="bi bi-<?= $s === 'new' ? 'bell-fill' : ($s === 'read' ? 'eye-fill' : ($s === 'replied' ? 'check-circle-fill' : 'archive-fill')) ?>"></i>
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

<!-- Toolbar -->
<div class="admin-card mb-4">
    <div class="admin-card-body py-3">
        <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <form method="GET" action="<?= url('admin/contacts') ?>" class="d-flex gap-2 flex-wrap" style="min-width:0;flex:1;">
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
                <a href="<?= url('admin/contacts') ?>" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </form>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-funnel me-1"></i>Filter by Status
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item <?= $currentStatus === '' ? 'active' : '' ?>" href="<?= url('admin/contacts') ?>">All</a></li>
                        <?php foreach (['new', 'read', 'replied', 'archived'] as $s): ?>
                        <li><a class="dropdown-item <?= $currentStatus === $s ? 'active' : '' ?>"
                               href="<?= url('admin/contacts') ?>?status=<?= $s ?><?= $currentSearch ? '&search=' . urlencode($currentSearch) : '' ?>">
                            <span class="badge bg-<?= $statusColors[$s] ?> me-1">&nbsp;</span> <?= $statusLabels[$s] ?> (<?= $statusCounts[$s] ?>)
                        </a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <a href="<?= url('admin/export/contacts') ?><?= $currentStatus ? '?status=' . urlencode($currentStatus) : '' ?>"
                   class="btn btn-outline-success btn-sm">
                    <i class="bi bi-download me-1"></i>Export CSV
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="admin-card">
    <div class="admin-card-header d-flex justify-content-between align-items-center">
        <h6 class="fw-semibold mb-0">
            <i class="bi bi-envelope-fill me-2 text-primary"></i>Contact Submissions
            <?php if ($currentSearch): ?>
            <span class="text-muted fw-normal small ms-2">— results for "<?= e($currentSearch) ?>"</span>
            <?php endif; ?>
        </h6>
        <span class="badge bg-light text-dark border"><?= number_format($contactsData['total'] ?? 0) ?> total</span>
    </div>
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:.85rem;">
                <thead class="table-light">
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
                    <tr class="<?= ($cr['status'] ?? 'new') === 'new' ? 'table-warning' : '' ?>">
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary-soft text-primary d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                     style="width:34px;height:34px;font-size:.8rem;">
                                    <?= strtoupper(substr($cr['name'] ?? 'U', 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark"><?= e($cr['name'] ?? '—') ?></div>
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
                            <span class="badge bg-light text-dark border"><?= e($cr['service_type']) ?></span>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="max-width:260px;">
                            <span class="text-truncate d-block" style="max-width:240px;" title="<?= e($cr['message'] ?? '') ?>">
                                <?= e(mb_strimwidth($cr['message'] ?? '', 0, 80, '…')) ?>
                            </span>
                        </td>
                        <td>
                            <?php $src = $cr['source'] ?? 'website'; ?>
                            <span class="badge bg-<?= $src === 'contact_form' ? 'warning text-dark' : ($src === 'fix_my_website' ? 'info' : 'secondary') ?>">
                                <?= e($src) ?>
                            </span>
                        </td>
                        <td>
                            <?php $st = $cr['status'] ?? 'new'; ?>
                            <span class="badge bg-<?= $statusColors[$st] ?? 'secondary' ?>">
                                <?= $statusLabels[$st] ?? e($st) ?>
                            </span>
                        </td>
                        <td class="text-muted"><?= timeAgo($cr['created_at']) ?></td>
                        <td class="pe-3">
                            <a href="<?= url('admin/contacts/' . $cr['id']) ?>"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($contactsData['items'])): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-3 d-block mb-2 opacity-30"></i>
                            <?= $currentSearch ? 'No contacts match your search.' : 'No contact requests yet.' ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if (($contactsData['last_page'] ?? 1) > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-center">
        <?php for ($p = 1; $p <= $contactsData['last_page']; $p++): ?>
        <li class="page-item <?= $p === ($contactsData['current_page'] ?? 1) ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $p ?><?= $currentStatus ? '&status=' . urlencode($currentStatus) : '' ?><?= $currentSearch ? '&search=' . urlencode($currentSearch) : '' ?>">
                <?= $p ?>
            </a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
