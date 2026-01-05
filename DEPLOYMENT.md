# Laravel Application Deployment Guide for cPanel

This guide explains how to deploy the Laravel application to cPanel hosting in the `/admin` subdirectory.

## Prerequisites

- cPanel access
- FTP/SFTP access or File Manager access
- PHP 8.1 or higher
- Composer installed on your local machine
- SSH access (recommended but optional)

## Directory Structure on cPanel

On your cPanel hosting, the application should be structured as follows:

```
public_html/
└── admin/                          # Your Laravel app root
    ├── .htaccess                  # Root .htaccess (redirects to public)
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── public/                    # This is the web-accessible folder
    │   ├── .htaccess             # Public .htaccess (handles routing)
    │   ├── index.php             # Laravel entry point
    │   ├── assets/
    │   └── storage/              # Symlink to ../storage/app/public
    ├── resources/
    ├── routes/
    ├── storage/
    ├── vendor/
    └── ...
```

## Step-by-Step Deployment Instructions

### Step 1: Prepare Your Local Application

1. **Set production environment variables:**
   ```bash
   cp .env.example .env
   ```

2. **Update `.env` file with production values:**
   ```env
   APP_NAME="Seminaire Expo Admin"
   APP_ENV=production
   APP_KEY=base64:YOUR_APP_KEY_HERE
   APP_DEBUG=false
   APP_URL=https://seminairexpo.com/admin

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_user
   DB_PASSWORD=your_database_password

   MAIL_MAILER=smtp
   MAIL_HOST=your_smtp_host
   MAIL_PORT=587
   MAIL_USERNAME=your_email
   MAIL_PASSWORD=your_password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=noreply@seminairexpo.com
   MAIL_ADMIN_EMAIL=admin@seminairexpo.com

   CORS_ALLOWED_ORIGINS=https://seminairexpo.com
   ```

3. **Generate application key (if not already set):**
   ```bash
   php artisan key:generate
   ```

4. **Install/update dependencies:**
   ```bash
   composer install --optimize-autoloader --no-dev
   npm install
   npm run build
   ```

5. **Optimize for production:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan event:cache
   ```

### Step 2: Upload Files to cPanel

#### Option A: Using FTP/SFTP Client (Recommended)

1. **Connect to your cPanel via FTP/SFTP:**
   - Host: `ftp.yourdomain.com` or your server IP
   - Username: Your cPanel username
   - Password: Your cPanel password
   - Port: 21 (FTP) or 22 (SFTP)

2. **Navigate to `public_html/admin/` folder**

3. **Upload all files EXCEPT:**
   - `.env` (you'll create this manually)
   - `node_modules/` (not needed on server)
   - `.git/` (not needed on server)
   - `.gitignore`
   - `package.json`, `package-lock.json`, `vite.config.js` (development files)

4. **Ensure `.htaccess` files are uploaded:**
   - Root `.htaccess` (in `/admin/`)
   - `public/.htaccess` (in `/admin/public/`)

#### Option B: Using cPanel File Manager

1. **Log into cPanel**

2. **Navigate to File Manager**

3. **Go to `public_html/admin/` folder** (create it if it doesn't exist)

4. **Upload a ZIP file** containing your Laravel application:
   - Create a ZIP of your application (excluding `.env`, `node_modules`, `.git`)
   - Upload the ZIP file
   - Extract it in the `admin` folder

5. **Set correct file permissions:**
   - Folders: `755`
   - Files: `644`
   - `storage/` and `bootstrap/cache/`: `775`

### Step 3: Create .env File on Server

1. **In cPanel File Manager**, navigate to `/public_html/admin/`

2. **Create a new file** named `.env`

3. **Copy the contents** from your local `.env` file (updated with production values)

4. **OR use SSH** (if available):
   ```bash
   cd ~/public_html/admin
   nano .env
   # Paste your .env content
   # Save and exit (Ctrl+X, Y, Enter)
   ```

### Step 4: Set File Permissions

Using cPanel File Manager or SSH:

1. **Navigate to `/public_html/admin/`**

2. **Set folder permissions:**
   - `storage/` → `775` (or `755`)
   - `bootstrap/cache/` → `775` (or `755`)
   - All other folders → `755`

3. **Set file permissions:**
   - All PHP files → `644`
   - `.htaccess` files → `644`

**Using SSH (if available):**
```bash
cd ~/public_html/admin
find storage bootstrap/cache -type d -exec chmod 775 {} \;
find storage bootstrap/cache -type f -exec chmod 664 {} \;
```

### Step 5: Create Storage Symlink

**Using SSH (recommended):**
```bash
cd ~/public_html/admin
php artisan storage:link
```

**OR manually using cPanel File Manager:**
1. Navigate to `/public_html/admin/public/`
2. Create a symlink named `storage` pointing to `../../storage/app/public`

### Step 6: Configure Database

1. **In cPanel, go to MySQL Databases**

2. **Create a database** (e.g., `username_admin_db`)

3. **Create a database user** and assign it to the database with ALL PRIVILEGES

4. **Update your `.env` file** with the database credentials:
   ```env
   DB_DATABASE=username_admin_db
   DB_USERNAME=username_dbuser
   DB_PASSWORD=your_password
   ```

5. **Run migrations** (using SSH or cPanel Terminal):
   ```bash
   cd ~/public_html/admin
   php artisan migrate --force
   ```

### Step 7: Run Artisan Commands

**Using SSH or cPanel Terminal:**
```bash
cd ~/public_html/admin

