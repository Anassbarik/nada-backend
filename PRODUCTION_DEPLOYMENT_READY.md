# Production Deployment - Ready Checklist ✅

## Pre-Deployment Steps Completed

### ✅ 1. Caches Cleared
- ✅ Configuration cache cleared
- ✅ Application cache cleared
- ✅ Route cache cleared
- ✅ View cache cleared
- ✅ All caches cleared with `optimize:clear`

### ✅ 2. Assets Built for Production
- ✅ Tailwind CSS compiled
- ✅ JavaScript bundled
- ✅ Manifest.json generated
- ✅ `public/hot` file removed (prevents dev server connection)
- ✅ Build output verified in `public/build/`

### ✅ 3. Storage Link
- ✅ Storage symbolic link exists (or will be created on server)

## Build Output Verification

The following files are ready in `public/build/`:
- ✅ `manifest.json` - Asset manifest
- ✅ `assets/app-DCNnGENT.css` - Compiled Tailwind CSS (57.50 kB)
- ✅ `assets/app-DhVVW2n5.js` - Compiled JavaScript (82.05 kB)

## Files to Upload to Production

### Required Files/Directories:
1. ✅ **`public/build/`** - Entire directory (CRITICAL for Tailwind/CSS to work)
2. ✅ All application files (app/, config/, database/, resources/, routes/, etc.)
3. ✅ `.env` file (with production settings)
4. ✅ `vendor/` directory (if not using composer install on server)

### Files to EXCLUDE:
- ❌ `node_modules/` (not needed on server)
- ❌ `public/hot` (should not exist - removed by build script)
- ❌ `.env.example` (use actual `.env`)
- ❌ Development files (`.git/`, etc.)

## Production Server Steps

### 1. Upload Files
Upload all files including the `public/build/` directory to your production server.

### 2. Environment Configuration
Ensure your production `.env` file has:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database configuration
DB_CONNECTION=mysql
DB_HOST=your_host
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Other required settings...
```

### 3. Run Migrations (if needed)
```bash
php artisan migrate --force
```

### 4. Clear Caches on Server
After uploading, clear caches on the production server:

**Option A: Via SSH (if available)**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

**Option B: Via Web Browser**
If you have the cache clear route set up:
```
https://your-domain.com/clear-cache?token=YOUR_SECRET_TOKEN
```

### 5. Verify Storage Link
```bash
php artisan storage:link
```

### 6. Set Permissions (if needed)
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Post-Deployment Verification

### ✅ Check These:

1. **CSS/JS Loading**
   - Visit your website
   - Check browser console for errors
   - Verify styles are applied (Tailwind working)
   - Check Network tab - assets should load from `/build/assets/` not `[::1]:5173`

2. **Routes Working**
   - Test admin routes
   - Test API routes
   - Test booking document upload/download

3. **Documents Feature**
   - Test payment document upload
   - Test flight ticket upload
   - Verify documents visible in admin bookings table
   - Test document downloads

4. **No Dev Server Connection**
   - Browser should NOT try to connect to `[::1]:5173` or `localhost:5173`
   - If it does, check:
     - `APP_ENV=production` in `.env`
     - `public/hot` file doesn't exist
     - `public/build/manifest.json` exists

## Troubleshooting

### Issue: CSS/JS not loading, trying to connect to dev server

**Symptoms:**
- CORS errors for `[::1]:5173`
- Styles not applying
- JavaScript not working

**Solutions:**
1. Verify `APP_ENV=production` in `.env`
2. Delete `public/hot` file if it exists
3. Verify `public/build/manifest.json` exists
4. Run `php artisan config:clear` after changing `.env`
5. Re-upload `public/build/` directory

### Issue: Documents not uploading/downloading

**Check:**
1. Storage directories exist: `storage/app/payment-documents/` and `storage/app/flight-tickets/`
2. Storage link exists: `public/storage` → `storage/app/public`
3. Permissions set correctly on storage directories
4. DualStorageService is working (files in both locations)

### Issue: Routes not working

**Solutions:**
1. Clear route cache: `php artisan route:clear`
2. Clear config cache: `php artisan config:clear`
3. Verify `.env` has correct `APP_URL`

## Quick Verification Commands (on server)

```bash
# Check environment
php artisan config:show app.env
# Should show: production

# Verify manifest exists
ls -la public/build/manifest.json

# Check storage link
ls -la public/storage

# Verify no hot file
ls -la public/hot
# Should show: No such file or directory
```

## Summary

✅ **All caches cleared locally**
✅ **Production assets built successfully**
✅ **Ready for deployment**

**Next Steps:**
1. Upload files to production server
2. Configure `.env` for production
3. Run migrations if needed
4. Clear caches on server
5. Verify everything works

---

**Build Date:** {{ date }}
**Build Output:** `public/build/` directory ready
**Tailwind:** Compiled and ready ✅

