# Hướng dẫn triển khai (Deployment Guide)

Hướng dẫn chi tiết để thiết lập, triển khai, và duy trì VMTA_Laravel trên các môi trường khác nhau.

---

## 1. Yêu cầu hệ thống

### Phát triển (Local)

- **OS:** macOS, Linux, Windows (WSL2)
- **PHP:** 8.2 - 8.3
- **Web server:** Built-in `php artisan serve` (dev)
- **Database:** SQLite (default)
- **Node.js:** 18+ (for Vite asset bundling)
- **Composer:** 2.0+
- **npm/yarn:** Latest stable
- **RAM:** 2GB minimum
- **Disk:** 1GB (code + DB)

### Staging / Production

- **OS:** Linux (Ubuntu 20.04 LTS+, CentOS 8+, Debian 11+)
- **PHP:** 8.2 - 8.3 (php-fpm)
- **Web server:** Nginx 1.18+ hoặc Apache 2.4+
- **Database:** PostgreSQL 15+ hoặc MySQL 8.0+
- **Redis:** 6.0+ (optional, recommended for caching)
- **Node.js:** 18 LTS (build assets, then remove for prod)
- **Composer:** 2.0+
- **RAM:** 4GB minimum
- **Disk:** 50GB+ (code, DB, logs, uploads)
- **Backup:** External storage (S3, Google Cloud Storage)

---

## 2. Biến môi trường (Environment Variables)

### Template (.env.example)

```env
# Application
APP_NAME=VMTA
APP_ENV=production          # development, staging, production
APP_DEBUG=false             # Never true in production
APP_URL=https://vmta.example.com

# Key (generated via php artisan key:generate)
APP_KEY=base64:...

# Encryption key (REQUIRED — 32-char hex, generated via php artisan key:generate --guard=app_enc)
APP_ENC_KEY=your-32-char-hex-key

# Database
DB_CONNECTION=pgsql        # sqlite, mysql, pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=vmta_laravel
DB_USERNAME=vmta_user
DB_PASSWORD=secure-password

# Cache
CACHE_STORE=database       # database, redis
CACHE_PREFIX=vmta_
# REDIS_HOST=127.0.0.1
# REDIS_PORT=6379

# Session
SESSION_DRIVER=database    # database, redis
SESSION_LIFETIME=120       # minutes
SESSION_ENCRYPT=false

# Queue
QUEUE_CONNECTION=database  # database, redis, sync

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=admin@vmta.example.com
MAIL_FROM_NAME="${APP_NAME}"

# Media Storage
MEDIA_STORAGE_DRIVER=local # local or google_drive
# MEDIA_STORAGE_PATH=/var/www/vmta/storage/uploads

# Google Drive (optional)
# GOOGLE_DRIVE_CLIENT_ID=...
# GOOGLE_DRIVE_CLIENT_SECRET=...
# GOOGLE_DRIVE_REDIRECT_URI=https://vmta.example.com/admin/media/google-drive/callback

# Admin Routes
CORE_ADMIN_PREFIX=admin
CORE_ADMIN_ROUTE_NAME=admin

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=warning           # debug, info, notice, warning, error, critical, alert, emergency
```

### Environment by Stage

**Development (.env.local):**
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite
CACHE_STORE=database
```

**Staging (.env.staging):**
```env
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://staging.vmta.example.com
DB_CONNECTION=pgsql
DB_HOST=staging-db.internal
CACHE_STORE=redis
LOG_LEVEL=info
```

**Production (.env.production):**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://vmta.example.com
DB_CONNECTION=pgsql
DB_HOST=prod-db.internal
CACHE_STORE=redis
LOG_LEVEL=warning
```

---

## 3. Local Development Setup

### Quickstart (5 minutes)

```bash
# Clone repository
git clone https://github.com/your-org/vmta-laravel.git
cd vmta-laravel

# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Generate encryption key (required)
php artisan key:generate --guard=app_enc

# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed default data (creates admin@nguyenkhoi.dev / 123456789)
php artisan db:seed

# Build frontend assets
npm run build

# Start development server
php artisan serve       # In one terminal
npm run dev             # In another terminal (Vite)
```

Access: http://localhost:8000/admin
- Email: `admin@nguyenkhoi.dev`
- Password: `123456789`

### With Docker (Optional)

```bash
# Build Docker image
docker build -t vmta-laravel:latest .

# Run container
docker run -d \
  --name vmta \
  -p 8000:8000 \
  -v $(pwd):/app \
  vmta-laravel:latest

# Inside container
docker exec -it vmta bash
  php artisan migrate
  php artisan db:seed
  npm run build
```

### IDE Configuration

**VSCode extensions recommended:**
- PHP Intelephense
- Blade (@component)
- Tailwind CSS IntelliSense
- Vite
- Laravel Blade Spacer

