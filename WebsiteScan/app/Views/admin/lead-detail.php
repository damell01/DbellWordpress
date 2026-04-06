<?php
$statusOptions = [
    'new'         => ['label'=>'New',        'bg'=>'primary', 'hex'=>'#6222CC'],
    'reviewed'    => ['label'=>'Reviewed',   'bg'=>'info',    'hex'=>'#06b6d4'],
    'contacted'   => ['label'=>'Contacted',  'bg'=>'warning', 'hex'=>'#FBA504'],
    'quote_sent'  => ['label'=>'Quote Sent', 'bg'=>'warning', 'hex'=>'#f59e0b'],
    'closed_won'  => ['label'=>'Won ✓',      'bg'=>'success', 'hex'=>'#10b981'],
    'closed_lost' => ['label'=>'Lost',       'bg'=>'secondary','hex'=>'#94a3b8'],
];
$curStatus = $lead['status'] ?? 'new';
$sc = $statusOptions[$curStatus] ?? ['label'=>ucfirst($curStatus),'bg'=>'secondary','hex'=>'#94a3b8'];
$fStage = (int)($lead['follow_up_stage'] ?? 0);

// Pre-compute first name for templates
$_tplFirstName = !empty($lead['contact_name'])
    ? (explode(' ', trim($lead['contact_name']))[0] ?: 'there')
    : 'there';

// Suggested templates (beyond the auto-pipeline templates)
$suggestedTemplates = [
    [
        'label'   => '📞 Quick Check-In',
        'subject' => 'Checking in — DBell Creations',
        'body'    => "Hey {$_tplFirstName},\n\nJust wanted to check in and see if you have any questions or if there's anything I can help you with.\n\nFeel free to reply to this email or give us a call at 251-406-2292.\n\n— DBell Creations",
    ],
    [
        'label'   => '🔍 Free Audit Offer',
        'subject' => 'Your free website audit is ready — DBell Creations',
        'body'    => "Hey {$_tplFirstName},\n\nI wanted to make sure you saw this — we offer a completely free website audit that shows you exactly what's hurting your site's performance and search rankings.\n\n👉 Run your free audit: https://www.dbellcreations.com/scan.html\n\nNo obligation, takes about 60 seconds. Let me know if you have questions!\n\n— DBell Creations\n📞 251-406-2292",
    ],
    [
        'label'   => '💰 Pricing Overview',
        'subject' => 'Our website packages — DBell Creations',
        'body'    => "Hey {$_tplFirstName},\n\nWanted to send over a quick overview of our most popular packages:\n\n⭐ Starter Website — \$350 (SALE)\n   Professional web presence, fast turnaround.\n\n⭐ Business Website — \$750 (SALE)\n   Full site with lead forms, SEO, and CMS.\n\n⭐ Custom Build — \$1,000–\$1,500+\n   Advanced features and custom designs.\n\n👉 Full pricing: https://www.dbellcreations.com/pricing.html\n\nReply anytime and I'll help you choose the right fit!\n\n— DBell Creations\n📞 251-406-2292",
    ],
    [
        'label'   => '🤝 Schedule a Call',
        'subject' => "Let's hop on a quick call — DBell Creations",
        'body'    => "Hey {$_tplFirstName},\n\nI'd love to connect for a quick 15-minute call to learn more about your business and see how we can help.\n\nJust reply to this email with a time that works for you, or call us directly at 📞 251-406-2292.\n\nLooking forward to chatting!\n\n— DBell Creations\n🌐 https://www.dbellcreations.com",
    ],
];
?>

<!-- ── Breadcrumb ────────────────────────────── -->
<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
    <a href="<?= url('admin/leads') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
        <i class="bi bi-arrow-left me-1"></i>Back to Leads
    </a>
    <span class="text-muted" style="font-size:.82rem;"><i class="bi bi-chevron-right"></i></span>
    <span class="fw-600" style="font-size:.88rem;color:#1e0a3c;">
        <?= e($lead['contact_name'] ?: ($lead['email'] ?: 'Lead #' . $lead['id'])) ?>
    </span>
    <span class="badge bg-<?= $sc['bg'] ?> ms-1"><?= $sc['label'] ?></span>
