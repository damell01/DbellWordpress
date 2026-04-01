# SiteScope — First Login & Hostinger Deployment Guide

This document is your quick-reference for accessing the admin dashboard after installation and for deploying SiteScope on Hostinger shared or VPS hosting.

---

## Your Admin Login

After completing the install wizard your credentials are:

| Field    | Value                                              |
|----------|----------------------------------------------------|
| **URL**  | `https://yourdomain.com/admin`                     |
| **Email**| The email you entered during installation          |
| **Password** | The password you entered during installation   |

> **Tip:** If you forget your password, use the "Forgot Password" link on the login page.  
> An email will be sent to the admin email address stored in Settings.

---

## Hostinger Deployment — Step-by-Step

### Requirements

- PHP 8.0 or higher
- MySQL 5.7+ / MariaDB 10.3+
- Apache with `mod_rewrite` enabled
- PHP extensions: `pdo_mysql`, `curl`, `dom`, `mbstring`, `openssl`

---

### Option A – File Manager Upload (Shared Hosting)

1. **Download** a ZIP of the SiteScope repository from GitHub.
2. **Log in** to your Hostinger hPanel.
3. Navigate to **Files → File Manager**.
4. Open `public_html` (or your subdomain folder).
5. Upload and **extract** the ZIP into a temporary folder, e.g. `public_html/sitescope-tmp/`.
6. Move the contents:
   - Copy everything **except** `public/` to `public_html/sitescope/` (or your preferred path).
   - Copy the **contents of** `public/` directly into `public_html/` (or your domain's web root).
7. Ensure `.htaccess` is present in both `public_html/` and the app root.

> **Document-root tip:** Point your domain's web root to the `public/` folder.  
> In hPanel → Domains → your domain → Manage → Document Root → set to `public_html/sitescope/public` (adjust path as needed).

---

### Option B – SSH / Terminal (VPS or Business Hosting)

```bash
# 1. SSH into your server
ssh u123456789@yourdomain.com

# 2. Navigate to your web root
cd ~/public_html

# 3. Clone the repository (or upload files via SFTP and skip this step)
git clone https://github.com/YOUR_USERNAME/YOUR_REPO.git sitescope

# 4. Set the document root to public/
#    (or create a symlink if your host expects index.php directly in public_html)
ln -s ~/public_html/sitescope/public ~/public_html/sitescope-public

# 5. Set correct permissions
chmod -R 755 ~/public_html/sitescope
chmod -R 775 ~/public_html/sitescope/storage

# 6. Run the installer
#    Open https://yourdomain.com/install/ in your browser
```

---

### 3. Create the MySQL Database

1. In hPanel go to **Databases → MySQL Databases**.
2. Create a new database, e.g. `u123456_sitescope`.
3. Create a database user with a strong password.
4. **Add the user to the database** with all privileges.
5. Note down: host (`localhost`), database name, username, and password — you will need these during installation.

---

### 4. Run the Install Wizard

1. Open `https://yourdomain.com/install/` in your browser.
2. Follow the 5-step wizard:
   - **Step 1** – Welcome
   - **Step 2** – Enter DB credentials and test connection
   - **Step 3** – Create tables (automatic)
   - **Step 4** – Set site name, URL, and admin account
   - **Step 5** – Installation complete ✓
3. After completion you will see your admin URL and email on screen.

---

### 5. Post-Install Checklist

- [ ] Log in to `/admin` and verify the dashboard loads.
- [ ] Go to **Admin → Settings** and set your site name, contact email, and CTA text.
- [ ] Run a test audit from the homepage.
- [ ] Configure email (SMTP) in Admin → Settings → Email.
- [ ] *(Optional)* Remove or rename the `install/` directory for extra security (the wizard locks itself automatically, but removal is best practice).
- [ ] *(Optional)* Set up a cron job for background processing (see below).

---

### 6. Cron Job Setup (Optional)

SiteScope runs audits synchronously on form submit, so a cron job is **not required** for most installs. If you add background processing in the future, you can set up a cron in hPanel → **Advanced → Cron Jobs** using a path to your processing script.

For example (adjust path and script name to match your setup):

```bash
*/5 * * * * /usr/bin/php /home/u123456789/public_html/sitescope/process_queue.php >> /dev/null 2>&1
```

---

### 7. SMTP Email Configuration

For reliable email delivery, configure SMTP in **Admin → Settings → Email**:

| Field             | Value (example – Hostinger SMTP)  |
|-------------------|------------------------------------|
| Mail Driver       | `smtp`                             |
| SMTP Host         | `smtp.hostinger.com`               |
| SMTP Port         | `465` (SSL) or `587` (TLS)         |
| SMTP Username     | Your full email address            |
| SMTP Password     | Your email password                |
| From Email        | Same as SMTP Username              |

You can also use Gmail, Mailgun, SendGrid, or any SMTP provider.

---

### 8. Screenshot Feature

Audit reports automatically include a screenshot of the scanned website.  
The default provider is **WordPress mshots** — free, no API key needed.

To change provider: **Admin → Settings → Screenshot Settings**.

Available providers:
- `mshots` — WordPress mshots (default, free)
- `thum_io` — thum.io (free tier)
- `custom` — Your own screenshot API URL

---

## Troubleshooting

| Problem | Solution |
|---------|---------|
| 500 error after upload | Check file permissions (`755` for dirs, `644` for files). |
| Database connection failed | Verify DB host is `localhost`, credentials are correct, and user has all privileges. |
| Blank page | Enable PHP error display temporarily (`display_errors = On` in `.htaccess`). |
| Install wizard blocked | Delete `config/installed.lock` to re-run the installer. |
| Emails not sending | Check SMTP settings in Admin → Settings, or test with PHP `mail()` first. |
| Screenshots not showing | Some hosting blocks outbound HTTP image requests; try a different provider in Settings. |
| `.htaccess` 404 errors | Ensure `mod_rewrite` is enabled and `AllowOverride All` is set for your directory. |

---

## Security Reminders

- Never share your `.env` file — it contains your DB credentials and app key.
- Keep PHP updated to 8.1+ for best security and performance.
- Use a strong, unique admin password (12+ characters, mixed case, numbers, symbols).
- Back up your database regularly via hPanel → Backups.

---

*SiteScope v1.0 — Free Website Audit & Lead Generation Platform*