**PhpStorm:**
- Enable Laravel support
- Set project root
- Configure Blade as template language

---

## 4. Staging Deployment

### Initial Setup (VPS / Managed Server)

```bash
# 1. Server preparation
ssh user@staging.example.com
sudo apt update && sudo apt upgrade -y

# 2. Install dependencies
sudo apt install -y php8.3 php8.3-fpm php8.3-pgsql php8.3-redis \
  composer nodejs npm postgresql redis-server nginx

# 3. Create application directory
sudo mkdir -p /var/www/vmta-staging
sudo chown user:user /var/www/vmta-staging
cd /var/www/vmta-staging

# 4. Clone repository
git clone https://github.com/your-org/vmta-laravel.git .

# 5. Install PHP dependencies
composer install --no-dev --optimize-autoloader

# 6. Install Node dependencies
npm install
npm run build
rm -rf node_modules  # Optional: save space

# 7. Setup environment
cp .env.example .env
# Edit .env with staging config
nano .env

# Generate keys
php artisan key:generate
php artisan key:generate --guard=app_enc

# 8. Database setup
# Create DB in PostgreSQL
sudo -u postgres createdb vmta_staging
sudo -u postgres createuser vmta_user
sudo -u postgres psql -c "ALTER USER vmta_user WITH PASSWORD 'secure-password';"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE vmta_staging TO vmta_user;"

# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed

# 9. Setup file permissions
sudo chown -R www-data:www-data /var/www/vmta-staging
sudo chmod -R 775 storage bootstrap/cache public/uploads

# 10. Configure Nginx
sudo nano /etc/nginx/sites-available/vmta-staging
```