</div>

<div class="row g-4">
    <!-- ── Left Column ───────────────────────── -->
    <div class="col-lg-7">

        <!-- Lead Header -->
        <div class="admin-card mb-4">
            <div class="admin-card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-800 flex-shrink-0"
                         style="width:60px;height:60px;font-size:1.5rem;background:linear-gradient(135deg,#6222CC,#9333ea);box-shadow:0 4px 14px rgba(98,34,204,0.35);">
                        <?= strtoupper(substr($lead['contact_name'] ?: ($lead['email'] ?: '?'), 0, 1)) ?>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="mb-1 fw-bold" style="color:#1e0a3c;">
                            <?= e($lead['contact_name'] ?: 'Anonymous Lead') ?>
                        </h5>
                        <div class="d-flex flex-wrap gap-2">
                            <?php if (!empty($lead['email'])): ?>
                            <a href="mailto:<?= e($lead['email']) ?>" class="text-decoration-none" style="color:#6222CC;font-size:.85rem;">
                                <i class="bi bi-envelope-fill me-1"></i><?= e($lead['email']) ?>
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($lead['phone'])): ?>
                            <a href="tel:<?= e($lead['phone']) ?>" class="text-decoration-none" style="color:#10b981;font-size:.85rem;">
                                <i class="bi bi-telephone-fill me-1"></i><?= e($lead['phone']) ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Detail grid -->
                <div class="row g-2">
                    <?php $detailItems = [
                        ['icon'=>'bi-building-fill','label'=>'Business','value'=>$lead['business_name'] ?: null,'color'=>'#6222CC'],
                        ['icon'=>'bi-globe','label'=>'Website','value'=>$lead['website_url'] ?: null,'color'=>'#06b6d4','link'=>$lead['website_url'] ?: ''],
                        ['icon'=>'bi-stars','label'=>'Service Interest','value'=>$lead['service_interest'] ?: null,'color'=>'#FBA504'],
                        ['icon'=>'bi-funnel-fill','label'=>'Source','value'=>ucfirst(str_replace('_',' ',$lead['source'] ?? 'unknown')),'color'=>'#10b981'],
                        ['icon'=>'bi-geo-alt-fill','label'=>'Source Page','value'=>$lead['source_page'] ?: null,'color'=>'#94a3b8'],
                        ['icon'=>'bi-calendar3','label'=>'Created','value'=>$lead['created_at'] ?: null,'color'=>'#6b5b8f'],
                    ];
                    foreach ($detailItems as $item): if (empty($item['value'])) continue; ?>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background:#faf8ff;">
                            <i class="bi <?= $item['icon'] ?>" style="color:<?= $item['color'] ?>;font-size:.88rem;flex-shrink:0;"></i>
                            <div style="min-width:0;">
                                <div style="font-size:.65rem;color:#6b5b8f;font-weight:600;text-transform:uppercase;letter-spacing:.05em;"><?= $item['label'] ?></div>
                                <?php if (!empty($item['link'])): ?>
                                <a href="<?= e($item['link']) ?>" target="_blank" rel="noopener"
                                   class="d-block text-truncate text-decoration-none" style="font-size:.82rem;font-weight:600;color:<?= $item['color'] ?>;max-width:180px;">
                                    <?= e(parse_url($item['value'], PHP_URL_HOST) ?: $item['value']) ?>
                                </a>
                                <?php else: ?>
                                <div class="text-truncate" style="font-size:.82rem;font-weight:600;color:#1e0a3c;max-width:180px;" title="<?= e($item['value']) ?>"><?= e($item['value']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Follow-up stage bar -->
                <div class="mt-3 p-3 rounded-2" style="background:#faf8ff;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span style="font-size:.75rem;font-weight:700;color:#6b5b8f;text-transform:uppercase;letter-spacing:.06em;">Follow-Up Stage</span>
                        <span style="font-size:.78rem;font-weight:600;color:#6222CC;">Stage <?= $fStage ?> of 4</span>
                    </div>
                    <div class="d-flex gap-2">
                        <?php $stageLabels = ['Initial','Follow-Up','Proposal','Close']; ?>
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                        <div style="flex:1;text-align:center;">
                            <div style="height:7px;border-radius:4px;background:<?= $i <= $fStage ? '#6222CC' : '#e8e2f4' ?>;margin-bottom:3px;"></div>
                            <div style="font-size:.62rem;color:<?= $i <= $fStage ? '#6222CC' : '#94a3b8' ?>;font-weight:600;"><?= $stageLabels[$i-1] ?></div>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <?php if (!empty($lead['next_follow_up_at'])): ?>
                    <div class="mt-2 text-muted" style="font-size:.75rem;">
                        <i class="bi bi-calendar-event me-1"></i>Next follow-up: <strong><?= e($lead['next_follow_up_at']) ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Send Next Pipeline Message -->
        <?php if (!empty($lead['email'])): ?>
        <div class="admin-card mb-4" id="sendMessageCard">
            <div class="admin-card-header">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-send-fill me-2" style="color:#6222CC;"></i>
                    Send Pipeline Message
                    <?php if ($nextStage <= 4 && $nextMessage): ?>
                    <span class="badge ms-2" style="background:#e8e2f4;color:#6222CC;font-size:.7rem;">Stage <?= $nextStage ?> suggested</span>
                    <?php endif; ?>
                </h6>
            </div>
            <div class="admin-card-body">

                <!-- Suggested template chips -->
                <div class="mb-3">
                    <div style="font-size:.72rem;font-weight:700;color:#6b5b8f;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Suggested Templates</div>
                    <div class="d-flex flex-wrap gap-2">
                        <?php if ($nextStage <= 4 && $nextMessage): ?>
                        <button type="button" class="btn btn-sm tpl-btn"
                                style="background:#6222CC;color:#fff;border:none;border-radius:20px;font-size:.78rem;padding:4px 14px;"
                                data-subject="<?= e($nextMessage['subject']) ?>"
                                data-body="<?= e($nextMessage['body']) ?>">
                            <i class="bi bi-robot me-1"></i>Stage <?= $nextStage ?> Auto-Template
                        </button>
                        <?php endif; ?>
                        <?php foreach ($suggestedTemplates as $tpl): ?>
                        <button type="button" class="btn btn-sm tpl-btn"
                                style="background:#f3f0fb;color:#6222CC;border:1px solid #d1c4e9;border-radius:20px;font-size:.78rem;padding:4px 14px;"
                                data-subject="<?= e($tpl['subject']) ?>"
                                data-body="<?= e($tpl['body']) ?>">
                            <?= e($tpl['label']) ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Compose form -->
                <form method="POST" action="<?= url('admin/leads/' . $lead['id'] . '/send-message') ?>" id="sendMsgForm">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:.8rem;font-weight:600;color:#6b5b8f;">To</label>
                        <input type="text" class="form-control form-control-sm" value="<?= e($lead['email']) ?>" readonly
                               style="background:#faf8ff;border-color:#d1c4e9;color:#6b5b8f;">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:.8rem;font-weight:600;color:#6b5b8f;">Subject</label>
                        <input type="text" name="subject" id="msgSubject" class="form-control form-control-sm"
                               style="border-color:#d1c4e9;"
                               placeholder="Enter subject…"
                               value="<?= $nextMessage ? e($nextMessage['subject']) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem;font-weight:600;color:#6b5b8f;">Message</label>
                        <textarea name="body" id="msgBody" class="form-control" rows="10"
                                  style="border-color:#d1c4e9;resize:vertical;font-family:monospace;font-size:.82rem;"
                                  placeholder="Compose your message…" required><?= $nextMessage ? e($nextMessage['body']) : '' ?></textarea>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-send-fill me-1"></i>Send Message
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearMsgBtn">
                            <i class="bi bi-x-circle me-1"></i>Clear
                        </button>
                        <span class="text-muted ms-auto" style="font-size:.72rem;">
                            Sending as: <strong>DBell Creations</strong>
                        </span>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Update Status -->
        <div class="admin-card mb-4">
            <div class="admin-card-header"><h6 class="fw-semibold mb-0"><i class="bi bi-pencil-square me-2 text-primary"></i>Update Pipeline Status</h6></div>
            <div class="admin-card-body">
                <form method="POST" action="<?= url('admin/leads/' . $lead['id']) ?>" class="d-flex flex-wrap gap-2 align-items-center">
                    <?= csrf_field() ?>
                    <select name="status" class="form-select" style="max-width:220px;border-color:#d1c4e9;">
                        <?php foreach ($statusOptions as $val => $info): ?>
                        <option value="<?= $val ?>" <?= $curStatus === $val ? 'selected' : '' ?>><?= $info['label'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Update Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Internal Notes -->
        <div class="admin-card">
            <div class="admin-card-header"><h6 class="fw-semibold mb-0"><i class="bi bi-sticky-fill me-2 text-warning"></i>Internal Notes</h6></div>
            <div class="admin-card-body">
                <form method="POST" action="<?= url('admin/leads/' . $lead['id'] . '/note') ?>" class="mb-3">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <textarea name="note" class="form-control" rows="3"
                                  style="border-color:#d1c4e9;resize:vertical;"
                                  placeholder="Add an internal note, next steps, call summary…" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>Add Note
                    </button>
                </form>

                <?php if (!empty($notes)): ?>
                <ul class="timeline">
                    <?php foreach ($notes as $noteItem): ?>
                    <li class="timeline-item">
                        <div class="timeline-icon bg-primary-soft text-primary">
                            <i class="bi bi-person-fill" style="font-size:.75rem;"></i>
                        </div>
                        <div class="timeline-body">
                            <div class="timeline-title"><?= e($noteItem['admin_name'] ?? 'Admin') ?></div>
                            <div class="p-2 rounded-2 mt-1" style="background:#faf8ff;font-size:.83rem;line-height:1.65;">
                                <?= nl2br(e($noteItem['note'])) ?>
                            </div>
                            <div class="timeline-meta"><?= timeAgo($noteItem['created_at']) ?></div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="text-muted small fst-italic text-center py-2">No notes yet — add one above.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Right Column ──────────────────────── -->
    <div class="col-lg-5">

        <!-- Quick Actions -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h6 class="fw-semibold mb-0"><i class="bi bi-lightning-charge-fill me-2" style="color:#FBA504;"></i>Quick Actions</h6>
            </div>
            <div class="admin-card-body d-grid gap-2">
                <?php if (!empty($lead['email'])): ?>
                <a href="#sendMessageCard" class="btn btn-primary"
                   onclick="document.getElementById('sendMessageCard').scrollIntoView({behavior:'smooth'});return false;">
                    <i class="bi bi-send-fill me-2"></i>Compose & Send Message
                </a>
                <a href="mailto:<?= e($lead['email']) ?>?subject=<?= urlencode('Following up — DBell Creations') ?>"
                   class="btn btn-outline-secondary">
                    <i class="bi bi-envelope-fill me-2"></i>Open in Email Client
                </a>
                <a href="mailto:<?= e($lead['email']) ?>?subject=<?= urlencode('Your Quote from DBell Creations') ?>"
                   class="btn btn-outline-warning">
                    <i class="bi bi-file-earmark-text me-2"></i>Send Quote
                </a>
                <?php endif; ?>
                <?php if (!empty($lead['phone'])): ?>
                <a href="tel:<?= e($lead['phone']) ?>" class="btn btn-outline-success">
                    <i class="bi bi-telephone-fill me-2"></i>Call <?= e($lead['phone']) ?>
                </a>
                <?php endif; ?>
                <?php if (!empty($lead['website_url'])): ?>
                <a href="<?= url('audit') ?>?url=<?= urlencode($lead['website_url']) ?>" target="_blank"
                   class="btn btn-outline-info">
                    <i class="bi bi-search me-2"></i>Run Website Scan
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Email Log -->
        <?php if (!empty($emailLog)): ?>
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h6 class="fw-semibold mb-0"><i class="bi bi-clock-history me-2" style="color:#6222CC;"></i>Email History</h6>
                <span class="badge bg-light text-dark border"><?= count($emailLog) ?></span>
            </div>
            <div class="admin-card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($emailLog as $log): ?>
                    <li class="list-group-item px-3 py-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div style="min-width:0;flex:1;">
                                <div class="fw-600 text-truncate" style="font-size:.8rem;color:#1e0a3c;" title="<?= e($log['subject']) ?>">
                                    <?= e($log['subject']) ?>
                                </div>
                                <div class="text-muted" style="font-size:.7rem;">Stage <?= (int)$log['email_stage'] ?> · <?= timeAgo($log['sent_at']) ?></div>
                            </div>
                            <span class="badge ms-2 flex-shrink-0 <?= $log['status'] === 'sent' ? 'bg-success' : 'bg-danger' ?>" style="font-size:.65rem;">
                                <?= e($log['status']) ?>
                            </span>
                        </div>
                        <?php if (!empty($log['body'])): ?>
                        <details class="mt-1">
                            <summary style="font-size:.7rem;color:#6222CC;cursor:pointer;user-select:none;">View message</summary>
                            <pre style="white-space:pre-wrap;font-size:.73rem;color:#4b5563;background:#faf8ff;border-radius:6px;padding:8px;margin-top:6px;overflow:auto;max-height:160px;"><?= e($log['body']) ?></pre>
                        </details>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <!-- Audit History -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-semibold mb-0"><i class="bi bi-search me-2 text-info"></i>Audit History</h6>
                <span class="badge bg-light text-dark border"><?= count($audits ?? []) ?> scan<?= count($audits ?? []) !== 1 ? 's' : '' ?></span>
            </div>
            <div class="admin-card-body p-0">
                <?php if (!empty($audits)): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($audits as $audit): ?>
                    <li class="list-group-item px-3 py-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-600 text-truncate me-2" style="max-width:180px;" title="<?= e($audit['website_url']) ?>">
                                <?= e(parse_url($audit['website_url'], PHP_URL_HOST) ?: $audit['website_url']) ?>
                            </span>
                            <?php if ($audit['overall_score'] !== null):
                                $scoreClass = $audit['overall_score'] >= 70 ? 'success' : ($audit['overall_score'] >= 50 ? 'warning text-dark' : 'danger');
                            ?>
                            <span class="badge bg-<?= $scoreClass ?>" style="font-size:.72rem;"><?= $audit['overall_score'] ?>/100</span>
                            <?php else: ?>
                            <span class="badge bg-secondary" style="font-size:.72rem;"><?= e($audit['status']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <?php if (!empty($audit['report_token'])): ?>
                            <a href="<?= url('report/' . $audit['report_token']) ?>" class="small text-primary text-decoration-none" target="_blank">
                                <i class="bi bi-file-earmark-text me-1"></i>View Report
                            </a>
                            <?php else: ?>
                            <span class="small text-muted">No report</span>
                            <?php endif; ?>
                            <span class="text-muted" style="font-size:.7rem;"><?= timeAgo($audit['requested_at']) ?></span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="empty-state py-4">
                    <i class="bi bi-search"></i>
                    <p>No audits yet. <a href="<?= !empty($lead['website_url']) ? url('audit').'?url='.urlencode($lead['website_url']) : url('audit') ?>" target="_blank">Run one now →</a></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    // Template chips populate the compose form
    document.querySelectorAll('.tpl-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var subject = btn.getAttribute('data-subject') || '';
            var body    = btn.getAttribute('data-body')    || '';
            var subjectEl = document.getElementById('msgSubject');
            var bodyEl    = document.getElementById('msgBody');
            if (subjectEl) subjectEl.value = subject;
            if (bodyEl)    bodyEl.value    = body;
            // Scroll into compose area
            var form = document.getElementById('sendMsgForm');
            if (form) form.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });

    // Clear button
    var clearBtn = document.getElementById('clearMsgBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            var subjectEl = document.getElementById('msgSubject');
            var bodyEl    = document.getElementById('msgBody');
            if (subjectEl) subjectEl.value = '';
            if (bodyEl)    bodyEl.value    = '';
            if (subjectEl) subjectEl.focus();
        });
    }
})();
</script>

