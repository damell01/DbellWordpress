<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin') ?> - <?= e($appName ?? 'VerityScan') ?></title>
    <link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-brand">
            <a href="<?= url('admin') ?>">
                <div class="admin-brand-icon"><i class="bi bi-graph-up-arrow"></i></div>
                    <span><?= e($appName ?? 'VerityScan') ?></span>
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
                    </a>
                </li>
                <li>
                    <a href="<?= url('admin/contacts') ?>" class="admin-nav-link <?= str_contains($title ?? '', 'Contact') ? 'active' : '' ?>">
                        <i class="bi bi-envelope-fill"></i> Contacts
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
                        <i class="bi bi-download"></i> Export Leads
                    </a>
                </li>
                <li>
                    <a href="<?= url('admin/export/scans') ?>" class="admin-nav-link">
                        <i class="bi bi-download"></i> Export Scans
                    </a>
                </li>
                <li>
                    <a href="<?= url('admin/export/contacts') ?>" class="admin-nav-link">
                        <i class="bi bi-download"></i> Export Contacts
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
            <a href="<?= url('admin/logout') ?>" class="admin-nav-link text-danger w-100" style="color:#ef4444!important;">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none border-0" id="sidebarToggle" style="width:36px;height:36px;padding:0;">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div>
                    <h4 class="mb-0"><?= e($title ?? 'Admin') ?></h4>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <?php $flashSuccess = get_flash('success'); $flashError = get_flash('error'); ?>
                <?php if ($flashSuccess): ?>
                <div class="alert alert-success alert-sm mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i><?= e($flashSuccess) ?>
                </div>
                <?php endif; ?>
                <?php if ($flashError): ?>
                <div class="alert alert-danger alert-sm mb-0"><?= e($flashError) ?></div>
                <?php endif; ?>
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary-soft text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:0.8rem;font-weight:700;flex-shrink:0;">
                        <?= strtoupper(substr(\App\Core\Session::get('user_name', 'A'), 0, 1)) ?>
                    </div>
                    <span class="text-muted small d-none d-md-inline"><?= e(\App\Core\Session::get('user_name', 'Admin')) ?></span>
                </div>
            </div>
        </header>

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
