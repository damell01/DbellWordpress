<div class="d-flex flex-wrap gap-2 align-items-center mb-4 justify-content-between">
    <form class="d-flex flex-wrap gap-2 align-items-center" method="GET">
        <div class="input-group input-group-sm" style="width:240px;">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" name="search" class="form-control border-start-0 ps-0"
                   placeholder="Search leads..." value="<?= e($search ?? '') ?>">
        </div>
        <select name="status" class="form-select form-select-sm" style="width:160px">
            <option value="">All Status</option>
            <option value="new" <?= ($status ?? '') === 'new' ? 'selected' : '' ?>>New</option>
            <option value="reviewed" <?= ($status ?? '') === 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
            <option value="contacted" <?= ($status ?? '') === 'contacted' ? 'selected' : '' ?>>Contacted</option>
            <option value="quote_sent" <?= ($status ?? '') === 'quote_sent' ? 'selected' : '' ?>>Quote Sent</option>
            <option value="closed_won" <?= ($status ?? '') === 'closed_won' ? 'selected' : '' ?>>Closed Won</option>
            <option value="closed_lost" <?= ($status ?? '') === 'closed_lost' ? 'selected' : '' ?>>Closed Lost</option>
        </select>
        <select name="service" class="form-select form-select-sm" style="width:160px">
            <option value="">All Services</option>
            <option value="web" <?= ($service ?? '') === 'web' ? 'selected' : '' ?>>Web Design</option>
            <option value="software" <?= ($service ?? '') === 'software' ? 'selected' : '' ?>>Software</option>
            <option value="seo" <?= ($service ?? '') === 'seo' ? 'selected' : '' ?>>SEO</option>
            <option value="automation" <?= ($service ?? '') === 'automation' ? 'selected' : '' ?>>Automation</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
        <?php if (!empty($search) || !empty($status)): ?>
        <a href="<?= url('admin/leads') ?>" class="btn btn-outline-secondary btn-sm">Clear</a>
        <?php endif; ?>
    </form>
    <div class="d-flex gap-2">
        <a href="<?= url('admin/export/leads') ?>" class="btn btn-success btn-sm px-3">
            <i class="bi bi-download me-1"></i>Export CSV
        </a>
    </div>
</div>

<?php $total = $leadsData['total'] ?? 0; ?>
<p class="text-muted small mb-3">
    <i class="bi bi-people me-1"></i>
    Showing <strong><?= count($leadsData['items'] ?? []) ?></strong> of <strong><?= number_format($total) ?></strong> total leads
    <?php if (!empty($search)): ?>- filtered by "<strong><?= e($search) ?></strong>"<?php endif; ?>
    <?php if (!empty($status)): ?>- status: <strong><?= e(ucfirst(str_replace('_', ' ', $status))) ?></strong><?php endif; ?>
</p>

<div class="admin-card">
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:36px;">#</th>
                        <th>Contact</th>
                        <th>Business</th>
                        <th>Website</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Follow-Up</th>
                        <th>Date</th>
                        <th style="width:60px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leadsData['items'] ?? [] as $lead):
                        $isAnonymous = empty($lead['contact_name']) && empty($lead['email']);
                        $initials = $isAnonymous ? '?' : strtoupper(substr($lead['contact_name'] ?: $lead['email'], 0, 1));
                    ?>
                    <tr>
                        <td class="text-muted" style="font-size:.75rem;"><?= $lead['id'] ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="lead-avatar"><?= $initials ?></div>
                                <div>
                                    <div class="fw-semibold" style="font-size:.85rem;">
                                        <?= e($lead['contact_name'] ?: ($isAnonymous ? 'Anonymous' : '-')) ?>
                                    </div>
                                    <div class="text-muted" style="font-size:.775rem;">
                                        <?= $lead['email'] ? e($lead['email']) : ($isAnonymous ? '<span class="text-muted fst-italic">No email</span>' : '-') ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:.85rem;"><?= e($lead['business_name'] ?: '-') ?></td>
                        <td>
                            <a href="<?= e($lead['website_url']) ?>" target="_blank" rel="noopener"
                               class="text-decoration-none text-muted text-truncate-200 d-block"
                               style="font-size:.8rem;" title="<?= e($lead['website_url']) ?>">
                                <i class="bi bi-box-arrow-up-right me-1" style="font-size:.7rem;"></i><?= e($lead['website_url']) ?>
                            </a>
                        </td>
                        <td>
                            <?php
                            $srcColors = ['audit_form' => 'primary', 'report_help' => 'success', 'contact_form' => 'warning', 'manual' => 'info'];
                            $srcLabels = ['audit_form' => 'Audit', 'report_help' => 'Report CTA', 'contact_form' => 'Contact', 'manual' => 'Manual'];
                            $src = $lead['source'] ?? 'audit';
                            $srcColor = $srcColors[$src] ?? 'secondary';
                            $srcLabel = $srcLabels[$src] ?? ucfirst($src);
                            ?>
                            <span class="badge bg-<?= $srcColor ?>-soft text-<?= $srcColor ?>" style="font-size:.7rem;"><?= e($srcLabel) ?></span>
                        </td>
                        <td>
                            <?php
                            $statusColors = [
                                'new' => ['bg' => 'primary', 'label' => 'New'],
                                'reviewed' => ['bg' => 'info', 'label' => 'Reviewed'],
                                'contacted' => ['bg' => 'warning', 'label' => 'Contacted'],
                                'quote_sent' => ['bg' => 'warning', 'label' => 'Quote Sent'],
                                'closed_won' => ['bg' => 'success', 'label' => 'Won'],
                                'closed_lost' => ['bg' => 'secondary', 'label' => 'Lost'],
                            ];
                            $sc = $statusColors[$lead['status'] ?? 'new'] ?? ['bg' => 'secondary', 'label' => ucfirst($lead['status'] ?? '')];
                            ?>
                            <span class="badge bg-<?= $sc['bg'] ?>" style="font-size:.75rem;"><?= e($sc['label']) ?></span>
                        </td>
                        <td>
                            <?php $fStage = (int)($lead['follow_up_stage'] ?? 0); ?>
                            <span class="text-muted small">Stage <?= $fStage ?>/4</span>
                            <?php if (!empty($lead['next_follow_up_at']) && $fStage < 4): ?>
                            <br><span class="text-muted" style="font-size:.7rem;"><?= e(date('M j', strtotime($lead['next_follow_up_at']))) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted" style="font-size:.775rem;white-space:nowrap;"><?= timeAgo($lead['created_at']) ?></td>
                        <td>
                            <a href="<?= url('admin/leads/' . $lead['id']) ?>" class="btn-action" title="View lead">
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($leadsData['items'])): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="bi bi-people text-muted" style="font-size:2rem;"></i>
                            <p class="text-muted mt-2 mb-0">No leads found.
                                <?php if (!empty($search) || !empty($status)): ?>
                                <a href="<?= url('admin/leads') ?>">Clear filters</a>
                                <?php else: ?>
                                Leads will appear here when website audits are run.
                                <?php endif; ?>
                            </p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (($leadsData['last_page'] ?? 1) > 1): ?>
<nav class="mt-3" aria-label="Leads pagination">
    <ul class="pagination pagination-sm justify-content-center">
        <?php for ($p = 1; $p <= $leadsData['last_page']; $p++): ?>
        <li class="page-item <?= $p === ($leadsData['current_page'] ?? 1) ? 'active' : '' ?>">
            <a class="page-link rounded-2" href="?page=<?= $p ?>&search=<?= urlencode($search ?? '') ?>&status=<?= urlencode($status ?? '') ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
