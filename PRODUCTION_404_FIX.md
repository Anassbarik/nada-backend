# Fix 404 Errors in Production (Subdirectory Deployment)

## The Problem

Your Laravel app is deployed in `/admin/public` subdirectory, causing 404 errors for all routes except the homepage. This happens because the web server doesn't know how to route requests properly in a subdirectory.

**Example failing route:** `https://seminairexpo.com/admin/public/events/seafood4africa/airports/1/edit`

---

## Solution 1: Apache Configuration (Recommended)

### Step 1: Verify `.htaccess` is Being Read

1. **SSH into your server** and check Apache configuration:

```bash
# Check if mod_rewrite is enabled
sudo a2enmod rewrite

# Restart Apache
sudo systemctl restart apache2
```

2. **Check your Apache VirtualHost file** (usually in `/etc/apache2/sites-available/`):

```bash
sudo nano /etc/apache2/sites-available/seminairexpo.com.conf
```

### Step 2: Update VirtualHost Configuration

Your VirtualHost should look like this:

```apache
<VirtualHost *:80>
    ServerName seminairexpo.com
    DocumentRoot /var/www/html
    
    # Allow .htaccess overrides for the entire site
    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Special configuration for Laravel app in subdirectory
    <Directory /var/www/html/admin/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Ensure mod_rewrite is active
        RewriteEngine On
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/seminairexpo-error.log
    CustomLog ${APACHE_LOG_DIR}/seminairexpo-access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName seminairexpo.com
    DocumentRoot /var/www/html
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/your/certificate.crt
    SSLCertificateKeyFile /path/to/your/private.key
    SSLCertificateChainFile /path/to/your/ca_bundle.crt
    
    # Allow .htaccess overrides
    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Laravel app in subdirectory
    <Directory /var/www/html/admin/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        RewriteEngine On
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/seminairexpo-ssl-error.log
    CustomLog ${APACHE_LOG_DIR}/seminairexpo-ssl-access.log combined
</VirtualHost>
```

### Step 3: Test and Restart Apache

```bash
# Test configuration
sudo apache2ctl configtest

# If "Syntax OK", restart Apache
sudo systemctl restart apache2
```

---

## Solution 2: Enhanced `.htaccess` for Subdirectory

Update your `public/.htaccess` file to handle subdirectory deployment better:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On
    
    # Set the base path for subdirectory deployment
    RewriteBase /admin/public/

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Handle X-XSRF-Token Header
    RewriteCond %{HTTP:x-xsrf-token} .
    RewriteRule .* - [E=HTTP_X_XSRF_TOKEN:%{HTTP:X-XSRF-Token}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

**Key addition:** `RewriteBase /admin/public/`

---

## Solution 3: Nginx Configuration (If Using Nginx)

If your server uses Nginx instead of Apache, update your configuration:

```nginx
server {
    listen 80;
    listen 443 ssl http2;
    server_name seminairexpo.com;
    
    # SSL configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    # Main site root
    root /var/www/html;
    index index.html index.htm;
    
    # Laravel app in subdirectory
    location /admin/public {
        alias /var/www/html/admin/public;
        try_files $uri $uri/ @laravel;
        
        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock; # Adjust PHP version
            fastcgi_param SCRIPT_FILENAME $request_filename;
            include fastcgi_params;
        }
    }
    
    location @laravel {
        rewrite /admin/public/(.*)$ /admin/public/index.php?/$1 last;
    }
}
```

---

## Solution 4: Quick Test Route

Add a test route to verify routing is working:

1. **Edit `routes/web.php`**, add this at the top (before any middleware):

```php
Route::get('/test-route-works', function() {
    return response()->json([
        'success' => true,
        'message' => 'Routing is working!',
        'url' => url()->current(),
        'base_url' => url('/'),
    ]);
});
```

2. **Visit:** `https://seminairexpo.com/admin/public/test-route-works`

3. **Expected result:** JSON response showing "Routing is working!"

4. **If you see JSON:** Your routing is fine, the issue is authentication/middleware
5. **If you see 404:** Your web server configuration needs fixing (follow Solution 1 or 3)

---

## Solution 5: Verify APP_URL in .env

Make sure your `.env` file has the correct APP_URL:

```env
APP_URL=https://seminairexpo.com/admin/public
```

Then clear config cache again:

```
https://seminairexpo.com/admin/public/clear-cache?token=your-secret-token
```

---

## Debugging Steps

### 1. Check if .htaccess is being read

Create a file `public/test.txt` with content "TEST FILE".

- **Visit:** `https://seminairexpo.com/admin/public/test.txt`
- **Expected:** You should see "TEST FILE"
- **If 404:** Web server configuration issue

### 2. Check Apache error logs

```bash
sudo tail -f /var/log/apache2/seminairexpo-error.log
```

Look for errors like:
- "File does not exist"
- ".htaccess: Invalid command 'RewriteEngine'"
- Permission denied

### 3. Check file permissions

```bash
# Laravel app should be owned by web server user
sudo chown -R www-data:www-data /var/www/html/admin
sudo chmod -R 755 /var/www/html/admin

# Storage and cache need write permissions
sudo chmod -R 775 /var/www/html/admin/storage
sudo chmod -R 775 /var/www/html/admin/bootstrap/cache
```

### 4. Verify mod_rewrite is enabled (Apache)

```bash
# Check if mod_rewrite is loaded
apache2ctl -M | grep rewrite

# Should show: rewrite_module (shared)
```

If not shown, enable it:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

## Common Causes & Fixes

| Symptom | Cause | Fix |
|---------|-------|-----|
| Homepage works, all other routes 404 | mod_rewrite disabled or AllowOverride None | Enable mod_rewrite, set AllowOverride All |
| All routes return HTML instead of content | .htaccess not being read | Check AllowOverride in VirtualHost |
| Routes work locally but not production | APP_URL mismatch | Update APP_URL in .env |
| Some routes work, nested routes fail | RewriteBase missing for subdirectory | Add `RewriteBase /admin/public/` |
| Login page shows instead of 404 | Routes working but need authentication | This is correct! Just log in |

---

## Still Not Working?

If none of the above works, check:

1. **Is Laravel being reached at all?**
   - Create `public/info.php` with `<?php phpinfo(); ?>`
   - Visit `https://seminairexpo.com/admin/public/info.php`
   - If this works, PHP is running

2. **Check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Run route list command:**
   ```bash
   php artisan route:list | grep airports
   ```
   Should show your route exists

4. **Contact your hosting provider** - they may have special requirements for subdirectory deployments

---

## After Fixing

Once routes work, **delete the test route** from `routes/web.php` and run:

```
https://seminairexpo.com/admin/public/clear-cache?token=your-secret-token
```

---

## Need More Help?

Provide these details:
- Web server type (Apache/Nginx/other)
- PHP version: `php -v`
- Apache modules: `apache2ctl -M`
- Error log output: `tail -20 /var/log/apache2/error.log`











