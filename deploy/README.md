# Class Aid - VPS Production Deployment Guide

This guide details how to deploy **Class Aid** as a production web application on any standard Linux VPS (Ubuntu 22.04 / 24.04 LTS, Debian 12, or similar).

---

## 1. System Requirements

- **OS:** Ubuntu 22.04+ / Debian 12+
- **Web Server:** Nginx or Apache
- **PHP:** PHP 8.2 or 8.3 with extensions:
  - `php-fpm`, `php-sqlite3` (or `php-mysql`), `php-mbstring`, `php-xml`, `php-curl`, `php-zip`, `php-gd`, `php-pdo`
- **Database:** SQLite 3 (built-in, recommended) or MySQL 8.0 / MariaDB 10.6+
- **Composer:** v2+

---

## 2. Server Setup & Dependencies (Ubuntu / Debian)

```bash
# Update packages
sudo apt update && sudo apt upgrade -y

# Install Nginx, PHP 8.2, and extensions
sudo apt install -y nginx git unzip curl \
    php8.2-fpm php8.2-cli php8.2-common php8.2-sqlite3 php8.2-mysql \
    php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

---

## 3. Clone Repository & Setup Files

```bash
# Clone to web directory
cd /var/www
sudo git clone https://github.com/your-username/Class_Aid.git class_aid
cd /var/www/class_aid

# Install PHP dependencies without dev packages
composer install --no-dev --optimize-autoloader

# Set file permissions (Nginx/Apache runs as www-data)
sudo chown -R www-data:www-data /var/www/class_aid
sudo chmod -R 755 /var/www/class_aid
sudo chmod -R 775 /var/www/class_aid/storage /var/www/class_aid/database /var/www/class_aid/public/uploads
```

---

## 4. Web Server Configuration

### Option A: Nginx (Recommended)

1. Copy the example configuration:
   ```bash
   sudo cp deploy/nginx.conf.example /etc/nginx/sites-available/class_aid
   ```
2. Edit `/etc/nginx/sites-available/class_aid` and update `server_name` to your domain or server IP.
3. Enable the site and restart Nginx:
   ```bash
   sudo ln -s /etc/nginx/sites-available/class_aid /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl restart nginx
   ```

### Option B: Apache

If using Apache, ensure `mod_rewrite` and `mod_headers` are enabled:
```bash
sudo a2enmod rewrite headers
```
Point the Apache `DocumentRoot` to `/var/www/class_aid/public` (or `/var/www/class_aid` as our root `.htaccess` handles routing).

---

## 5. First-Launch Web Installer

1. Open your browser and navigate to `http://your-server-ip` or `http://your-domain.com`.
2. Class Aid will automatically detect that it is not yet installed and redirect to `/install`.
3. Fill in:
   - **Database Selection:** Choose SQLite (embedded, zero config) or MySQL.
   - **Super Administrator Account:** Name, Email, Password, WhatsApp/EasyPaisa phone.
   - **SMTP Mail Settings (Optional):** For automated invoice delivery and password resets.
4. Click **Complete Setup & Launch Super Admin Portal**.
5. The installer will:
   - Generate a secure `.env` file with `chmod 0600`.
   - Run initial migrations and seed core SaaS plans.
   - Hash your master password securely with `password_hash()`.
   - Lock the `/install` route against future access.

---

## 6. SSL / HTTPS (Let's Encrypt Certbot)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d classaid.yourdomain.com
```

---

## 7. Security Highlights Implemented in Class Aid

- **Rate Limiting:** Built-in IP-based rate limiting on `/login`, `/forgot-password`, and `/install` to defeat brute force attacks.
- **Session Protection:** Session fixation defense via `session_regenerate_id(true)`, User-Agent fingerprint binding to defeat hijacking, and 4-hour inactivity timeout locking.
- **Prepared Statements:** Native PDO prepared statements (`PDO::ATTR_EMULATE_PREPARES => false`) to completely prevent SQL injection.
- **CSRF Tokens:** Required on all POST requests.
- **Password Security:** One-way salted hashing with PHP default bcrypt / Argon2.
- **Zero Secrets in Public:** `.env` file permissions set to 0600 and blocked by both Apache `.htaccess` and Nginx rules.
