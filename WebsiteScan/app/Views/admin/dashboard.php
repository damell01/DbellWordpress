<?php
$scansByDayJson = json_encode(array_map(fn($r) => ['day' => $r['day'], 'total' => (int)$r['total']], $scansByDay ?? []));
?>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <?php
    $statCards = [
        ['label'=>'Total Audits',     'value'=>$stats['total_audits'],     'icon'=>'bi-search',        'color'=>'primary',  'sub'=>'All time'],
        ['label'=>'Total Leads',      'value'=>$stats['total_leads'],      'icon'=>'bi-people-fill',   'color'=>'success',  'sub'=>'All time'],
        ['label'=>'Contact Requests', 'value'=>$stats['total_contacts'],   'icon'=>'bi-envelope-fill', 'color'=>'warning',  'sub'=>'All time'],
        ['label'=>'Audits This Week', 'value'=>$stats['audits_this_week'], 'icon'=>'bi-graph-up-arrow','color'=>'info',     'sub'=>'Last 7 days'],
        ['label'=>'Avg Score (30d)',  'value'=>$stats['avg_score_30d'] ?? 0, 'icon'=>'bi-speedometer2','color'=>'info','sub'=>'Recent report quality'],
        ['label'=>'Help Requests (30d)', 'value'=>$stats['help_requests_30d'] ?? 0, 'icon'=>'bi-life-preserver','color'=>'danger','sub'=>'High-intent conversions'],
    ];
    foreach ($statCards as $c): ?>
    <div class="col-sm-6 col-xl-4">
        <div class="stat-card">
            <div class="stat-card-icon bg-<?= $c['color'] ?>-soft text-<?= $c['color'] ?>">
                <i class="bi <?= $c['icon'] ?>"></i>
            </div>
            <div>
                <div class="stat-card-value"><?= number_format($c['value']) ?></div>
                <div class="stat-card-label"><?= e($c['label']) ?></div>
                <div class="stat-card-trend text-muted"><?= e($c['sub']) ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><i class="bi bi-bullseye me-2 text-danger"></i>Lead Intelligence</h6>
                <span class="text-muted" style="font-size:.75rem;">Most engaged leads based on scans and help requests</span>
            </div>
            <div class="admin-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:.83rem;">
                        <thead>
                            <tr><th>Lead</th><th>Source</th><th>Scans</th><th>Help Requests</th><th>Avg Score</th><th>Last Scan</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leadIntelligence ?? [] as $lead): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('admin/leads/' . $lead['id']) ?>" class="text-decoration-none fw-semibold text-dark">
                                        <?= e($lead['contact_name'] ?: ($lead['email'] ?: (parse_url($lead['website_url'], PHP_URL_HOST) ?: 'Anonymous'))) ?>
                                    </a>
                                    <div class="text-muted small"><?= e(parse_url($lead['website_url'], PHP_URL_HOST) ?: $lead['website_url']) ?></div>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= e($lead['source'] ?: 'audit') ?></span></td>
                                <td><?= (int) ($lead['scan_count'] ?? 0) ?></td>
                                <td><?= (int) ($lead['help_requests'] ?? 0) ?></td>
                                <td><?= $lead['avg_score'] !== null ? (int) $lead['avg_score'] : '–' ?></td>
                                <td class="text-muted"><?= !empty($lead['last_scan_at']) ? timeAgo($lead['last_scan_at']) : 'Never' ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($leadIntelligence)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No lead intelligence yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <h6><i class="bi bi-bar-chart-line me-2 text-primary"></i>Audits Per Day</h6>
                <span class="text-muted" style="font-size:.75rem;">Last 30 days</span>
            </div>
            <div class="admin-card-body">
                <canvas id="auditsChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <h6><i class="bi bi-exclamation-circle me-2 text-warning"></i>Top Issues Found</h6>
            </div>
            <div class="admin-card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach (array_slice($commonIssues ?? [], 0, 8) as $idx => $issue): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2 border-0 <?= $idx % 2 === 0 ? '' : 'bg-light' ?>">
                        <span class="small text-truncate me-2" style="max-width:200px;" title="<?= e($issue['title']) ?>"><?= e($issue['title']) ?></span>
                        <span class="badge bg-primary-soft text-primary rounded-pill fw-600" style="min-width:28px;text-align:center;"><?= $issue['cnt'] ?></span>
                    </li>
                    <?php endforeach; ?>
                    <?php if (empty($commonIssues)): ?>
                    <li class="list-group-item text-muted small px-3 py-4 text-center border-0">
                        <i class="bi bi-clipboard2 d-block mb-2" style="font-size:1.5rem;"></i>No audit data yet
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Recent Leads & Scans -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><i class="bi bi-people-fill me-2 text-success"></i>Recent Leads</h6>
                <a href="<?= url('admin/leads') ?>" class="btn btn-sm btn-outline-primary" style="font-size:.75rem;padding:.25rem .6rem;">View All</a>
            </div>
            <div class="admin-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:.83rem;">
                        <thead>
                            <tr><th>Contact</th><th>Website</th><th>Status</th><th>When</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentLeads ?? [] as $lead):
                                $isAnon = empty($lead['contact_name']) && empty($lead['email']);
                                $name   = $lead['contact_name'] ?: ($lead['email'] ?: 'Anonymous');
                                $init   = $isAnon ? '?' : strtoupper(substr($name, 0, 1));
                                $sc = ['new'=>'primary','reviewed'=>'info','contacted'=>'warning','quote_sent'=>'warning','closed_won'=>'success','closed_lost'=>'secondary'][$lead['status'] ?? 'new'] ?? 'secondary';
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="lead-avatar" style="width:26px;height:26px;font-size:.7rem;"><?= $init ?></div>
                                        <a href="<?= url('admin/leads/' . $lead['id']) ?>" class="text-decoration-none fw-semibold text-dark"><?= e($name) ?></a>
                                    </div>
                                </td>
                                <td class="text-muted text-truncate-150"><?= e(parse_url($lead['website_url'], PHP_URL_HOST) ?: $lead['website_url']) ?></td>
                                <td><span class="badge bg-<?= $sc ?>" style="font-size:.7rem;"><?= e(ucfirst(str_replace('_',' ',$lead['status'] ?? 'new'))) ?></span></td>
                                <td class="text-muted"><?= timeAgo($lead['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentLeads)): ?>
                            <tr><td colspan="4" class="text-muted text-center py-4">
                                <i class="bi bi-people d-block mb-1" style="font-size:1.5rem;"></i>No leads yet
                            </td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><i class="bi bi-search me-2 text-primary"></i>Recent Scans</h6>
                <a href="<?= url('admin/scans') ?>" class="btn btn-sm btn-outline-primary" style="font-size:.75rem;padding:.25rem .6rem;">View All</a>
            </div>
            <div class="admin-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:.83rem;">
                        <thead>
                            <tr><th>URL</th><th>Score</th><th>Status</th><th>When</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentScans ?? [] as $scan): ?>
                            <tr>
                                <td class="text-truncate-150">
                                    <?php if (!empty($scan['report_token'])): ?>
                                    <a href="<?= url('report/' . $scan['report_token']) ?>" target="_blank"
                                       class="text-decoration-none text-dark" title="<?= e($scan['website_url']) ?>">
                                        <?= e(parse_url($scan['website_url'], PHP_URL_HOST) ?: $scan['website_url']) ?>
                                    </a>
                                    <?php else: ?>
                                    <span class="text-muted"><?= e(parse_url($scan['website_url'], PHP_URL_HOST) ?: $scan['website_url']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($scan['overall_score'] !== null):
                                        $scoreClass = $scan['overall_score'] >= 70 ? 'success' : ($scan['overall_score'] >= 50 ? 'warning' : 'danger');
                                    ?>
                                    <span class="badge bg-<?= $scoreClass ?>" style="font-size:.75rem;"><?= $scan['overall_score'] ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary" style="font-size:.75rem;">–</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $sts = $scan['req_status'] ?? $scan['status'] ?? 'pending';
                                    $stc = ['completed'=>'success','processing'=>'warning','failed'=>'danger','pending'=>'secondary'][$sts] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $stc ?>-soft text-<?= $stc ?>" style="font-size:.7rem;"><?= ucfirst($sts) ?></span>
                                </td>
                                <td class="text-muted"><?= timeAgo($scan['requested_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentScans)): ?>
                            <tr><td colspan="4" class="text-muted text-center py-4">
                                <i class="bi bi-search d-block mb-1" style="font-size:1.5rem;"></i>No scans yet
                            </td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var data = <?= $scansByDayJson ?>;
    var ctx  = document.getElementById('auditsChart');
    if (!ctx || !data.length) return;
    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: data.map(d => {
                var dt = new Date(d.day);
                return dt.toLocaleDateString('en-US', {month:'short',day:'numeric'});
            }),
            datasets: [{
                label: 'Audits',
                data: data.map(d => d.total),
                backgroundColor: 'rgba(37,99,235,0.18)',
                borderColor: '#2563eb',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: function(items) {
                            return data[items[0].dataIndex]?.day ?? items[0].label;
                        }
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.04)' } },
                x: { ticks: { maxTicksLimit: 12, font: { size: 11 } }, grid: { display: false } }
            }
        }
    });
})();
</script>