**Nginx config:**
```nginx
server {
    listen 80;
    server_name staging.vmta.example.com;
    root /var/www/vmta-staging/public;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    index index.html index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable and restart:
```bash
sudo ln -s /etc/nginx/sites-available/vmta-staging /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Enable HTTPS (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Generate certificate
sudo certbot certonly --nginx -d staging.vmta.example.com

# Auto-renew
sudo systemctl enable certbot.timer
```

Update Nginx to redirect HTTP → HTTPS:
```nginx
server {
    listen 80;
    server_name staging.vmta.example.com;
    return 301 https://$server_name$request_uri;
}
```

---

## 5. Production Deployment

### Pre-deployment Checklist

- [ ] Environment variables configured (.env)
- [ ] Database migrations tested on staging
- [ ] Backup strategy in place
- [ ] Monitoring setup (logs, metrics, alerts)
- [ ] SSL certificate installed
- [ ] CDN configured (optional)
- [ ] Email service configured
- [ ] Google Drive API credentials (if using)
- [ ] Security headers configured (HSTS, CSP)
- [ ] Rate limiting configured

### Deployment Process

```bash
# 1. On deployment server
ssh user@production.example.com
cd /var/www/vmta

# 2. Backup current version
cp -r /var/www/vmta /backups/vmta-$(date +%Y%m%d-%H%M%S)

# 3. Pull latest code
git pull origin main

# 4. Install dependencies
composer install --no-dev --optimize-autoloader

# 5. Update frontend assets
npm ci
npm run build
# Optional: remove node_modules to save space
# rm -rf node_modules

# 6. Run migrations (zero-downtime with cache warmup)
# Option A: Traditional (brief downtime, ~1-2 minutes)
php artisan down
php artisan migrate --force
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up

# Option B: Migrations in background (no downtime)
php artisan migrate --force &  # Background

# 7. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 8. Warmup caches
php artisan cache:prime
php artisan optimize

# 9. Restart queue worker
sudo systemctl restart vmta-worker

# 10. Verify deployment
curl https://vmta.example.com/admin -L | head -20  # Check response

# 11. Monitor logs
tail -f storage/logs/laravel-*.log

# 12. Alert ops team (Slack, email, etc.)
echo "Deployment complete"
```

### Zero-downtime Deployment (Advanced)

```bash
#!/bin/bash
# deploy-rolling.sh

set -e

CURRENT_RELEASE="/var/www/vmta/current"
RELEASES_DIR="/var/www/vmta/releases"
NEW_RELEASE="$RELEASES_DIR/$(date +%Y%m%d-%H%M%S)"

echo "Creating new release directory..."
mkdir -p $NEW_RELEASE
git clone --branch main https://github.com/your-org/vmta-laravel.git $NEW_RELEASE

cd $NEW_RELEASE

echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader

echo "Building frontend..."
npm ci
npm run build

echo "Copying shared files (.env)..."
cp $CURRENT_RELEASE/.env .env
cp -r $CURRENT_RELEASE/storage/uploads ./storage/ 2>/dev/null || true

echo "Running migrations..."
php artisan migrate --force

echo "Warming up caches..."
php artisan cache:prime
php artisan optimize

echo "Switching symlink..."
ln -sfn $NEW_RELEASE $CURRENT_RELEASE

echo "Restarting PHP..."
sudo systemctl reload php8.3-fpm

echo "Cleaning up old releases (keeping last 5)..."
cd $RELEASES_DIR
ls -t | tail -n +6 | xargs rm -rf

echo "Deployment complete!"
```

---

## 6. Database Management

### Initial Migration

```bash
# Run all migrations
php artisan migrate

# Seed default data
php artisan db:seed

# Seed specific seeder
php artisan db:seed --class=AdminSeeder
```

### Backup Strategy

**Daily automated backup:**
```bash
# Add to crontab
0 2 * * * /opt/backup-db.sh

# backup-db.sh
#!/bin/bash
BACKUP_DIR="/backups/database"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# PostgreSQL
pg_dump -U vmta_user vmta_laravel | gzip > $BACKUP_DIR/vmta_$TIMESTAMP.sql.gz

# Upload to cloud (e.g., S3)
aws s3 cp $BACKUP_DIR/vmta_$TIMESTAMP.sql.gz s3://vmta-backups/

# Cleanup old backups (keep 30 days)
find $BACKUP_DIR -name "vmta_*.sql.gz" -mtime +30 -delete
```

### Database Optimization

```bash
# PostgreSQL
# Analyze and vacuum
php artisan tinker
>>> DB::statement('VACUUM ANALYZE');

# Check index usage
php artisan tinker
>>> DB::select('SELECT * FROM pg_stat_user_indexes ORDER BY idx_scan DESC;')

# MySQL
# Optimize tables
php artisan tinker
>>> DB::statement('OPTIMIZE TABLE users, roles, activity_logs, ...');
```

### Replication (Production HA)

**PostgreSQL streaming replication:**

Primary server:
```postgresql
-- postgresql.conf
wal_level = replica
max_wal_senders = 10
wal_keep_segments = 64
```

Replica server:
```bash
# Clone data from primary
pg_basebackup -h primary-db.internal -U replication_user -D /var/lib/postgresql/15/main -Pv -W -R
```

---

## 7. Monitoring & Logging

### Log Management

**Laravel logs location:**
```
storage/logs/laravel-YYYY-MM-DD.log
```

**Log rotation:**
```bash
# Add to crontab (auto-rotate logs daily)
0 0 * * * find /var/www/vmta/storage/logs -name "*.log" -mtime +30 -delete
```

**Structured logging (JSON):**
```php
// config/logging.php
'stack' => [
    'driver' => 'stack',
    'channels' => ['daily', 'slack'], // Add channels
    'ignore_exceptions' => false,
]

// Production: Use JSON driver for better parsing
'single' => [
    'driver' => 'single',
    'path' => storage_path('logs/laravel.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'formatter' => 'json', // JSON output
],
```

### Monitoring Tools

**Application Performance:**
- New Relic (commercial)
- Datadog (commercial)
- Sentry (error tracking)

**Infrastructure:**
- Prometheus + Grafana (metrics)
- ELK Stack (logs)
- Uptime monitoring (Uptimerobot, etc.)

### Alerts

**Critical alerts:**
- High error rate (> 5% in 5 mins)
- Database connection errors
- Queue failures (> 10 retries)
- Disk space < 10%
- Memory usage > 90%

---

## 8. Security Hardening

### .env Security

```bash
# Never commit .env to git
echo ".env" >> .gitignore
echo ".env.*.local" >> .gitignore

# Restrict permissions
chmod 600 .env

# Use environment-based secrets in CI/CD
# Export APP_KEY, APP_ENC_KEY, DB_PASSWORD, etc.
```

### Security Headers (Nginx)

```nginx
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
add_header X-Frame-Options "DENY" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;
```

### Database Security

```bash
# Use strong passwords
DB_PASSWORD=$(openssl rand -base64 32)

# Restrict database user permissions
GRANT CONNECT ON DATABASE vmta_laravel TO vmta_user;
GRANT USAGE ON SCHEMA public TO vmta_user;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO vmta_user;

# Disable root login, change default port
```

### Application Security

```bash
# Disable debug mode in production
APP_DEBUG=false

# Set secure session cookie
SESSION_SECURE_COOKIES=true
SESSION_HTTP_ONLY=true

# Rate limiting on login
RATE_LIMIT_LOGIN=5/15minutes

# Enforce HTTPS
APP_FORCE_HTTPS=true
```

---

## 9. Scaling Considerations

### Horizontal Scaling

**Load balancer setup:**
```
   Users
    ↓
[Load Balancer] (Nginx, HAProxy)
    ↓
┌───────┬───────┬───────┐
│ App 1 │ App 2 │ App 3 │  (PHP-FPM instances)
└───────┴───────┴───────┘
         ↓
[Shared Database]
[Shared Cache]
[Shared Storage]
```

**Stateless application:**
- No server-local session files (use database/Redis)
- Use absolute URLs in views
- Store uploads in cloud storage

### Database Scaling

**Read replicas:**
```php
// config/database.php
'connections' => [
    'mysql' => [
        'write' => [
            'host' => 'primary-db.internal',
        ],
        'read' => [
            ['host' => 'replica-1.internal'],
            ['host' => 'replica-2.internal'],
        ],
    ],
]
```

### Caching Strategy

**Redis for high traffic:**
```env
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

---

## 10. Disaster Recovery

### Backup & Restore

**Backup all data:**
```bash
#!/bin/bash
# Full backup (code + DB + uploads)

BACKUP_DIR="/backups/vmta-$(date +%Y%m%d-%H%M%S)"
mkdir -p $BACKUP_DIR

# Code
tar -czf $BACKUP_DIR/code.tar.gz /var/www/vmta/

# Database
pg_dump vmta_laravel | gzip > $BACKUP_DIR/database.sql.gz

# Uploads
tar -czf $BACKUP_DIR/uploads.tar.gz /var/www/vmta/storage/uploads/

# Upload to S3
aws s3 sync $BACKUP_DIR s3://vmta-backups/$BACKUP_DIR/
```

**Restore from backup:**
```bash
#!/bin/bash
# Restore from backup

BACKUP_DIR=$1

# Stop application
php artisan down

# Restore code
tar -xzf $BACKUP_DIR/code.tar.gz

# Restore database
gunzip -c $BACKUP_DIR/database.sql.gz | psql vmta_laravel

# Restore uploads
tar -xzf $BACKUP_DIR/uploads.tar.gz

# Restart
php artisan up
```

### RTO/RPO Targets

- **RTO** (Recovery Time Objective): < 1 hour
- **RPO** (Recovery Point Objective): < 15 minutes

---

## 11. Troubleshooting

### Common Issues

| Issue | Cause | Solution |
|-------|-------|----------|
| **White screen** | PHP error | Check `storage/logs/laravel-*.log` |
| **Database connection failed** | Wrong credentials | Verify `DB_*` in `.env` |
| **Uploads not working** | Permission denied | `sudo chown -R www-data:www-data storage/` |
| **Routes not found (404)** | Routes not cached | `php artisan route:cache` |
| **Slow queries** | No indexes, N+1 problem | Use `php artisan tinker` + `DB::enableQueryLog()` |
| **Out of memory** | Memory limit exceeded | Increase `memory_limit` in php.ini |

### Debugging

```bash
# Check PHP version
php -v

# Check PHP extensions
php -m | grep pgsql  # or mysql, redis

# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
>>> DB::select('SELECT 1');

# View environment
php artisan tinker
>>> env('APP_NAME')
>>> config('app.url')

# Check queue status
php artisan queue:work --verbose

# View cache
php artisan tinker
>>> Cache::get('settings')
```

---

## 12. Maintenance Windows

### Scheduled Maintenance

```bash
# Schedule in crontab
# Daily: 2:00 AM UTC
0 2 * * * /opt/maintenance.sh
```

**maintenance.sh:**
```bash
#!/bin/bash

# Put app in maintenance mode
php artisan down --message="Scheduled maintenance (2:00-3:00 UTC)" --secret="secret-key"

# Run maintenance tasks
php artisan cache:clear
php artisan view:clear
php artisan optimize
php artisan media:cleanup
# Prune activity logs older than 90 days
php artisan tinker
>>> ActivityLog::where('created_at', '<', now()->subDays(90))->delete()

# Bring app back up
php artisan up
```

---

## 13. Rollback Procedure

```bash
# If deployment fails
cd /var/www/vmta

# Restore previous code
git reset --hard HEAD~1

# Restore previous database state
pg_restore -d vmta_laravel /backups/database/vmta_YYYYMMDD.sql.gz

# Restart
php artisan optimize
sudo systemctl restart php8.3-fpm
```

---

## 14. Deployment Checklist

### Pre-deployment
- [ ] Code reviewed and merged to main
- [ ] Tests passing (CI/CD green)
- [ ] Database migrations tested on staging
- [ ] .env variables updated
- [ ] Backup taken
- [ ] Team notified

### Deployment
- [ ] Pull latest code
- [ ] Install dependencies (composer, npm)
- [ ] Build assets
- [ ] Run migrations
- [ ] Clear/warm caches
- [ ] Restart services
- [ ] Smoke tests (manual or automated)

### Post-deployment
- [ ] Verify deployment (check URL, dashboard)
- [ ] Monitor logs for errors
- [ ] Check performance metrics
- [ ] Confirm users can login
- [ ] Test key features
- [ ] Document any changes
- [ ] Update deployment log

---

*Cập nhật: 17/05/2026*
