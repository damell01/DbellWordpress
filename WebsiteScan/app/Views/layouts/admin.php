<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin') ?> — DBell CRM</title>
    <link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="admin-body">

<!-- Mobile sidebar overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="admin-wrapper">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-brand">
            <a href="<?= url('admin') ?>">
                <div class="admin-brand-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                <div>
                    <div style="font-size:1rem;font-weight:800;letter-spacing:-0.3px;">DBell CRM</div>
                    <div class="admin-brand-tagline">Creations Dashboard</div>
                </div>
            </a>
        </div>
        <nav class="admin-nav">
            <ul class="list-unstyled mb-0">
                <li>
                    <a href="<?= url('admin') ?>" class="admin-nav-link <?= ($title === 'Dashboard') ? 'active' : '' ?>">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>

                <li><span class="nav-section-label">CRM</span></li>
                <li>
                    <a href="<?= url('admin/leads') ?>" class="admin-nav-link <?= str_contains($title ?? '', 'Lead') ? 'active' : '' ?>">
                        <i class="bi bi-people-fill"></i> Leads
                        <?php
                        try {
                            $db = \App\Core\Database::getInstance();
                            $newLeads = (int)$db->scalar("SELECT COUNT(*) FROM leads WHERE status = 'new'");
                            if ($newLeads > 0): ?>
                            <span class="nav-badge"><?= $newLeads ?></span>
                            <?php endif;
                        } catch (\Throwable $e) { /* ignore */ }
                        ?>
                    </a>
                </li>
                <li>
                    <a href="<?= url('admin/contacts') ?>" class="admin-nav-link <?= str_contains($title ?? '', 'Contact') ? 'active' : '' ?>">
                        <i class="bi bi-envelope-fill"></i> Contact Inbox
                        <?php
                        try {
                            $db = \App\Core\Database::getInstance();
                            $newContacts = (int)$db->scalar("SELECT COUNT(*) FROM contact_requests WHERE status = 'new'");
                            if ($newContacts > 0): ?>
                            <span class="nav-badge"><?= $newContacts ?></span>
                            <?php endif;
                        } catch (\Throwable $e) { /* ignore */ }
                        ?>
                    </a>
                </li>

                <li><span class="nav-section-label">Audits</span></li>
                <li>
                    <a href="<?= url('admin/scans') ?>" class="admin-nav-link <?= str_contains($title ?? '', 'Scan') ? 'active' : '' ?>">
                        <i class="bi bi-search"></i> All Scans
                    </a>
                </li>

                <li><span class="nav-section-label">Export</span></li>
                <li>
                    <a href="<?= url('admin/export/leads') ?>" class="admin-nav-link">
                        <i class="bi bi-filetype-csv"></i> Export Leads
                    </a>
                </li>
                <li>
                    <a href="<?= url('admin/export/scans') ?>" class="admin-nav-link">
                        <i class="bi bi-filetype-csv"></i> Export Scans
                    </a>
                </li>
                <li>
                    <a href="<?= url('admin/export/contacts') ?>" class="admin-nav-link">
                        <i class="bi bi-filetype-csv"></i> Export Contacts
                    </a>
                </li>

                <li class="nav-divider"></li>
                <li>
                    <a href="<?= url('admin/settings') ?>" class="admin-nav-link <?= str_contains($title ?? '', 'Setting') ? 'active' : '' ?>">
                        <i class="bi bi-gear-fill"></i> Settings
                    </a>
                </li>
                <li>
                    <a href="<?= url('/') ?>" class="admin-nav-link" target="_blank" rel="noopener">
                        <i class="bi bi-box-arrow-up-right"></i> View Site
                    </a>
                </li>
            </ul>
        </nav>
        <div class="admin-sidebar-footer">
            <div class="d-flex align-items-center gap-2 px-1 mb-2">
                <div class="topbar-avatar" style="width:28px;height:28px;font-size:0.72rem;">
                    <?= strtoupper(substr(\App\Core\Session::get('user_name', 'A'), 0, 1)) ?>
                </div>
                <div style="min-width:0;">
                    <div style="font-size:0.78rem;font-weight:600;color:rgba(255,255,255,0.85);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <?= e(\App\Core\Session::get('user_name', 'Admin')) ?>
                    </div>
                    <div style="font-size:0.67rem;color:rgba(255,255,255,0.35);">Administrator</div>
                </div>
            </div>
            <a href="<?= url('admin/logout') ?>" class="admin-nav-link" style="color:#f87171!important;">
                <i class="bi bi-box-arrow-right"></i> Sign Out
            </a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm border-0 d-lg-none" id="sidebarToggle"
                        style="width:38px;height:38px;padding:0;background:rgba(98,34,204,0.08);border-radius:9px;color:#6222CC;">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <h4 class="mb-0"><?= e($title ?? 'Admin') ?></h4>
            </div>
            <div class="topbar-actions">
                <?php $flashSuccess = get_flash('success'); $flashError = get_flash('error'); ?>
                <?php if ($flashSuccess): ?>
                <div class="alert alert-success alert-sm mb-0 d-flex align-items-center gap-2 d-none d-md-flex">
                    <i class="bi bi-check-circle-fill"></i><?= e($flashSuccess) ?>
                </div>
                <?php endif; ?>
                <?php if ($flashError): ?>
                <div class="alert alert-danger alert-sm mb-0 d-none d-md-flex"><?= e($flashError) ?></div>
                <?php endif; ?>
                <a href="<?= url('admin/contacts') ?>" class="btn btn-sm position-relative" title="Contact Inbox"
                   style="width:38px;height:38px;padding:0;background:rgba(98,34,204,0.07);border-radius:9px;color:#6222CC;border:none;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-envelope-fill" style="font-size:0.95rem;"></i>
                    <?php
                    try {
                        $db = \App\Core\Database::getInstance();
                        $unread = (int)$db->scalar("SELECT COUNT(*) FROM contact_requests WHERE status = 'new'");
                        if ($unread > 0): ?>
                    <span class="position-absolute top-0 end-0 badge rounded-pill"
                          style="background:#FBA504;font-size:0.6rem;min-width:16px;height:16px;padding:0 3px;display:flex;align-items:center;justify-content:center;translate:-2px 2px;">
                        <?= $unread ?>
                    </span>
                    <?php endif;
                    } catch (\Throwable $e) { /* ignore */ } ?>
                </a>
                <div class="topbar-avatar">
                    <?= strtoupper(substr(\App\Core\Session::get('user_name', 'A'), 0, 1)) ?>
                </div>
                <span class="text-muted small d-none d-md-inline" style="font-size:0.8rem;">
                    <?= e(\App\Core\Session::get('user_name', 'Admin')) ?>
                </span>
            </div>
        </header>

        <?php if ($flashSuccess): ?>
        <div class="crm-toast success show" id="crmToast">
            <i class="bi bi-check-circle-fill me-2" style="color:#10b981;"></i><?= e($flashSuccess) ?>
        </div>
        <?php elseif ($flashError): ?>
        <div class="crm-toast danger show" id="crmToast">
            <i class="bi bi-exclamation-circle-fill me-2" style="color:#ef4444;"></i><?= e($flashError) ?>
        </div>
        <?php endif; ?>

        <div class="admin-content">
            <?= $content ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
<script src="<?= asset('js/admin.js') ?>"></script>
</body>
</html>
