-- SiteScope Database Schema
-- Version 1.0.0

SET FOREIGN_KEY_CHECKS = 0;

-- ── Users ──────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(100) NOT NULL,
    `email`         VARCHAR(255) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role`          ENUM('admin','user') NOT NULL DEFAULT 'user',
    `created_at`    DATETIME NOT NULL,
    `updated_at`    DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Settings ───────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `settings` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key`   VARCHAR(100) NOT NULL,
    `setting_value` TEXT,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Leads ──────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `leads` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `website_url`   VARCHAR(500) NOT NULL,
    `business_name` VARCHAR(200) DEFAULT NULL,
    `contact_name`  VARCHAR(150) DEFAULT NULL,
    `email`         VARCHAR(255) DEFAULT NULL,
    `phone`         VARCHAR(50) DEFAULT NULL,
    `notes`         TEXT DEFAULT NULL,
    `source`        VARCHAR(50) DEFAULT 'audit',
    `status`        ENUM('new','reviewed','contacted','quote_sent','closed_won','closed_lost') NOT NULL DEFAULT 'new',
    `created_at`    DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_leads_email`  (`email`),
    KEY `idx_leads_status` (`status`),
    KEY `idx_leads_created`(`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Audit Requests ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `audit_requests` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `lead_id`        INT UNSIGNED DEFAULT NULL,
    `website_url`    VARCHAR(500) NOT NULL,
    `normalized_url` VARCHAR(500) NOT NULL,
    `status`         ENUM('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
    `ip_address`     VARCHAR(45) NOT NULL,
    `user_agent`     VARCHAR(500) DEFAULT NULL,
    `requested_at`   DATETIME NOT NULL,
    `completed_at`   DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_ar_lead`    (`lead_id`),
    KEY `idx_ar_status`  (`status`),
    KEY `idx_ar_ip`      (`ip_address`),
    KEY `idx_ar_requested`(`requested_at`),
    CONSTRAINT `fk_ar_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Audit Reports ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `audit_reports` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `audit_request_id` INT UNSIGNED NOT NULL,
    `report_token`     VARCHAR(64) NOT NULL,
    `overall_score`    TINYINT UNSIGNED DEFAULT 0,
    `summary_text`     TEXT DEFAULT NULL,
    `screenshot_url`   VARCHAR(2048) DEFAULT NULL,
    `pagespeed_mobile_json` MEDIUMTEXT DEFAULT NULL,
    `pagespeed_desktop_json` MEDIUMTEXT DEFAULT NULL,
    `created_at`       DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_report_token` (`report_token`),
    KEY `idx_report_request` (`audit_request_id`),
    CONSTRAINT `fk_report_request` FOREIGN KEY (`audit_request_id`) REFERENCES `audit_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Audit Scores ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `audit_scores` (
    `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `audit_report_id`     INT UNSIGNED NOT NULL,
    `seo_score`           TINYINT UNSIGNED DEFAULT 0,
    `accessibility_score` TINYINT UNSIGNED DEFAULT 0,
    `conversion_score`    TINYINT UNSIGNED DEFAULT 0,
    `technical_score`     TINYINT UNSIGNED DEFAULT 0,
    `local_score`         TINYINT UNSIGNED DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_scores_report` (`audit_report_id`),
    CONSTRAINT `fk_scores_report` FOREIGN KEY (`audit_report_id`) REFERENCES `audit_reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Audit Issues ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `audit_issues` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `audit_report_id` INT UNSIGNED NOT NULL,
    `category`        VARCHAR(50) NOT NULL,
    `severity`        ENUM('critical','high','medium','low','info') NOT NULL DEFAULT 'info',
    `code`            VARCHAR(100) NOT NULL,
    `title`           VARCHAR(300) NOT NULL,
    `explanation`     TEXT DEFAULT NULL,
    `why_it_matters`  TEXT DEFAULT NULL,
    `how_to_fix`      TEXT DEFAULT NULL,
    `business_impact` TEXT DEFAULT NULL,
    `detected_value`  VARCHAR(500) DEFAULT NULL,
    `created_at`      DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_issues_report`   (`audit_report_id`),
    KEY `idx_issues_severity` (`severity`),
    KEY `idx_issues_category` (`category`),
    KEY `idx_issues_code`     (`code`),
    CONSTRAINT `fk_issues_report` FOREIGN KEY (`audit_report_id`) REFERENCES `audit_reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Contact Requests ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `contact_requests` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `lead_id`         INT UNSIGNED DEFAULT NULL,
    `audit_report_id` INT UNSIGNED DEFAULT NULL,
    `name`            VARCHAR(150) NOT NULL,
    `email`           VARCHAR(255) NOT NULL,
    `phone`           VARCHAR(50) DEFAULT NULL,
    `company`         VARCHAR(200) DEFAULT NULL,
    `message`         TEXT DEFAULT NULL,
    `service_type`    VARCHAR(100) DEFAULT NULL,
    `source`          VARCHAR(50) DEFAULT 'website',
    `website_url`     VARCHAR(500) DEFAULT NULL,
    `status`          ENUM('new','read','replied','archived') NOT NULL DEFAULT 'new',
    `notes`           TEXT DEFAULT NULL,
    `created_at`      DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_cr_email` (`email`),
    KEY `idx_cr_created` (`created_at`),
    KEY `idx_cr_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Lead Notes ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lead_notes` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `lead_id`    INT UNSIGNED NOT NULL,
    `user_id`    INT UNSIGNED DEFAULT NULL,
    `note`       TEXT NOT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_ln_lead` (`lead_id`),
    CONSTRAINT `fk_ln_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Admin Activity Logs ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `admin_activity_logs` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED DEFAULT NULL,
    `action`     VARCHAR(100) NOT NULL,
    `details`    TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_aal_action`  (`action`),
    KEY `idx_aal_user`    (`user_id`),
    KEY `idx_aal_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Report Views ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `report_views` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `audit_report_id` INT UNSIGNED NOT NULL,
    `viewed_at`       DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_rv_report` (`audit_report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `audit_issue_feedback` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `audit_report_id` INT UNSIGNED NOT NULL,
    `audit_issue_id`  INT UNSIGNED NOT NULL,
    `feedback_type`   ENUM('incorrect','helpful') NOT NULL DEFAULT 'helpful',
    `notes`           TEXT DEFAULT NULL,
    `ip_address`      VARCHAR(45) DEFAULT NULL,
    `created_at`      DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_aif_report` (`audit_report_id`),
    KEY `idx_aif_issue` (`audit_issue_id`),
    KEY `idx_aif_type` (`feedback_type`),
    CONSTRAINT `fk_aif_report` FOREIGN KEY (`audit_report_id`) REFERENCES `audit_reports` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_aif_issue` FOREIGN KEY (`audit_issue_id`) REFERENCES `audit_issues` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ── Screenshot column migration (for existing installs upgrading from < 1.1) ──
-- Fresh installs already have this column from the CREATE TABLE above.
-- Running this on an existing install adds the column safely (IF NOT EXISTS).
ALTER TABLE `audit_reports`
    ADD COLUMN IF NOT EXISTS `screenshot_url` VARCHAR(2048) DEFAULT NULL AFTER `summary_text`;
ALTER TABLE `audit_reports`
    ADD COLUMN IF NOT EXISTS `pagespeed_mobile_json` MEDIUMTEXT DEFAULT NULL AFTER `screenshot_url`;
ALTER TABLE `audit_reports`
    ADD COLUMN IF NOT EXISTS `pagespeed_desktop_json` MEDIUMTEXT DEFAULT NULL AFTER `pagespeed_mobile_json`;

CREATE TABLE IF NOT EXISTS `audit_issue_feedback` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `audit_report_id` INT UNSIGNED NOT NULL,
    `audit_issue_id`  INT UNSIGNED NOT NULL,
    `feedback_type`   ENUM('incorrect','helpful') NOT NULL DEFAULT 'helpful',
    `notes`           TEXT DEFAULT NULL,
    `ip_address`      VARCHAR(45) DEFAULT NULL,
    `created_at`      DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_aif_report` (`audit_report_id`),
    KEY `idx_aif_issue` (`audit_issue_id`),
    KEY `idx_aif_type` (`feedback_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Lead Follow-Up Fields ─────────────────────────────────────────────────────
ALTER TABLE `leads`
    ADD COLUMN IF NOT EXISTS `service_interest` VARCHAR(50) DEFAULT NULL AFTER `status`,
    ADD COLUMN IF NOT EXISTS `source_page` VARCHAR(200) DEFAULT NULL AFTER `service_interest`,
    ADD COLUMN IF NOT EXISTS `follow_up_stage` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `source_page`,
    ADD COLUMN IF NOT EXISTS `last_contacted_at` DATETIME DEFAULT NULL AFTER `follow_up_stage`,
    ADD COLUMN IF NOT EXISTS `next_follow_up_at` DATETIME DEFAULT NULL AFTER `last_contacted_at`;

-- ── Email Log ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `email_log` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `lead_id`         INT UNSIGNED NOT NULL,
    `email_stage`     TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `recipient_email` VARCHAR(255) NOT NULL,
    `subject`         VARCHAR(500) NOT NULL,
    `status`          ENUM('sent','failed') NOT NULL DEFAULT 'sent',
    `sent_at`         DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_el_lead`    (`lead_id`),
    KEY `idx_el_stage`   (`email_stage`),
    KEY `idx_el_sent`    (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
