<?php
$scansByDayJson = json_encode(array_map(fn($r) => ['day' => $r['day'], 'total' => (int)$r['total']], $scansByDay ?? []));
?>

<!-- ── Stat Cards ─────────────────────────────── -->
<div class="row g-3 mb-4">
    <?php
    $statCards = [
        ['label'=>'Total Leads',      'value'=>$stats['total_leads'],      'icon'=>'bi-people-fill',    'color'=>'primary',   'sub'=>'All time'],
        ['label'=>'New Contacts',     'value'=>$stats['total_contacts'],   'icon'=>'bi-envelope-fill',  'color'=>'secondary', 'sub'=>'Inbox messages'],
        ['label'=>'Total Audits',     'value'=>$stats['total_audits'],     'icon'=>'bi-search',         'color'=>'info',      'sub'=>'Website scans'],
        ['label'=>'Audits This Week', 'value'=>$stats['audits_this_week'], 'icon'=>'bi-graph-up-arrow', 'color'=>'success',   'sub'=>'Last 7 days'],
        ['label'=>'Avg Score (30d)',  'value'=>$stats['avg_score_30d'] ?? 0,'icon'=>'bi-speedometer2',  'color'=>'warning',   'sub'=>'Recent quality'],
        ['label'=>'Help Requests',    'value'=>$stats['help_requests_30d'] ?? 0,'icon'=>'bi-life-preserver','color'=>'danger','sub'=>'Last 30 days'],
    ];
    foreach ($statCards as $c): ?>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-card-icon bg-<?= $c['color'] ?>-soft text-<?= $c['color'] ?>">
                <i class="bi <?= $c['icon'] ?>"></i>
            </div>
            <div style="min-width:0;">
                <div class="stat-card-value"><?= number_format($c['value']) ?></div>
                <div class="stat-card-label"><?= e($c['label']) ?></div>
                <div class="stat-card-trend"><?= e($c['sub']) ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Pipeline Overview ──────────────────────── -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><i class="bi bi-funnel-fill me-2 text-primary"></i>Lead Pipeline</h6>
                <a href="<?= url('admin/leads') ?>" class="btn btn-sm btn-outline-primary" style="font-size:.75rem;padding:.25rem .6rem;">View All</a>
            </div>
            <div class="admin-card-body">
                <?php
                $pipelineStages = [
                    'new'         => ['label'=>'New',        'color'=>'#6222CC', 'icon'=>'bi-star-fill'],
                    'reviewed'    => ['label'=>'Reviewed',   'color'=>'#06b6d4', 'icon'=>'bi-eye-fill'],
                    'contacted'   => ['label'=>'Contacted',  'color'=>'#FBA504', 'icon'=>'bi-telephone-fill'],
                    'quote_sent'  => ['label'=>'Quote Sent', 'color'=>'#f59e0b', 'icon'=>'bi-file-earmark-text-fill'],
                    'closed_won'  => ['label'=>'Closed Won', 'color'=>'#10b981', 'icon'=>'bi-trophy-fill'],
                    'closed_lost' => ['label'=>'Lost',       'color'=>'#94a3b8', 'icon'=>'bi-x-circle-fill'],
                ];
                $pipelineCounts = [];
                foreach ($pipelineStages as $key => $stage) {
                    $pipelineCounts[$key] = 0;
                }
                foreach ($recentLeads ?? [] as $lead) {
                    $s = $lead['status'] ?? 'new';
                    if (isset($pipelineCounts[$s])) $pipelineCounts[$s]++;
                }
                $totalLeadsData = $stats['total_leads'] ?? 0;
                ?>
                <div class="row g-2">
                    <?php foreach ($pipelineStages as $key => $stage): ?>
                    <div class="col-6 col-sm-4 col-xl-2">
                        <a href="<?= url('admin/leads') ?>?status=<?= $key ?>" class="text-decoration-none">
                            <div class="d-flex flex-column align-items-center justify-content-center p-3 rounded-3 hover-lift"
                                 style="background:<?= $stage['color'] ?>18;border:1px solid <?= $stage['color'] ?>30;min-height:90px;">
                                <i class="bi <?= $stage['icon'] ?> mb-2" style="color:<?= $stage['color'] ?>;font-size:1.3rem;"></i>
                                <div class="fw-800" style="color:<?= $stage['color'] ?>;font-size:1.4rem;line-height:1;"><?= $pipelineCounts[$key] ?></div>
                                <div style="font-size:0.72rem;color:#6b5b8f;font-weight:600;margin-top:3px;"><?= $stage['label'] ?></div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Lead Intelligence + Recent Contacts ────── -->