<!-- ── Breadcrumb ────────────────────────────── -->
<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
    <a href="<?= url('admin/leads') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
        <i class="bi bi-arrow-left me-1"></i>Back to Leads
    </a>
    <span class="text-muted" style="font-size:.82rem;"><i class="bi bi-chevron-right"></i></span>
    <span class="fw-600" style="font-size:.88rem;color:#1e0a3c;">
        <?= e($lead['contact_name'] ?: ($lead['email'] ?: 'Lead #' . $lead['id'])) ?>
    </span>
    <span class="badge bg-<?= $sc['bg'] ?> ms-1"><?= $sc['label'] ?></span>
</div>

<div class="row g-4">
    <!-- ── Left Column ───────────────────────── -->
    <div class="col-lg-7">

        <!-- Lead Header -->
        <div class="admin-card mb-4">
            <div class="admin-card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-800 flex-shrink-0"
                         style="width:60px;height:60px;font-size:1.5rem;background:linear-gradient(135deg,#6222CC,#9333ea);box-shadow:0 4px 14px rgba(98,34,204,0.35);">
                        <?= strtoupper(substr($lead['contact_name'] ?: ($lead['email'] ?: '?'), 0, 1)) ?>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="mb-1 fw-bold" style="color:#1e0a3c;">
                            <?= e($lead['contact_name'] ?: 'Anonymous Lead') ?>
                        </h5>
                        <div class="d-flex flex-wrap gap-2">
                            <?php if (!empty($lead['email'])): ?>
                            <a href="mailto:<?= e($lead['email']) ?>" class="text-decoration-none" style="color:#6222CC;font-size:.85rem;">
                                <i class="bi bi-envelope-fill me-1"></i><?= e($lead['email']) ?>
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($lead['phone'])): ?>
                            <a href="tel:<?= e($lead['phone']) ?>" class="text-decoration-none" style="color:#10b981;font-size:.85rem;">
                                <i class="bi bi-telephone-fill me-1"></i><?= e($lead['phone']) ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Detail grid -->
                <div class="row g-2">
                    <?php $detailItems = [
                        ['icon'=>'bi-building-fill','label'=>'Business','value'=>$lead['business_name'] ?: null,'color'=>'#6222CC'],
                        ['icon'=>'bi-globe','label'=>'Website','value'=>$lead['website_url'] ?: null,'color'=>'#06b6d4','link'=>$lead['website_url'] ?: ''],
                        ['icon'=>'bi-stars','label'=>'Service Interest','value'=>$lead['service_interest'] ?: null,'color'=>'#FBA504'],
                        ['icon'=>'bi-funnel-fill','label'=>'Source','value'=>ucfirst(str_replace('_',' ',$lead['source'] ?? 'unknown')),'color'=>'#10b981'],
                        ['icon'=>'bi-geo-alt-fill','label'=>'Source Page','value'=>$lead['source_page'] ?: null,'color'=>'#94a3b8'],
                        ['icon'=>'bi-calendar3','label'=>'Created','value'=>$lead['created_at'] ?: null,'color'=>'#6b5b8f'],
                    ];
                    foreach ($detailItems as $item): if (empty($item['value'])) continue; ?>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background:#faf8ff;">
                            <i class="bi <?= $item['icon'] ?>" style="color:<?= $item['color'] ?>;font-size:.88rem;flex-shrink:0;"></i>
                            <div style="min-width:0;">
                                <div style="font-size:.65rem;color:#6b5b8f;font-weight:600;text-transform:uppercase;letter-spacing:.05em;"><?= $item['label'] ?></div>
                                <?php if (!empty($item['link'])): ?>
                                <a href="<?= e($item['link']) ?>" target="_blank" rel="noopener"
                                   class="d-block text-truncate text-decoration-none" style="font-size:.82rem;font-weight:600;color:<?= $item['color'] ?>;max-width:180px;">
                                    <?= e(parse_url($item['value'], PHP_URL_HOST) ?: $item['value']) ?>
                                </a>
                                <?php else: ?>
                                <div class="text-truncate" style="font-size:.82rem;font-weight:600;color:#1e0a3c;max-width:180px;" title="<?= e($item['value']) ?>"><?= e($item['value']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Follow-up stage bar -->
                <div class="mt-3 p-3 rounded-2" style="background:#faf8ff;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span style="font-size:.75rem;font-weight:700;color:#6b5b8f;text-transform:uppercase;letter-spacing:.06em;">Follow-Up Stage</span>
                        <span style="font-size:.78rem;font-weight:600;color:#6222CC;">Stage <?= $fStage ?> of 4</span>
                    </div>
                    <div class="d-flex gap-2">
                        <?php $stageLabels = ['Initial','Follow-Up','Proposal','Close']; ?>
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                        <div style="flex:1;text-align:center;">
                            <div style="height:7px;border-radius:4px;background:<?= $i <= $fStage ? '#6222CC' : '#e8e2f4' ?>;margin-bottom:3px;"></div>
                            <div style="font-size:.62rem;color:<?= $i <= $fStage ? '#6222CC' : '#94a3b8' ?>;font-weight:600;"><?= $stageLabels[$i-1] ?></div>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <?php if (!empty($lead['next_follow_up_at'])): ?>
                    <div class="mt-2 text-muted" style="font-size:.75rem;">
                        <i class="bi bi-calendar-event me-1"></i>Next follow-up: <strong><?= e($lead['next_follow_up_at']) ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Update Status -->
        <div class="admin-card mb-4">
            <div class="admin-card-header"><h6 class="fw-semibold mb-0"><i class="bi bi-pencil-square me-2 text-primary"></i>Update Pipeline Status</h6></div>
            <div class="admin-card-body">
                <form method="POST" action="<?= url('admin/leads/' . $lead['id']) ?>" class="d-flex flex-wrap gap-2 align-items-center">
                    <?= csrf_field() ?>
                    <select name="status" class="form-select" style="max-width:220px;border-color:#d1c4e9;">
                        <?php foreach ($statusOptions as $val => $info): ?>
                        <option value="<?= $val ?>" <?= $curStatus === $val ? 'selected' : '' ?>><?= $info['label'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Update Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Internal Notes -->
        <div class="admin-card">
            <div class="admin-card-header"><h6 class="fw-semibold mb-0"><i class="bi bi-sticky-fill me-2 text-warning"></i>Internal Notes</h6></div>
            <div class="admin-card-body">
                <form method="POST" action="<?= url('admin/leads/' . $lead['id'] . '/note') ?>" class="mb-3">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <textarea name="note" class="form-control" rows="3"
                                  style="border-color:#d1c4e9;resize:vertical;"
                                  placeholder="Add an internal note, next steps, call summary…" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>Add Note
                    </button>
                </form>

                <?php if (!empty($notes)): ?>
                <ul class="timeline">
                    <?php foreach ($notes as $noteItem): ?>
                    <li class="timeline-item">
                        <div class="timeline-icon bg-primary-soft text-primary">
                            <i class="bi bi-person-fill" style="font-size:.75rem;"></i>
                        </div>
                        <div class="timeline-body">
                            <div class="timeline-title"><?= e($noteItem['admin_name'] ?? 'Admin') ?></div>
                            <div class="p-2 rounded-2 mt-1" style="background:#faf8ff;font-size:.83rem;line-height:1.65;">
                                <?= nl2br(e($noteItem['note'])) ?>
                            </div>
                            <div class="timeline-meta"><?= timeAgo($noteItem['created_at']) ?></div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="text-muted small fst-italic text-center py-2">No notes yet — add one above.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Right Column ──────────────────────── -->
    <div class="col-lg-5">

        <!-- Quick Actions -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h6 class="fw-semibold mb-0"><i class="bi bi-lightning-charge-fill me-2" style="color:#FBA504;"></i>Quick Actions</h6>
            </div>
            <div class="admin-card-body d-grid gap-2">
                <?php if (!empty($lead['email'])): ?>
                <a href="mailto:<?= e($lead['email']) ?>?subject=<?= urlencode('Following up — DBell Creations') ?>"
                   class="btn btn-primary">
                    <i class="bi bi-envelope-fill me-2"></i>Send Email
                </a>
                <a href="mailto:<?= e($lead['email']) ?>?subject=<?= urlencode('Your Quote from DBell Creations') ?>"
                   class="btn btn-outline-warning">
                    <i class="bi bi-file-earmark-text me-2"></i>Send Quote
                </a>
                <?php endif; ?>
                <?php if (!empty($lead['phone'])): ?>
                <a href="tel:<?= e($lead['phone']) ?>" class="btn btn-outline-success">
                    <i class="bi bi-telephone-fill me-2"></i>Call <?= e($lead['phone']) ?>
                </a>
                <?php endif; ?>
                <?php if (!empty($lead['website_url'])): ?>
                <a href="<?= url('audit') ?>?url=<?= urlencode($lead['website_url']) ?>" target="_blank"
                   class="btn btn-outline-info">
                    <i class="bi bi-search me-2"></i>Run Website Scan
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Audit History -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-semibold mb-0"><i class="bi bi-search me-2 text-info"></i>Audit History</h6>
                <span class="badge bg-light text-dark border"><?= count($audits ?? []) ?> scan<?= count($audits ?? []) !== 1 ? 's' : '' ?></span>
            </div>
            <div class="admin-card-body p-0">
                <?php if (!empty($audits)): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($audits as $audit): ?>
                    <li class="list-group-item px-3 py-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-600 text-truncate me-2" style="max-width:180px;" title="<?= e($audit['website_url']) ?>">
                                <?= e(parse_url($audit['website_url'], PHP_URL_HOST) ?: $audit['website_url']) ?>
                            </span>
                            <?php if ($audit['overall_score'] !== null):
                                $scoreClass = $audit['overall_score'] >= 70 ? 'success' : ($audit['overall_score'] >= 50 ? 'warning text-dark' : 'danger');
                            ?>
                            <span class="badge bg-<?= $scoreClass ?>" style="font-size:.72rem;"><?= $audit['overall_score'] ?>/100</span>
                            <?php else: ?>
                            <span class="badge bg-secondary" style="font-size:.72rem;"><?= e($audit['status']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <?php if (!empty($audit['report_token'])): ?>
                            <a href="<?= url('report/' . $audit['report_token']) ?>" class="small text-primary text-decoration-none" target="_blank">
                                <i class="bi bi-file-earmark-text me-1"></i>View Report
                            </a>
                            <?php else: ?>
                            <span class="small text-muted">No report</span>
                            <?php endif; ?>
                            <span class="text-muted" style="font-size:.7rem;"><?= timeAgo($audit['requested_at']) ?></span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="empty-state py-4">
                    <i class="bi bi-search"></i>
                    <p>No audits yet. <a href="<?= !empty($lead['website_url']) ? url('audit').'?url='.urlencode($lead['website_url']) : url('audit') ?>" target="_blank">Run one now →</a></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
