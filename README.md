# Global Dream Pro — PHP, JavaScript, CSS, MySQL

This starter includes a public scholarship directory, a password-protected admin dashboard, manual post management, and a feed-based discovery queue.

## Requirements
- PHP 8.1+ recommended
- PHP extensions: PDO MySQL, cURL, DOM, libxml, mbstring
- MySQL 8+ or MariaDB
- XAMPP/WAMP/MAMP or a PHP host

## Install with XAMPP (Windows)
1. Extract `global-dream-pro` to `C:\xampp\htdocs\global-dream-pro`.
2. Start Apache and MySQL in XAMPP.
3. Open `http://localhost/phpmyadmin`.
4. Import `database.sql`.
5. Review `config/config.php`. Local XAMPP defaults are `root` and an empty password; set your own credentials on production.
6. Open `http://localhost/global-dream-pro/setup.php`.
7. Create the first administrator with a strong password (at least 12 characters).
8. Delete `setup.php` after creating the administrator.
9. Open the public site: `http://localhost/global-dream-pro/`.
10. Open the admin dashboard: `http://localhost/global-dream-pro/admin/login.php`.

## How the admin works
- Add/edit/delete scholarship posts.
- Save posts as draft, published, or archived.
- Add permitted RSS or Atom feeds at Admin → Sources.
- Run `cron/fetch_feeds.php` from CLI. Feed entries are deduplicated and placed into Admin → Discovery queue.
- An administrator must check the official page and approve each entry. No item is auto-published.

## Add a permitted RSS/Atom feed
1. Find the official institution or scholarship provider's RSS/Atom feed.
2. Read the provider's terms and robots/use policy. Only add feeds you are permitted to fetch.
3. Sign into the admin area → Sources → add the feed name and URL.
4. The feed URL host is recorded as an allowlist. The importer refuses private/local IP destinations and does not follow HTTP redirects.
5. Run the importer manually to test (see below).
6. Review items in Discovery queue. Verify the deadline, funding, nationality rules, documents, and official application link before publishing.

Do not invent a feed URL. Some institutions do not provide a feed. For those, use the manual admin form.

## Run feed importer manually
Open a terminal / Command Prompt and run:
```bash
cd C:\xampp\htdocs\global-dream-pro
C:\xampp\php\php.exe cron\fetch_feeds.php
```
Adjust the PHP path if XAMPP is installed elsewhere. On Linux:
```bash
cd /path/to/global-dream-pro
php cron/fetch_feeds.php
```
Make sure PHP cURL, DOM, libxml, and mbstring extensions are enabled.

## Schedule automatic checks
Use Windows Task Scheduler or Linux cron to run the CLI command every 6–12 hours. Do not expose the importer as a public URL. The script is CLI-only. Start with a low frequency and respect each feed's rate limits and terms.

## Database and security notes
- The SQL creates `admins`, `scholarships`, `discovery_sources`, and `discovery_items`.
- Passwords are hashed with `password_hash`; logins use `password_verify`.
- Admin forms use CSRF tokens and prepared statements; session ID is regenerated at login.
- The importer restricts feed hosts, checks public IPs, does not follow redirects, limits response size/time, and only accepts candidate links on the configured source host.
- Treat all discovered items as untrusted. Verify them manually.
- Before production: use HTTPS, a dedicated least-privilege MySQL user, environment variables for secrets, secure cookies, backups, server-side logging, rate limiting, and regular updates. Remove setup.php after use.
- RSS discovery only works when a source provides a feed. It does not magically discover every website or guarantee current scholarship data.
