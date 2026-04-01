<!-- Toolbar -->
<div class="d-flex flex-wrap gap-2 align-items-center mb-4 justify-content-between">
    <form class="d-flex gap-2" method="GET">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by URL..." value="<?= e($search ?? '') ?>">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="<?= url('admin/scans') ?>" class="btn btn-outline-secondary btn-sm">Clear</a>
    </form>
    <a href="<?= url('admin/export/scans') ?>" class="btn btn-success btn-sm">
        <i class="bi bi-download me-1"></i>Export CSV
    </a>
</div>

<div class="admin-card">
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>URL</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th>Date</th>
                        <th>Report</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items ?? [] as $scan): ?>
                    <tr>
                        <td class="text-muted small"><?= $scan['id'] ?></td>
                        <td class="small text-truncate" style="max-width:200px"><?= e($scan['website_url']) ?></td>
                        <td>
                            <?php
                            $sc = match($scan['status'] ?? '') {
                                'completed' => 'success',
                                'processing' => 'warning',
                                'failed' => 'danger',
                                default => 'secondary',
                            };
                            ?>
                            <span class="badge bg-<?= $sc ?>"><?= e($scan['status'] ?? '—') ?></span>
                        </td>
                        <td>
                            <?php if (isset($scan['overall_score']) && $scan['overall_score'] !== null): ?>
                            <span class="badge <?= $scan['overall_score'] >= 70 ? 'bg-success' : ($scan['overall_score'] >= 50 ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                <?= $scan['overall_score'] ?>
                            </span>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td class="text-muted small"><?= timeAgo($scan['requested_at']) ?></td>
                        <td>
                            <?php if (!empty($scan['report_token'])): ?>
                            <a href="<?= url('report/' . $scan['report_token']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">View</a>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($items)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No scans found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php
$lastPage = (int)ceil(($total ?? 0) / ($perPage ?? 20));
if ($lastPage > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-center">
        <?php for ($p = 1; $p <= $lastPage; $p++): ?>
        <li class="page-item <?= $p === ($page ?? 1) ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search ?? '') ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
