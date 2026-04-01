<?php
/**
 * SiteScope Installer
 * Browser-based setup wizard for database and admin account creation.
 */

define('BASE_PATH', dirname(__DIR__));
define('INSTALL_VERSION', '1.0.0');

// Block if already installed
if (file_exists(BASE_PATH . '/config/installed.lock')) {
    http_response_code(403);
    die('<html><body><h2>Already Installed</h2><p>This application is already installed. Delete <code>config/installed.lock</code> only if you need to reinstall.</p><a href="/">Go to Site</a></body></html>');
}

// Load helpers
require BASE_PATH . '/app/Core/helpers.php';

session_start();

$step    = (int)($_GET['step'] ?? 1);
$errors  = [];
$success = false;

// ─── Process Installation ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = (int)($_POST['step'] ?? 1);

    if ($step === 2) {
        // Step 2: Test DB connection
        $dbHost = trim($_POST['db_host'] ?? '');
        $dbPort = trim($_POST['db_port'] ?? '3306');
        $dbName = trim($_POST['db_name'] ?? '');
        $dbUser = trim($_POST['db_user'] ?? '');
        $dbPass = $_POST['db_pass'] ?? '';

        if (empty($dbHost) || empty($dbName) || empty($dbUser)) {
            $errors[] = 'Please fill in all required database fields.';
        } else {
            try {
                $dsn = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";
                $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                // Create database if it doesn't exist
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `{$dbName}`");
                $_SESSION['install_db'] = compact('dbHost', 'dbPort', 'dbName', 'dbUser', 'dbPass');
                $step = 3;
            } catch (PDOException $e) {
                $errors[] = 'Database connection failed: ' . $e->getMessage();
                $step = 2;
            }
        }
    } elseif ($step === 3) {
        // Step 3: Create tables
        if (empty($_SESSION['install_db'])) {
            $errors[] = 'Session expired. Please start over.';
            $step = 2;
        } else {
            $db = $_SESSION['install_db'];
            try {
                $pdo = new PDO(
                    "mysql:host={$db['dbHost']};port={$db['dbPort']};dbname={$db['dbName']};charset=utf8mb4",
                    $db['dbUser'], $db['dbPass'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                $sql = file_get_contents(__DIR__ . '/schema.sql');
                foreach (array_filter(array_map('trim', explode(';', $sql))) as $query) {
                    if ($query) $pdo->exec($query);
                }
                $step = 4;
            } catch (PDOException $e) {
                $errors[] = 'Table creation failed: ' . $e->getMessage();
                $step = 3;
            }
        }
    } elseif ($step === 4) {
        // Step 4: Create admin and write config
        if (empty($_SESSION['install_db'])) {
            $errors[] = 'Session expired. Please start over.';
            $step = 2;
        } else {
            $adminName  = trim($_POST['admin_name'] ?? '');
            $adminEmail = trim($_POST['admin_email'] ?? '');
            $adminPass  = $_POST['admin_password'] ?? '';
            $adminConf  = $_POST['admin_confirm'] ?? '';
$siteName   = trim($_POST['site_name'] ?? 'VerityScan');
            $siteUrl    = rtrim(trim($_POST['site_url'] ?? ''), '/');

            if (empty($adminName) || empty($adminEmail) || empty($adminPass)) {
                $errors[] = 'Please fill in all admin fields.';
                $step = 4;
            } elseif ($adminPass !== $adminConf) {
                $errors[] = 'Passwords do not match.';
                $step = 4;
            } elseif (strlen($adminPass) < 8) {
                $errors[] = 'Password must be at least 8 characters.';
                $step = 4;
            } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Please enter a valid email address.';
                $step = 4;
            } else {
                $db = $_SESSION['install_db'];
                try {
                    $pdo = new PDO(
                        "mysql:host={$db['dbHost']};port={$db['dbPort']};dbname={$db['dbName']};charset=utf8mb4",
                        $db['dbUser'], $db['dbPass'],
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );
                    // Insert admin user
                    $hash = password_hash($adminPass, PASSWORD_BCRYPT);
                    $pdo->prepare("INSERT INTO users (name, email, password_hash, role, created_at, updated_at) VALUES (?, ?, ?, 'admin', NOW(), NOW())")
                        ->execute([$adminName, $adminEmail, $hash]);

                    // Insert default settings
                    $settings = [
                        ['site_name', $siteName],
                        ['hero_headline', 'Find Out What\'s Holding Your Website Back'],
                        ['hero_subheadline', 'Get a free, instant audit of your website – SEO, accessibility, performance, and conversion issues revealed in seconds.'],
                        ['cta_text', 'Need help fixing these issues? Contact us today.'],
                        ['cta_subtext', 'We can improve your website and help you get more leads.'],
                        ['rate_limit_audits', '5'],
                        ['rate_limit_window', '3600'],
                        ['contact_email', $adminEmail],
                        ['require_email_for_report', '0'],
                        ['screenshot_provider', 'mshots'],
                        ['screenshot_api_url', ''],
                        ['screenshot_verify', '0'],
                    ];
                    $settingStmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
                    foreach ($settings as $s) $settingStmt->execute($s);

                    // Write .env file
                    $appKey = bin2hex(random_bytes(16));
                    $envContent = <<<ENV
APP_NAME={$siteName}
APP_URL={$siteUrl}
APP_ENV=production
APP_KEY={$appKey}

DB_HOST={$db['dbHost']}
DB_PORT={$db['dbPort']}
DB_NAME={$db['dbName']}
DB_USER={$db['dbUser']}
DB_PASS={$db['dbPass']}

MAIL_DRIVER=mail
MAIL_FROM={$adminEmail}
MAIL_FROM_NAME={$siteName}
SMTP_HOST=
SMTP_PORT=587
SMTP_USER=
SMTP_PASS=
SMTP_ENCRYPTION=tls

ADMIN_EMAIL={$adminEmail}

RATE_LIMIT_AUDITS=5
RATE_LIMIT_WINDOW=3600

SESSION_LIFETIME=7200
ENV;
                    file_put_contents(BASE_PATH . '/.env', $envContent);

                    // Write installed lock
                    file_put_contents(BASE_PATH . '/config/installed.lock', date('Y-m-d H:i:s'));

                    // Clear session
                    unset($_SESSION['install_db']);
                    $step = 5;
                    $success = true;
                } catch (PDOException $e) {
                    $errors[] = 'Setup failed: ' . $e->getMessage();
                    $step = 4;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VerityScan Installer</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
body { background: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
.installer-wrap { max-width: 600px; margin: 3rem auto; padding: 0 1rem; }
.installer-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.1); overflow: hidden; }
.installer-header { background: linear-gradient(135deg, #0f172a, #2563eb); color: #fff; padding: 2rem; text-align: center; }
.installer-body { padding: 2rem; }
.step-indicator { display: flex; justify-content: center; gap: 0.5rem; margin-bottom: 2rem; }
.step-dot { width: 10px; height: 10px; border-radius: 50%; background: #e2e8f0; }
.step-dot.active { background: #2563eb; }
.step-dot.done { background: #10b981; }
</style>
</head>
<body>
<div class="installer-wrap">
    <div class="installer-card">
        <div class="installer-header">
            <i class="bi bi-graph-up-arrow fs-2 mb-2 d-block"></i>
                    <h3 class="mb-1">VerityScan Installer</h3>
            <p class="mb-0 opacity-75 small">Version <?= INSTALL_VERSION ?></p>
        </div>
        <div class="installer-body">

            <!-- Step Indicators -->
            <div class="step-indicator">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <div class="step-dot <?= $i < $step ? 'done' : ($i === $step ? 'active' : '') ?>"></div>
                <?php endfor; ?>
            </div>

            <!-- Errors -->
            <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if ($step === 1): ?>
            <!-- Step 1: Welcome -->
            <h5 class="fw-bold mb-3">Welcome!</h5>
                    <p class="text-muted">This wizard will guide you through the VerityScan installation. Before you begin, make sure you have:</p>
            <ul class="text-muted small mb-4">
                <li>PHP 8.0+ installed</li>
                <li>A MySQL database name, username, and password ready</li>
                <li>Write permissions on the installation directory</li>
            </ul>
            <form method="POST"><input type="hidden" name="step" value="2">
                <button type="submit" class="btn btn-primary w-100">Start Installation →</button>
            </form>

            <?php elseif ($step === 2): ?>
            <!-- Step 2: Database -->
            <h5 class="fw-bold mb-3"><i class="bi bi-database me-2 text-primary"></i>Database Configuration</h5>
            <form method="POST">
                <input type="hidden" name="step" value="2">
                <div class="mb-3">
                    <label class="form-label">Database Host <span class="text-danger">*</span></label>
                    <input type="text" name="db_host" class="form-control" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Database Port</label>
                    <input type="text" name="db_port" class="form-control" value="<?= htmlspecialchars($_POST['db_port'] ?? '3306') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Database Name <span class="text-danger">*</span></label>
                    <input type="text" name="db_name" class="form-control" value="<?= htmlspecialchars($_POST['db_name'] ?? 'sitescope') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Database Username <span class="text-danger">*</span></label>
                    <input type="text" name="db_user" class="form-control" value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Database Password</label>
                    <input type="password" name="db_pass" class="form-control" placeholder="Leave empty if no password">
                </div>
                <button type="submit" class="btn btn-primary w-100">Test Connection & Continue →</button>
            </form>

            <?php elseif ($step === 3): ?>
            <!-- Step 3: Create Tables -->
            <h5 class="fw-bold mb-3"><i class="bi bi-table me-2 text-primary"></i>Create Database Tables</h5>
            <p class="text-muted">Click the button below to create all required database tables.</p>
            <?php if (!file_exists(__DIR__ . '/schema.sql')): ?>
            <div class="alert alert-danger">schema.sql not found in the install/ directory.</div>
            <?php else: ?>
            <form method="POST">
                <input type="hidden" name="step" value="3">
                <button type="submit" class="btn btn-primary w-100">Create Tables & Continue →</button>
            </form>
            <?php endif; ?>

            <?php elseif ($step === 4): ?>
            <!-- Step 4: Admin Account -->
            <h5 class="fw-bold mb-3"><i class="bi bi-person-gear me-2 text-primary"></i>Admin Account & Settings</h5>
            <form method="POST">
                <input type="hidden" name="step" value="4">
                <div class="mb-3">
                    <label class="form-label">Site Name</label>
                                <input type="text" name="site_name" class="form-control" value="VerityScan" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Site URL</label>
                    <input type="url" name="site_url" class="form-control" value="https://yourdomain.com" required>
                </div>
                <hr>
                <div class="mb-3">
                    <label class="form-label">Admin Name <span class="text-danger">*</span></label>
                    <input type="text" name="admin_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Admin Email <span class="text-danger">*</span></label>
                    <input type="email" name="admin_email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Admin Password <span class="text-danger">*</span></label>
                    <input type="password" name="admin_password" class="form-control" required minlength="8">
                </div>
                <div class="mb-4">
                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" name="admin_confirm" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Finish Installation →</button>
            </form>

            <?php elseif ($step === 5): ?>
            <!-- Step 5: Success -->
            <div class="text-center py-3">
                <i class="bi bi-check-circle-fill text-success" style="font-size:4rem"></i>
                <h4 class="fw-bold mt-3 mb-2">Installation Complete!</h4>
                        <p class="text-muted mb-4">VerityScan has been successfully installed. The installer has been locked for security.</p>
                <div class="alert alert-info text-start mb-4" role="alert">
                    <h6 class="alert-heading fw-bold"><i class="bi bi-key-fill me-2"></i>Your Admin Login Details</h6>
                    <hr>
                    <p class="mb-1 small"><strong>Admin URL:</strong> <code><?= htmlspecialchars(rtrim($_POST['site_url'] ?? '/', '/') . '/admin') ?></code></p>
                    <p class="mb-1 small"><strong>Email:</strong> <code><?= htmlspecialchars($_POST['admin_email'] ?? '') ?></code></p>
                    <p class="mb-0 small"><strong>Password:</strong> the password you entered in the previous step.</p>
                    <hr>
                    <p class="mb-0 small text-muted">
                        <i class="bi bi-file-earmark-text me-1"></i>
                        See <strong>FIRST_LOGIN.md</strong> in the root of your installation for full setup guidance and Hostinger deployment steps.
                    </p>
                </div>
                <div class="d-grid gap-2">
                    <a href="<?= htmlspecialchars(rtrim($_POST['site_url'] ?? '/', '/') . '/') ?>" class="btn btn-primary">Visit Your Site →</a>
                    <a href="<?= htmlspecialchars(rtrim($_POST['site_url'] ?? '/', '/') . '/admin') ?>" class="btn btn-outline-primary">Go to Admin Dashboard</a>
                </div>
                <p class="text-muted small mt-3">Remember to remove the <code>install/</code> directory after verifying your installation.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <p class="text-center text-muted small mt-3">SiteScope v<?= INSTALL_VERSION ?></p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
