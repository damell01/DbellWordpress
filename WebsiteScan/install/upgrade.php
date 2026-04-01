<?php
/**
 * SiteScope CLI upgrade script
 *
 * Usage:
 *   php install/upgrade.php
 *   php install/upgrade.php --dry-run
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $base = BASE_PATH . '/app/';
    if (str_starts_with($class, $prefix)) {
        $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
        $file = $base . $relative . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

require BASE_PATH . '/app/Core/helpers.php';
require BASE_PATH . '/config/app.php';

use App\Services\UpgradeService;

$argv = $_SERVER['argv'] ?? [];
$dryRun = in_array('--dry-run', $argv, true);

function out(string $message): void
{
    fwrite(STDOUT, $message . PHP_EOL);
}

function err(string $message): void
{
    fwrite(STDERR, $message . PHP_EOL);
}

out('SiteScope upgrade starting...');
out('Environment: ' . (string) env('APP_ENV', 'production'));
out('Database: ' . (string) env('DB_NAME', ''));
if ($dryRun) {
    out('Dry run enabled. No changes will be written.');
}

try {
    $upgrade = new UpgradeService();
    $result = $upgrade->run($dryRun);

    foreach ($result['actions'] as $action) {
        out('- ' . $action['description']);
    }

    out($result['message']);
    out('You can now redeploy and test /admin/settings and report feedback.');
    exit(0);
} catch (Throwable $e) {
    err('Upgrade failed: ' . $e->getMessage());
    exit(1);
}
