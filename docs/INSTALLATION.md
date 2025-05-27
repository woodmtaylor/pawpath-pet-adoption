# PawPath Installation Guide

This guide provides step-by-step instructions for setting up PawPath on various environments.

## Table of Contents

- [System Requirements](#system-requirements)
- [Development Setup](#development-setup)
- [Production Deployment](#production-deployment)
- [Docker Setup](#docker-setup)
- [Environment Configuration](#environment-configuration)
- [Database Setup](#database-setup)
- [Troubleshooting](#troubleshooting)

---

## System Requirements

### Minimum Requirements

**Backend (PHP):**
- PHP 8.1 or higher
- MySQL 8.0 or MariaDB 10.6+
- Apache 2.4+ or Nginx 1.18+
- Composer 2.0+

**Frontend (React):**
- Node.js 18.0 or higher
- npm 9.0+ or Yarn 1.22+

**System Resources:**
- RAM: 2GB minimum (4GB recommended)
- Storage: 10GB minimum
- CPU: 2 cores minimum

### Required PHP Extensions

```bash
# Check if extensions are installed
php -m | grep -E "(pdo|mysql|curl|json|mbstring|openssl|zip|gd)"
```

Required extensions:
- `pdo`
- `pdo_mysql`
- `curl`
- `json`
- `mbstring`
- `openssl`
- `zip`
- `gd` (for image processing)

---

## Development Setup

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/pawpath-pet-adoption.git
cd pawpath-pet-adoption
```

### 2. Backend Setup

#### Install PHP Dependencies

```bash
cd backend
composer install
```

#### Configure Environment

```bash
# Copy environment template
cp .env.example .env

# Edit configuration
nano .env
```

**Required `.env` configuration:**

```env
# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=pawpath
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Application Settings
APP_ENV=development
APP_DEBUG=true
JWT_SECRET=your-super-secret-jwt-key-min-32-chars

# Email Configuration (Development - using Mailtrap)
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_FROM_ADDRESS=noreply@pawpath.local
MAIL_FROM_NAME="PawPath Development"

# Application URL
APP_URL=http://localhost:5173
```

#### Set Up Database

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE pawpath CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# Import schema and data
mysql -u root -p pawpath < sql_dump.sql

# Verify tables were created
mysql -u root -p pawpath -e "SHOW TABLES"
```

#### Set Up File Permissions

```bash
# Create upload directories
mkdir -p public/uploads/images/profiles
mkdir -p public/uploads/images

# Set proper permissions
chmod -R 755 public/uploads
chown -R www-data:www-data public/uploads  # Linux/Apache
# OR
chown -R _www:_www public/uploads          # macOS/Apache
```

#### Start Backend Server

```bash
# Development server
php -S localhost:8000 -t public/

# Or using Apache/Nginx (see production setup)
```

### 3. Frontend Setup

```bash
cd ../frontend
npm install
```

#### Configure Frontend Environment

```bash
# Create environment file
cp .env.example .env

# Edit configuration
nano .env
```

**Frontend `.env` configuration:**

```env
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME=PawPath
VITE_APP_VERSION=1.0.0
```

#### Start Frontend Development Server

```bash
npm run dev
```

### 4. Verify Installation

1. **Backend API Test:**
   ```bash
   curl http://localhost:8000/api/test
   ```
   Expected response: `{"success":true,"message":"API is working"}`

2. **Frontend Access:**
   Open http://localhost:5173 in your browser

3. **Run Test Suite:**
   ```bash
   cd backend
   bash api_tests.sh
   ```

---

## Production Deployment

### 1. Server Preparation

#### Ubuntu/Debian Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y apache2 mysql-server php8.1 php8.1-fpm php8.1-mysql \
  php8.1-curl php8.1-json php8.1-mbstring php8.1-xml php8.1-zip \
  php8.1-gd composer nodejs npm certbot python3-certbot-apache

# Install Node.js 18+ if not available
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

#### CentOS/RHEL Setup

```bash
# Install EPEL and Remi repositories
sudo dnf install -y epel-release
sudo dnf install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm

# Install packages
sudo dnf module enable php:remi-8.1 -y
sudo dnf install -y httpd mysql-server php php-fpm php-mysql php-curl \
  php-json php-mbstring php-xml php-zip php-gd composer nodejs npm
```

### 2. Database Setup

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create production database
sudo mysql -u root -p << EOF
CREATE DATABASE pawpath CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'pawpath_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON pawpath.* TO 'pawpath_user'@'localhost';
FLUSH PRIVILEGES;
EOF

# Import schema
mysql -u pawpath_user -p pawpath < sql_dump.sql
```

### 3. Application Deployment

```bash
# Clone to production directory
sudo git clone https://github.com/yourusername/pawpath-pet-adoption.git /var/www/pawpath
cd /var/www/pawpath

# Set ownership
sudo chown -R www-data:www-data /var/www/pawpath
```

#### Backend Configuration

```bash
cd /var/www/pawpath/backend

# Install dependencies (production mode)
sudo -u www-data composer install --no-dev --optimize-autoloader

# Set up environment
sudo -u www-data cp .env.example .env
sudo nano .env
```

**Production `.env` settings:**

```env
# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=pawpath
DB_USERNAME=pawpath_user
DB_PASSWORD=secure_password_here

# Application
APP_ENV=production
APP_DEBUG=false
JWT_SECRET=your-super-secure-production-jwt-key-min-32-chars

# Email (Production SMTP)
MAIL_HOST=smtp.your-email-provider.com
MAIL_PORT=587
MAIL_USERNAME=your-production-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="PawPath"

# Application URL
APP_URL=https://yourdomain.com
```

#### Frontend Build

```bash
cd /var/www/pawpath/frontend

# Install dependencies
sudo -u www-data npm ci --production

# Create production environment
sudo -u www-data tee .env << EOF
VITE_API_URL=https://yourdomain.com/api
VITE_APP_NAME=PawPath
EOF

# Build for production
sudo -u www-data npm run build
```

### 4. Web Server Configuration

#### Apache Configuration

```bash
# Create virtual host
sudo tee /etc/apache2/sites-available/pawpath.conf << EOF
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/pawpath/frontend/dist
    
    # Frontend routes
    <Directory /var/www/pawpath/frontend/dist>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Handle React Router
        FallbackResource /index.html
    </Directory>
    
    # Backend API
    Alias /api /var/www/pawpath/backend/public
    <Directory /var/www/pawpath/backend/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Uploads directory
    Alias /uploads /var/www/pawpath/backend/public/uploads
    <Directory /var/www/pawpath/backend/public/uploads>
        Options -Indexes
        AllowOverride None
        Require all granted
    </Directory>
    
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    
    # Logs
    ErrorLog \${APACHE_LOG_DIR}/pawpath_error.log
    CustomLog \${APACHE_LOG_DIR}/pawpath_access.log combined
</VirtualHost>
EOF

# Enable site and modules
sudo a2ensite pawpath.conf
sudo a2enmod rewrite headers
sudo systemctl reload apache2
```

#### Nginx Configuration

```bash
# Create server block
sudo tee /etc/nginx/sites-available/pawpath << EOF
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/pawpath/frontend/dist;
    index index.html;
    
    # Frontend (React)
    location / {
        try_files \$uri \$uri/ /index.html;
    }
    
    # Backend API
    location /api {
        alias /var/www/pawpath/backend/public;
        try_files \$uri \$uri/ /index.php?\$query_string;
        
        location ~ \.php$ {
            include fastcgi_params;
            fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
            fastcgi_param SCRIPT_FILENAME \$request_filename;
        }
    }
    
    # Uploads
    location /uploads {
        alias /var/www/pawpath/backend/public/uploads;
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Security
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options DENY;
    add_header X-XSS-Protection "1; mode=block";
    
    # Logs
    access_log /var/log/nginx/pawpath_access.log;
    error_log /var/log/nginx/pawpath_error.log;
}
EOF

# Enable site
sudo ln -s /etc/nginx/sites-available/pawpath /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 5. SSL Certificate

```bash
# Using Certbot (Let's Encrypt)
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Or for Nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Set up auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

---

## Docker Setup

### Docker Compose Configuration

Create `docker-compose.yml`:

```yaml
version: '3.8'

services:
  # MySQL Database
  database:
    image: mysql:8.0
    container_name: pawpath_db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: pawpath
      MYSQL_USER: pawpath_user
      MYSQL_PASSWORD: pawpath_password
      MYSQL_ROOT_PASSWORD: root_password
    volumes:
      - db_data:/var/lib/mysql
      - ./sql_dump.sql:/docker-entrypoint-initdb.d/init.sql
    ports:
      - "3306:3306"
    networks:
      - pawpath_network

  # PHP Backend
  backend:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: pawpath_backend
    restart: unless-stopped
    depends_on:
      - database
    volumes:
      - ./backend:/var/www/html
      - uploads_data:/var/www/html/public/uploads
    ports:
      - "8000:80"
    environment:
      DB_HOST: database
      DB_DATABASE: pawpath
      DB_USERNAME: pawpath_user
      DB_PASSWORD: pawpath_password
    networks:
      - pawpath_network

  # React Frontend
  frontend:
    build:
      context: ./frontend
      dockerfile: Dockerfile
    container_name: pawpath_frontend
    restart: unless-stopped
    depends_on:
      - backend
    ports:
      - "5173:3000"
    environment:
      VITE_API_URL: http://localhost:8000/api
    networks:
      - pawpath_network

volumes:
  db_data:
  uploads_data:

networks:
  pawpath_network:
    driver: bridge
```

### Backend Dockerfile

Create `backend/Dockerfile`:

```dockerfile
FROM php:8.1-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/public

# Configure Apache document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

EXPOSE 80
```

### Frontend Dockerfile

Create `frontend/Dockerfile`:

```dockerfile
FROM node:18-alpine

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm ci --only=production

# Copy source code
COPY . .

# Build application
RUN npm run build

# Use serve to run the application
RUN npm install -g serve

EXPOSE 3000

CMD ["serve", "-s", "dist", "-l", "3000"]
```

### Run with Docker

```bash
# Build and start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down

# Remove all data (careful!)
docker-compose down -v
```

---

## Environment Configuration

### Development Environment

```env
# .env (Backend)
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=pawpath_dev
DB_USERNAME=dev_user
DB_PASSWORD=dev_password

APP_ENV=development
APP_DEBUG=true
JWT_SECRET=dev-jwt-secret-key-32-characters

# Mailtrap for email testing
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
```

### Staging Environment

```env
# .env (Backend)
DB_HOST=staging-db.yourdomain.com
DB_PORT=3306
DB_DATABASE=pawpath_staging
DB_USERNAME=staging_user
DB_PASSWORD=secure_staging_password

APP_ENV=staging
APP_DEBUG=false
JWT_SECRET=staging-jwt-secret-key-32-characters

# Real SMTP for testing
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=staging@yourdomain.com
MAIL_PASSWORD=staging_email_password
```

### Production Environment

```env
# .env (Backend)
DB_HOST=production-db.yourdomain.com
DB_PORT=3306
DB_DATABASE=pawpath_production
DB_USERNAME=production_user
DB_PASSWORD=very_secure_production_password

APP_ENV=production
APP_DEBUG=false
JWT_SECRET=production-jwt-secret-key-32-characters

# Production SMTP
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=production_email_password
```

---

## Database Setup

### Manual Database Creation

```sql
-- Create database
CREATE DATABASE pawpath 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'pawpath_user'@'localhost' 
IDENTIFIED BY 'secure_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON pawpath.* 
TO 'pawpath_user'@'localhost';

FLUSH PRIVILEGES;
```

### Import Sample Data

```bash
# Import the full schema and sample data
mysql -u pawpath_user -p pawpath < sql_dump.sql

# Or import just the schema
mysql -u pawpath_user -p pawpath < backend/database/schema.sql

# Import specific migrations
mysql -u pawpath_user -p pawpath < backend/database/migrations/01_enhance_user_system.sql
```

### Create Initial Admin User

```bash
cd backend
php -r "
require 'vendor/autoload.php';
use PawPath\models\User;
use PawPath\models\UserProfile;

\$user = new User();
\$userId = \$user->create([
    'username' => 'admin',
    'email' => 'admin@yourdomain.com',
    'password' => 'AdminPassword123!'
]);

// Update role to admin
\$pdo = \PawPath\config\database\DatabaseConfig::getConnection();
\$stmt = \$pdo->prepare('UPDATE User SET role = \"admin\", account_status = \"active\" WHERE user_id = ?');
\$stmt->execute([\$userId]);

echo \"Admin user created with ID: \$userId\n\";
"
```

---

## Troubleshooting

### Common Issues

#### 1. Database Connection Failed

**Error:** `Connection failed: SQLSTATE[HY000] [2002] Connection refused`

**Solutions:**
```bash
# Check if MySQL is running
sudo systemctl status mysql

# Start MySQL if stopped
sudo systemctl start mysql

# Check connection
mysql -u root -p -e "SELECT 1"

# Verify credentials in .env file
cat backend/.env | grep DB_
```

#### 2. JWT Token Issues

**Error:** `Invalid token` or `No token provided`

**Solutions:**
```bash
# Verify JWT secret is set and long enough (32+ characters)
grep JWT_SECRET backend/.env

# Clear any cached tokens in browser localStorage
# Check API request headers include: Authorization: Bearer <token>
```

#### 3. File Upload Permissions

**Error:** `Failed to move uploaded file`

**Solutions:**
```bash
# Check directory exists and has proper permissions
ls -la backend/public/uploads/

# Fix permissions
sudo chown -R www-data:www-data backend/public/uploads/
sudo chmod -R 755 backend/public/uploads/

# Check PHP upload settings
php -i | grep -E "(upload_max_filesize|post_max_size|max_file_uploads)"
```

#### 4. Frontend Build Issues

**Error:** `Module not found` or build failures

**Solutions:**
```bash
# Clear npm cache
npm cache clean --force

# Remove node_modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Check Node.js version
node --version  # Should be 18+

# Verify environment variables
cat frontend/.env
```

#### 5. Apache/Nginx Configuration Issues

**Error:** `404 Not Found` for API routes or frontend routes

**Solutions:**

Apache:
```bash
# Enable rewrite module
sudo a2enmod rewrite

# Check .htaccess file exists in backend/public/
cat backend/public/.htaccess

# Verify virtual host configuration
sudo apache2ctl configtest

# Check Apache error logs
sudo tail -f /var/log/apache2/error.log
```

Nginx:
```bash
# Test configuration
sudo nginx -t

# Check try_files directive in server block
# Verify PHP-FPM is running
sudo systemctl status php8.1-fpm

# Check error logs
sudo tail -f /var/log/nginx/error.log
```

#### 6. Email Not Sending

**Error:** Email verification/notifications not working

**Solutions:**
```bash
# Test email configuration
cd backend
php tests/test_email.php

# Check SMTP settings in .env
grep MAIL_ .env

# For development, use Mailtrap.io
# For production, verify SMTP credentials with provider

# Check PHP mail logs
tail -f /var/log/mail.log
```

#### 7. CORS Issues

**Error:** `Access to fetch blocked by CORS policy`

**Solutions:**
```bash
# Check frontend .env API URL
cat frontend/.env

# Verify backend is running on expected port
curl http://localhost:8000/api/test

# For production, ensure proper domain configuration
# Add CORS headers in Apache/Nginx configuration
```

### Performance Optimization

#### Database Optimization

```sql
-- Add recommended indexes
CREATE INDEX idx_pet_search ON Pet(species, breed, age);
CREATE INDEX idx_adoption_status ON Adoption_Application(status, application_date);
CREATE INDEX idx_user_role_status ON User(role, account_status);

-- Analyze tables for better query planning
ANALYZE TABLE Pet, User, Adoption_Application, Quiz_Result;
```

#### PHP Optimization

```ini
; php.ini optimizations for production
memory_limit = 256M
max_execution_time = 60
upload_max_filesize = 10M
post_max_size = 10M
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 4000
```

#### Frontend Optimization

```bash
# Build with optimizations
npm run build

# Serve with compression
# Add to Apache .htaccess:
echo '<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/plain
  AddOutputFilterByType DEFLATE text/html
  AddOutputFilterByType DEFLATE text/xml
  AddOutputFilterByType DEFLATE text/css
  AddOutputFilterByType DEFLATE application/xml
  AddOutputFilterByType DEFLATE application/xhtml+xml
  AddOutputFilterByType DEFLATE application/rss+xml
  AddOutputFilterByType DEFLATE application/javascript
  AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>' >> frontend/dist/.htaccess
```

### Security Hardening

#### Backend Security

```bash
# Set secure file permissions
find backend/ -type f -exec chmod 644 {} \;
find backend/ -type d -exec chmod 755 {} \;
chmod 600 backend/.env

# Hide sensitive files
echo 'Deny from all' > backend/.env.htaccess
```

#### Database Security

```sql
-- Remove test databases and users
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
FLUSH PRIVILEGES;

-- Set strong passwords
ALTER USER 'root'@'localhost' IDENTIFIED BY 'very_strong_root_password';
```

#### Web Server Security

Apache:
```apache
# Add to virtual host
ServerTokens Prod
ServerSignature Off

# Security headers
Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
```

Nginx:
```nginx
# Add to server block
server_tokens off;

# Security headers
add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload" always;
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
```

### Monitoring and Logging

#### Application Logs

```bash
# Backend logs
tail -f backend/logs/error.log

# Web server logs
tail -f /var/log/apache2/pawpath_error.log
tail -f /var/log/nginx/pawpath_error.log

# Database logs
tail -f /var/log/mysql/error.log
```

#### Health Check Script

Create `scripts/health-check.sh`:

```bash
#!/bin/bash

# Check backend API
if curl -f http://localhost:8000/api/test > /dev/null 2>&1; then
    echo "✓ Backend API is healthy"
else
    echo "✗ Backend API is down"
    exit 1
fi

# Check database connection
if mysql -u pawpath_user -p$DB_PASSWORD pawpath -e "SELECT 1" > /dev/null 2>&1; then
    echo "✓ Database is healthy"
else
    echo "✗ Database connection failed"
    exit 1
fi

# Check frontend (if serving static files)
if curl -f http://localhost:5173 > /dev/null 2>&1; then
    echo "✓ Frontend is healthy"
else
    echo "✗ Frontend is down"
    exit 1
fi

echo "All systems operational"
```

### Backup and Recovery

#### Automated Backup Script

Create `scripts/backup.sh`:

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/pawpath"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="pawpath"
DB_USER="pawpath_user"
DB_PASS="your_password"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_$DATE.sql

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz \
    /var/www/pawpath/backend/public/uploads \
    /var/www/pawpath/backend/.env

# Keep only last 30 days
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

#### Recovery Process

```bash
# Restore database
mysql -u pawpath_user -p pawpath < /var/backups/pawpath/db_YYYYMMDD_HHMMSS.sql

# Restore files
cd /var/www/pawpath
tar -xzf /var/backups/pawpath/files_YYYYMMDD_HHMMSS.tar.gz

# Fix permissions
sudo chown -R www-data:www-data backend/public/uploads
sudo chmod -R 755 backend/public/uploads
```

### Deployment Automation

#### CI/CD Pipeline Example (GitHub Actions)

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        
    - name: Setup Node.js
      uses: actions/setup-node@v3
      with:
        node-version: '18'
        
    - name: Install PHP dependencies
      run: |
        cd backend
        composer install --no-dev --optimize-autoloader
        
    - name: Build frontend
      run: |
        cd frontend
        npm ci
        npm run build
        
    - name: Deploy to server
      uses: appleboy/ssh-action@v0.1.4
      with:
        host: ${{ secrets.SERVER_HOST }}
        username: ${{ secrets.SERVER_USER }}
        key: ${{ secrets.SERVER_SSH_KEY }}
        script: |
          cd /var/www/pawpath
          git pull origin main
          cd backend && composer install --no-dev --optimize-autoloader
          cd ../frontend && npm ci && npm run build
          sudo systemctl reload apache2
```

### Environment-Specific Configurations

#### Development

```bash
# Quick development setup
cd backend && composer install
cd ../frontend && npm install

# Start both servers
npm run dev:all  # If you have a script for this
# Or use separate terminals
```

#### Staging

```bash
# Staging deployment
git checkout staging
composer install --no-dev
npm ci && npm run build:staging
```

#### Production

```bash
# Production deployment with zero downtime
# 1. Deploy to staging directory
# 2. Run tests
# 3. Switch symlinks
# 4. Reload services
```

### Additional Resources

#### Log Rotation

Create `/etc/logrotate.d/pawpath`:

```
/var/log/apache2/pawpath_*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 640 root adm
    postrotate
        systemctl reload apache2
    endscript
}
```

#### Systemd Service (for background workers)

Create `/etc/systemd/system/pawpath-worker.service`:

```ini
[Unit]
Description=PawPath Background Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/pawpath/backend
ExecStart=/usr/bin/php worker.php
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

#### Firewall Configuration

```bash
# UFW (Ubuntu)
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable

# iptables (General)
iptables -A INPUT -p tcp --dport 80 -j ACCEPT
iptables -A INPUT -p tcp --dport 443 -j ACCEPT
iptables -A INPUT -p tcp --dport 22 -j ACCEPT
```

---

## Need Help?

If you encounter issues not covered in this guide:

1. **Check the logs** - Most issues are revealed in error logs
2. **Review the troubleshooting section** above
3. **Search existing issues** on GitHub
4. **Create a new issue** with:
   - Your environment details
   - Steps to reproduce
   - Error messages
   - Log outputs

For additional support:
- 📧 Email: support@pawpath.com
- 💬 GitHub Discussions: [Project Discussions](https://github.com/yourusername/pawpath-pet-adoption/discussions)
- 🐛 Bug Reports: [GitHub Issues](https://github.com/yourusername/pawpath-pet-adoption/issues)

---

*Last updated: December 2024*