<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <h6><i class="bi bi-bullseye me-2 text-danger"></i>Lead Intelligence</h6>
                <span class="text-muted" style="font-size:.73rem;">Top leads by engagement</span>
            </div>
            <div class="admin-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:.82rem;">
                        <thead>
                            <tr>
                                <th class="ps-3">Lead</th>
                                <th>Source</th>
                                <th>Scans</th>
                                <th>Msgs</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leadIntelligence ?? [] as $lead): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="lead-avatar" style="width:28px;height:28px;font-size:.72rem;">
                                            <?= strtoupper(substr($lead['contact_name'] ?: ($lead['email'] ?: '?'), 0, 1)) ?>
                                        </div>
                                        <div style="min-width:0;">
                                            <a href="<?= url('admin/leads/' . $lead['id']) ?>" class="text-decoration-none fw-semibold text-dark d-block text-truncate" style="max-width:130px;">
                                                <?= e($lead['contact_name'] ?: ($lead['email'] ?: 'Anonymous')) ?>
                                            </a>
                                            <div class="text-muted" style="font-size:.7rem;max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                <?= e(parse_url($lead['website_url'], PHP_URL_HOST) ?: $lead['website_url']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border" style="font-size:.67rem;"><?= e($lead['source'] ?: 'audit') ?></span></td>
                                <td><span class="fw-600"><?= (int)($lead['scan_count'] ?? 0) ?></span></td>
                                <td><span class="fw-600"><?= (int)($lead['help_requests'] ?? 0) ?></span></td>
                                <td>
                                    <?php if ($lead['avg_score'] !== null):
                                        $sc = (int)$lead['avg_score'];
                                        $cls = $sc >= 70 ? 'success' : ($sc >= 50 ? 'warning' : 'danger');
                                    ?>
                                    <span class="badge bg-<?= $cls ?>" style="font-size:.7rem;"><?= $sc ?></span>
                                    <?php else: ?><span class="text-muted">–</span><?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($leadIntelligence)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-bar-chart d-block mb-1" style="font-size:1.5rem;opacity:.3;"></i>No data yet
                            </td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Contact Submissions -->
    <div class="col-lg-5">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <h6><i class="bi bi-envelope-fill me-2" style="color:#FBA504;"></i>Recent Contact Inbox</h6>
                <a href="<?= url('admin/contacts') ?>" class="btn btn-sm btn-outline-primary" style="font-size:.75rem;padding:.25rem .6rem;">View All</a>
            </div>
            <div class="admin-card-body p-0">
                <?php if (!empty($recentContacts)): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($recentContacts as $c):
                        $isNew = ($c['status'] ?? 'new') === 'new';
                        $srcIcon = $c['source'] === 'contact_form' ? 'bi-envelope-fill' : 'bi-robot';
                    ?>
                    <li class="list-group-item px-3 py-2 border-0 <?= $isNew ? 'border-start border-warning border-3' : '' ?>" style="<?= $isNew ? 'border-left:3px solid #FBA504!important;' : '' ?>">
                        <a href="<?= url('admin/contacts/' . $c['id']) ?>" class="text-decoration-none">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex align-items-center gap-2" style="min-width:0;">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:30px;height:30px;background:linear-gradient(135deg,#6222CC,#9333ea);color:#fff;font-size:.72rem;font-weight:700;">
                                        <?= strtoupper(substr($c['name'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <div style="min-width:0;">
                                        <div class="fw-600 text-dark" style="font-size:.82rem;"><?= e($c['name'] ?? '—') ?></div>
                                        <div class="text-muted text-truncate" style="font-size:.72rem;max-width:160px;">
                                            <?= e(mb_strimwidth($c['message'] ?? '', 0, 45, '…')) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end flex-shrink-0 ms-2">
                                    <?php if ($isNew): ?>
                                    <span class="badge" style="background:#FBA504;font-size:.62rem;">New</span>
                                    <?php endif; ?>
                                    <div class="text-muted" style="font-size:.68rem;margin-top:2px;"><?= timeAgo($c['created_at']) ?></div>
                                </div>
                            </div>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="empty-state py-4">
                    <i class="bi bi-inbox"></i>
                    <p>No contact messages yet</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ── Charts Row ─────────────────────────────── -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <h6><i class="bi bi-bar-chart-line me-2 text-primary"></i>Audits Per Day</h6>
                <span class="text-muted" style="font-size:.73rem;">Last 30 days</span>
            </div>
            <div class="admin-card-body">
                <canvas id="auditsChart" height="110"></canvas>
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
                    <li class="list-group-item border-0">
                        <div class="empty-state py-3">
                            <i class="bi bi-clipboard2"></i>
                            <p>No audit data yet</p>
                        </div>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- ── Recent Leads & Scans ───────────────────── -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><i class="bi bi-people-fill me-2 text-primary"></i>Recent Leads</h6>
                <a href="<?= url('admin/leads') ?>" class="btn btn-sm btn-outline-primary" style="font-size:.75rem;padding:.25rem .6rem;">View All</a>
            </div>
            <div class="admin-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:.82rem;">
                        <thead>
                            <tr><th class="ps-3">Contact</th><th>Website</th><th>Status</th><th>When</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentLeads ?? [] as $lead):
                                $isAnon = empty($lead['contact_name']) && empty($lead['email']);
                                $name   = $lead['contact_name'] ?: ($lead['email'] ?: 'Anonymous');
                                $init   = $isAnon ? '?' : strtoupper(substr($name, 0, 1));
                                $sc = ['new'=>'primary','reviewed'=>'info','contacted'=>'warning','quote_sent'=>'warning','closed_won'=>'success','closed_lost'=>'secondary'][$lead['status'] ?? 'new'] ?? 'secondary';
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="lead-avatar" style="width:26px;height:26px;font-size:.68rem;"><?= $init ?></div>
                                        <a href="<?= url('admin/leads/' . $lead['id']) ?>" class="text-decoration-none fw-semibold text-dark"><?= e($name) ?></a>
                                    </div>
                                </td>
                                <td class="text-muted text-truncate-150"><?= e(parse_url($lead['website_url'], PHP_URL_HOST) ?: $lead['website_url']) ?></td>
                                <td><span class="badge bg-<?= $sc ?>" style="font-size:.68rem;"><?= e(ucfirst(str_replace('_',' ',$lead['status'] ?? 'new'))) ?></span></td>
                                <td class="text-muted" style="font-size:.75rem;"><?= timeAgo($lead['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentLeads)): ?>
                            <tr><td colspan="4" class="text-muted text-center py-4">
                                <i class="bi bi-people d-block mb-1" style="font-size:1.5rem;opacity:.3;"></i>No leads yet
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
                <h6><i class="bi bi-search me-2 text-info"></i>Recent Scans</h6>
                <a href="<?= url('admin/scans') ?>" class="btn btn-sm btn-outline-primary" style="font-size:.75rem;padding:.25rem .6rem;">View All</a>
            </div>
            <div class="admin-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:.82rem;">
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
                                    <span class="badge bg-<?= $scoreClass ?>" style="font-size:.72rem;"><?= $scan['overall_score'] ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary" style="font-size:.72rem;">–</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $sts = $scan['req_status'] ?? $scan['status'] ?? 'pending';
                                    $stc = ['completed'=>'success','processing'=>'warning','failed'=>'danger','pending'=>'secondary'][$sts] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $stc ?>-soft text-<?= $stc ?>" style="font-size:.68rem;"><?= ucfirst($sts) ?></span>
                                </td>
                                <td class="text-muted" style="font-size:.75rem;"><?= timeAgo($scan['requested_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentScans)): ?>
                            <tr><td colspan="4" class="text-muted text-center py-4">
                                <i class="bi bi-search d-block mb-1" style="font-size:1.5rem;opacity:.3;"></i>No scans yet
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
document.addEventListener('DOMContentLoaded', function () {
    var data = <?= $scansByDayJson ?>;
    var ctx  = document.getElementById('auditsChart');
    if (!ctx || !data.length) return;
    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: data.map(d => {
                var dt = new Date(d.day);
                return dt.toLocaleDateString('en-US', { month:'short', day:'numeric' });
            }),
            datasets: [{
                label: 'Audits',
                data: data.map(d => d.total),
                backgroundColor: 'rgba(98,34,204,0.15)',
                borderColor: '#6222CC',
                borderWidth: 2,
                borderRadius: 7,
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
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(98,34,204,0.05)' } },
                x: { ticks: { maxTicksLimit: 12, font: { size: 11 } }, grid: { display: false } }
            }
        }
    });
});
</script>
