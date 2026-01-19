# Production Deployment Checklist

## 🔴 CRITICAL - Do These First

### 1. Build Assets for Production
```bash
npm run build
```

This will:
- Build and minify Tailwind CSS (with purging unused classes)
- Bundle and minify JavaScript
- Generate hashed filenames for cache busting
- Create `public/build/manifest.json`
- Remove `public/hot` file automatically

### 2. Verify Build Success
After running `npm run build`, check for:
- ✅ `public/build/manifest.json` exists
- ✅ `public/build/assets/*.css` exists
- ✅ `public/build/assets/*.js` exists
- ✅ `public/hot` does NOT exist (removed by cleanup script)

### 3. Environment Configuration

**IMPORTANT**: Update `.env` on production server:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-production-domain.com

# Build assets (not dev server)
VITE_DEV_SERVER_KEY=
VITE_DEV_SERVER_CERT=
```

## 📦 Files to Deploy

### Include These:
```
✅ app/
✅ bootstrap/
✅ config/
✅ database/
✅ public/ (including public/build/)
✅ resources/
✅ routes/
✅ storage/ (create writable directories on server)
✅ vendor/ (or run composer install --no-dev on server)
✅ .env (configure for production)
✅ artisan
✅ composer.json
✅ composer.lock
```

### EXCLUDE These (Already in .gitignore):
```
❌ node_modules/
❌ public/hot
❌ .env (local)
❌ *.log
❌ /vendor (if using composer install on server)
```

## 🎨 Tailwind CSS - Production Optimization

Your Tailwind setup is already optimized:

1. **Content Scanning**: Configured to scan all Blade templates
2. **CSS Purging**: Tailwind 4 automatically removes unused classes
3. **Minification**: Vite minifies CSS in production build
4. **Cache Busting**: Files are hashed (e.g., `app-Bs-RVNRw.css`)

## 🔧 Server Setup Commands

### On Your Production Server:

```bash
# 1. Install PHP dependencies (production only, optimized)
composer install --no-dev --optimize-autoloader

# 2. Run migrations
php artisan migrate --force

# 3. Clear and cache configs
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 5. Link storage
php artisan storage:link
```

## 🔒 Security Checklist

- [ ] `APP_DEBUG=false` in production `.env`
- [ ] `APP_ENV=production` in production `.env`
- [ ] Database credentials are secure
- [ ] `APP_KEY` is set (run `php artisan key:generate` if needed)
- [ ] HTTPS is enabled
- [ ] `public/hot` does NOT exist on server

## 📊 Performance Optimization

### Already Done:
- ✅ CSS/JS minification (Vite)
- ✅ Tailwind CSS purging
- ✅ Asset hashing for cache busting
- ✅ Gzip compression ready (configure web server)

### On Web Server (Nginx/Apache):

#### Nginx Example:
```nginx
# Enable Gzip
gzip on;
gzip_vary on;
gzip_types text/css application/javascript image/svg+xml;

# Cache static assets
location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

## 🧪 Testing After Deployment

1. **Check CSS Loads**:
   - Open browser DevTools → Network tab
   - Look for `app-*.css` loading from `/build/assets/`
   - Status should be 200, not 404

2. **Verify Tailwind Styles**:
   - Inspect any element with Tailwind classes
   - Classes should be applied correctly
   - No missing styles

3. **Check JavaScript**:
   - Alpine.js should work (dropdowns, modals, etc.)
   - Lucide icons should render
   - No console errors

4. **Mobile Responsiveness**:
   - Test on mobile device or DevTools
   - Tailwind responsive classes should work

## 🐛 Troubleshooting

### CSS Not Loading

**Problem**: Styles are broken, seeing unstyled HTML

**Solution**:
```bash
# 1. Remove public/hot if it exists
rm -f public/hot

# 2. Rebuild assets
npm run build

# 3. Clear Laravel cache
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# 4. Redeploy public/build/ directory
```

### Wrong Asset Paths

**Problem**: 404 errors for CSS/JS files

**Solution**:
- Verify `APP_URL` in `.env` matches your domain
- Check `public/build/manifest.json` exists
- Ensure web server serves `public/` as document root

### Tailwind Classes Not Working

**Problem**: Some Tailwind classes aren't styled

**Cause**: Classes weren't detected during build (dynamic classes)

**Solution**: 
- Use full class names (not string concatenation)
- Add to `tailwind.config.js` safelist if needed

## 📝 Pre-Deployment Script

Create `deploy.sh` for easy deployment:

```bash
#!/bin/bash
echo "🚀 Building for production..."

# Build assets
npm run build

# Check if build was successful
if [ ! -f "public/build/manifest.json" ]; then
    echo "❌ Build failed! manifest.json not found"
    exit 1
fi

# Check if hot file still exists
if [ -f "public/hot" ]; then
    echo "❌ Warning: public/hot still exists!"
    rm public/hot
    echo "✅ Removed public/hot"
fi

echo "✅ Build complete!"
echo ""
echo "📦 Ready to deploy. Include these directories:"
echo "  - app/"
echo "  - public/build/"
echo "  - resources/"
echo "  - And all other Laravel files"
echo ""
echo "🔴 REMEMBER: Set APP_ENV=production and APP_DEBUG=false on server"
```

## ✅ Final Checklist

Before deploying:
- [ ] Run `npm run build` locally
- [ ] Verify no `public/hot` file
- [ ] Test built assets locally: `php artisan serve`
- [ ] Configure production `.env`
- [ ] Upload files to server
- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Run migrations
- [ ] Cache configs: `php artisan config:cache`
- [ ] Set storage permissions
- [ ] Test the site thoroughly

---

## 🎯 Quick Deploy Commands

```bash
# Local machine - before upload
npm run build
rm -f public/hot

# Production server - after upload
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 755 storage bootstrap/cache
```

Your Tailwind CSS will work perfectly in production! 🎨



