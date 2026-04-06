<?php
namespace App\Services;

use App\Core\Database;
use PDO;

class UpgradeService {
    private Database $db;

    public function __construct(?Database $db = null) {
        $this->db = $db ?: Database::getInstance();
    }

    public function plan(): array {
        $pdo = $this->db->pdo();
        $actions = [];

        if (!$this->columnExists($pdo, 'audit_reports', 'screenshot_url')) {
            $actions[] = [
                'description' => 'Add audit_reports.screenshot_url column',
                'sql' => "ALTER TABLE `audit_reports` ADD COLUMN `screenshot_url` VARCHAR(2048) DEFAULT NULL AFTER `summary_text`",
            ];
        }

        if (!$this->columnExists($pdo, 'audit_reports', 'pagespeed_mobile_json')) {
            $actions[] = [
                'description' => 'Add audit_reports.pagespeed_mobile_json column',
                'sql' => "ALTER TABLE `audit_reports` ADD COLUMN `pagespeed_mobile_json` MEDIUMTEXT DEFAULT NULL AFTER `screenshot_url`",
            ];
        }

        if (!$this->columnExists($pdo, 'audit_reports', 'pagespeed_desktop_json')) {
            $actions[] = [
                'description' => 'Add audit_reports.pagespeed_desktop_json column',
                'sql' => "ALTER TABLE `audit_reports` ADD COLUMN `pagespeed_desktop_json` MEDIUMTEXT DEFAULT NULL AFTER `pagespeed_mobile_json`",
            ];
        }

        if (!$this->tableExists($pdo, 'audit_issue_feedback')) {
            $actions[] = [
                'description' => 'Create audit_issue_feedback table',
                'sql' => "CREATE TABLE `audit_issue_feedback` (
                    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `audit_report_id` INT UNSIGNED NOT NULL,
                    `audit_issue_id` INT UNSIGNED NOT NULL,
                    `feedback_type` ENUM('incorrect','helpful') NOT NULL DEFAULT 'helpful',
                    `notes` TEXT DEFAULT NULL,
                    `ip_address` VARCHAR(45) DEFAULT NULL,
                    `created_at` DATETIME NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_aif_report` (`audit_report_id`),
                    KEY `idx_aif_issue` (`audit_issue_id`),
                    KEY `idx_aif_type` (`feedback_type`),
                    CONSTRAINT `fk_aif_report` FOREIGN KEY (`audit_report_id`) REFERENCES `audit_reports` (`id`) ON DELETE CASCADE,
                    CONSTRAINT `fk_aif_issue` FOREIGN KEY (`audit_issue_id`) REFERENCES `audit_issues` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            ];
        }

        if ($this->tableExists($pdo, 'settings')) {
            $defaultSettings = [
                'google_maps_api_key' => '',
                'google_pagespeed_api_key' => '',
                'enable_google_places_lookup' => '1',
                'enable_pagespeed_lookup' => '1',
            ];

            foreach ($defaultSettings as $key => $value) {
                if (!$this->settingExists($pdo, $key)) {
                    $actions[] = [
                        'description' => "Insert default setting {$key}",
                        'sql' => "INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES (?, ?)",
                        'params' => [$key, $value],
                    ];
                }
            }
        }

        // ── contact_requests enhancements ────────────────────────────────────
        if ($this->tableExists($pdo, 'contact_requests')) {
            if (!$this->columnExists($pdo, 'contact_requests', 'source')) {
                $actions[] = [
                    'description' => 'Add contact_requests.source column',
                    'sql' => "ALTER TABLE `contact_requests` ADD COLUMN `source` VARCHAR(50) DEFAULT 'website'",
                ];
            }

            if (!$this->columnExists($pdo, 'contact_requests', 'website_url')) {
                $actions[] = [
                    'description' => 'Add contact_requests.website_url column',
                    'sql' => "ALTER TABLE `contact_requests` ADD COLUMN `website_url` VARCHAR(500) DEFAULT NULL",
                ];
            }

            if (!$this->columnExists($pdo, 'contact_requests', 'status')) {
                $actions[] = [
                    'description' => 'Add contact_requests.status column',
                    'sql' => "ALTER TABLE `contact_requests` ADD COLUMN `status` ENUM('new','read','replied','archived') NOT NULL DEFAULT 'new'",
                ];
            }

            if (!$this->columnExists($pdo, 'contact_requests', 'notes')) {
                $actions[] = [
                    'description' => 'Add contact_requests.notes column (admin internal notes)',
                    'sql' => "ALTER TABLE `contact_requests` ADD COLUMN `notes` TEXT DEFAULT NULL",
                ];
            }
        }

        // ── lead_notes table ─────────────────────────────────────────────────
        if (!$this->tableExists($pdo, 'lead_notes')) {
            $actions[] = [
                'description' => 'Create lead_notes table',
                'sql' => "CREATE TABLE `lead_notes` (
                    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `lead_id`    INT UNSIGNED NOT NULL,
                    `user_id`    INT UNSIGNED DEFAULT NULL,
                    `note`       TEXT NOT NULL,
                    `created_at` DATETIME NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_ln_lead` (`lead_id`),
                    CONSTRAINT `fk_ln_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            ];
        }

        // ── email_log table ───────────────────────────────────────────────────
        if (!$this->tableExists($pdo, 'email_log')) {
            $actions[] = [
                'description' => 'Create email_log table',
                'sql' => "CREATE TABLE `email_log` (
                    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `lead_id`         INT UNSIGNED NOT NULL,
                    `email_stage`     TINYINT UNSIGNED NOT NULL DEFAULT 1,
                    `recipient_email` VARCHAR(255) NOT NULL,
                    `subject`         VARCHAR(500) NOT NULL,
                    `body`            TEXT DEFAULT NULL,
                    `status`          ENUM('sent','failed') NOT NULL DEFAULT 'sent',
                    `sent_at`         DATETIME NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_el_lead`  (`lead_id`),
                    KEY `idx_el_stage` (`email_stage`),
                    KEY `idx_el_sent`  (`sent_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            ];
        } else {
            // Add body column to existing email_log tables that predate this column
            if (!$this->columnExists($pdo, 'email_log', 'body')) {
                $actions[] = [
                    'description' => 'Add email_log.body column',
                    'sql' => "ALTER TABLE `email_log` ADD COLUMN `body` TEXT DEFAULT NULL AFTER `subject`",
                ];
            }
        }

        // ── leads follow-up columns ───────────────────────────────────────────
        if ($this->tableExists($pdo, 'leads')) {
            if (!$this->columnExists($pdo, 'leads', 'service_interest')) {
                $actions[] = [
                    'description' => 'Add leads.service_interest column',
                    'sql' => "ALTER TABLE `leads` ADD COLUMN `service_interest` VARCHAR(50) DEFAULT NULL AFTER `status`",
                ];
            }
            if (!$this->columnExists($pdo, 'leads', 'source_page')) {
                $actions[] = [
                    'description' => 'Add leads.source_page column',
                    'sql' => "ALTER TABLE `leads` ADD COLUMN `source_page` VARCHAR(200) DEFAULT NULL AFTER `service_interest`",
                ];
            }
            if (!$this->columnExists($pdo, 'leads', 'follow_up_stage')) {
                $actions[] = [
                    'description' => 'Add leads.follow_up_stage column',
                    'sql' => "ALTER TABLE `leads` ADD COLUMN `follow_up_stage` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `source_page`",
                ];
            }
            if (!$this->columnExists($pdo, 'leads', 'last_contacted_at')) {
                $actions[] = [
                    'description' => 'Add leads.last_contacted_at column',
                    'sql' => "ALTER TABLE `leads` ADD COLUMN `last_contacted_at` DATETIME DEFAULT NULL AFTER `follow_up_stage`",
                ];
            }
            if (!$this->columnExists($pdo, 'leads', 'next_follow_up_at')) {
                $actions[] = [
                    'description' => 'Add leads.next_follow_up_at column',
                    'sql' => "ALTER TABLE `leads` ADD COLUMN `next_follow_up_at` DATETIME DEFAULT NULL AFTER `last_contacted_at`",
                ];
            }
        }

        return $actions;
    }

    public function run(bool $dryRun = false): array {
        $actions = $this->plan();

        if (!$dryRun) {
            foreach ($actions as $action) {
                $stmt = $this->db->pdo()->prepare($action['sql']);
                $stmt->execute($action['params'] ?? []);
            }
        }

        return [
            'dry_run' => $dryRun,
            'actions' => $actions,
            'changed' => !$dryRun && !empty($actions),
            'message' => empty($actions)
                ? 'Nothing to upgrade. Your database already has the latest changes.'
                : ($dryRun ? 'Dry run complete.' : 'Upgrade complete.'),
        ];
    }

    private function tableExists(PDO $pdo, string $table): bool {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?"
        );
        $stmt->execute([$table]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function columnExists(PDO $pdo, string $table, string $column): bool {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?"
        );
        $stmt->execute([$table, $column]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function settingExists(PDO $pdo, string $key): bool {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