# Clear and cache configuration
php artisan config:clear
php artisan config:cache

# Clear and cache routes
php artisan route:clear
php artisan route:cache

# Clear and cache views
php artisan view:clear
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### Step 8: Verify .htaccess Files

Ensure both `.htaccess` files exist and have correct content:

1. **Root `.htaccess`** (`/public_html/admin/.htaccess`)
   - Should redirect to `public/` folder

2. **Public `.htaccess`** (`/public_html/admin/public/.htaccess`)
   - Should handle Laravel routing

### Step 9: Test Your Application

1. **Visit your application:**
   - Admin Panel: `https://seminairexpo.com/admin`
   - API: `https://seminairexpo.com/admin/api/events`

2. **Check for errors:**
   - Check browser console
   - Check cPanel Error Logs
   - Enable `APP_DEBUG=true` temporarily if needed

## Troubleshooting

### Common Issues:

1. **500 Internal Server Error:**
   - Check file permissions (storage, bootstrap/cache should be 775)
   - Check `.env` file exists and has correct values
   - Check error logs in cPanel

2. **404 Not Found:**
   - Verify `.htaccess` files are present
   - Check if `mod_rewrite` is enabled in Apache
   - Verify the document root is pointing to `/public_html/admin/public/`

3. **Permission Denied:**
   - Set `storage/` and `bootstrap/cache/` to `775`
   - Ensure web server user owns these directories

4. **Database Connection Error:**
   - Verify database credentials in `.env`
   - Ensure database user has proper privileges
   - Check if database host is `localhost` (common in cPanel)

5. **Storage Files Not Loading:**
   - Verify storage symlink exists: `public/storage` → `../storage/app/public`
   - Check storage folder permissions

### Enable Error Logging:

In `.env`, temporarily set:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

Check logs at: `/public_html/admin/storage/logs/laravel.log`

## PHP Version Check

Ensure your cPanel PHP version is 8.1 or higher:

1. **In cPanel, go to "Select PHP Version"**
2. **Choose PHP 8.1 or higher**
3. **Enable required extensions:**
   - `mbstring`
   - `openssl`
   - `pdo`
   - `pdo_mysql`
   - `tokenizer`
   - `xml`
   - `ctype`
   - `json`
   - `bcmath`
   - `fileinfo`
   - `curl`

## Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong `APP_KEY` generated
- [ ] `.env` file is not publicly accessible
- [ ] File permissions are correct
- [ ] Storage symlink is created
- [ ] Database credentials are secure
- [ ] HTTPS is enabled (SSL certificate)
- [ ] Regular backups are configured

## Maintenance Mode

To put the site in maintenance mode:
```bash
php artisan down
```

To bring it back online:
```bash
php artisan up
```

## Regular Updates

After deploying updates:

1. Pull/upload new files
2. Run: `composer install --optimize-autoloader --no-dev`
3. Run: `php artisan migrate`
4. Run: `php artisan config:cache`
5. Run: `php artisan route:cache`
6. Run: `php artisan view:cache`

## Support

For issues, check:
- Laravel logs: `storage/logs/laravel.log`
- cPanel Error Logs
- Apache Error Logs

