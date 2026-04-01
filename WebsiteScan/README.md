# SiteScope - Free Website Audit & Lead Generation Platform

**SiteScope** is a production-ready free website audit tool designed for agencies and freelancers. It attracts small business clients by offering free website audits, then converts them into service leads.

## Features

- Free Website Audit – SEO, Accessibility, Conversion, Technical, and Local Readiness checks
- Polished Reports – Scored audit reports with actionable recommendations, including **website screenshots**
- Lead Capture – Collect contact details with every audit
- Email Notifications – Notify admin and user on new leads
- Secure Admin Dashboard – Manage leads, scans, settings, exports
- Easy Installer – Browser-based setup wizard for Hostinger

## Requirements

- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Apache with mod_rewrite
- cURL, DOM, SimpleXML extensions

## Installation

1. Upload all files to your hosting directory
2. Point domain to the `public/` folder
3. Navigate to `https://yourdomain.com/install/`
4. Fill in database credentials and admin account details
5. Installation locks automatically when complete

> **First time?** See [FIRST_LOGIN.md](FIRST_LOGIN.md) for your admin login details and a full Hostinger deployment guide.

## Cron Jobs (Optional)

SiteScope runs audits synchronously — no cron job required for standard installs. If you add a custom background queue script in the future:

```
*/5 * * * * /usr/bin/php /home/username/public_html/sitescope/process_queue.php
```

## Upgrades

For existing installs, run the CLI upgrade script after uploading new files:

```bash
php install/upgrade.php
```

Preview the upgrade without changing anything:

```bash
php install/upgrade.php --dry-run
```

## Security

- Passwords hashed with password_hash()
- CSRF protection on all forms
- Prepared statements throughout
- Rate limiting on audit submissions and login
- Installer locked after first use
