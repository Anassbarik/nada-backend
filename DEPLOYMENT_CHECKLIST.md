# Deployment Checklist - Vite/Tailwind Production Build

## Pre-Deployment Steps

### 1. Build Assets
```bash
npm run build
```

This will:
- Build CSS and JS assets
- Generate `public/build/manifest.json`
- Remove `public/hot` file (prevents dev server connection)

### 2. Verify Build Output
Check that these files exist:
- ✅ `public/build/manifest.json`
- ✅ `public/build/assets/app-*.css`
- ✅ `public/build/assets/app-*.js`
- ❌ `public/hot` should NOT exist

### 3. Environment Configuration

**CRITICAL:** Ensure your production `.env` file has:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seminairexpo.com
```

**DO NOT** set:
- ❌ `APP_ENV=local` (will cause dev server connection)
- ❌ `APP_DEBUG=true` (security risk)

### 4. Files to Upload

Make sure to include in your deployment:
- ✅ `public/build/` directory (entire folder)
- ✅ `.env` file with `APP_ENV=production`
- ✅ All other application files

**DO NOT** upload:
- ❌ `public/hot` (should not exist after build)
- ❌ `node_modules/` (not needed on server)
- ❌ `.env.example` (use actual `.env`)

## Troubleshooting

### Issue: CSS/JS not loading, trying to connect to `[::1]:5173`

**Symptoms:**
- CORS errors trying to access `http://[::1]:5173`
- Styles not applying
- JavaScript not working

**Causes & Solutions:**

1. **`APP_ENV=local` in production `.env`**
   - ✅ Fix: Change to `APP_ENV=production` in production `.env`
   - ✅ Run: `php artisan config:clear` after changing

2. **`public/hot` file exists on server**
   - ✅ Fix: Delete `public/hot` file on server
   - ✅ Or: Re-run `npm run build` locally and re-upload `public/build/`

3. **Missing `public/build/manifest.json`**
   - ✅ Fix: Run `npm run build` and upload `public/build/` directory

4. **Wrong `APP_URL`**
   - ✅ Fix: Set `APP_URL=https://seminairexpo.com` in production `.env`

### Quick Fix Commands (on server)

```bash
# Check current environment
php artisan config:show app.env

# Clear config cache (after changing .env)
php artisan config:clear
php artisan cache:clear

# Verify manifest exists
ls -la public/build/manifest.json

# Remove hot file if it exists
rm -f public/hot
```

## Build Process

The build script (`npm run build`) does:
1. `vite build` - Compiles assets to `public/build/`
2. `node scripts/cleanup-vite.mjs` - Removes `public/hot` and verifies manifest

## Verification

After deployment, verify:
1. ✅ Visit `https://seminairexpo.com` - styles should load
2. ✅ Check browser console - no CORS errors
3. ✅ Check Network tab - assets loading from `/build/assets/` not `[::1]:5173`
4. ✅ Run: `php artisan config:show app.env` - should show `production`

## Common Mistakes

❌ **Building on server instead of locally**
- Build locally, then upload `public/build/`

❌ **Forgetting to upload `public/build/` directory**
- This directory is required for production

❌ **Setting `APP_ENV=local` in production**
- Always use `APP_ENV=production`

❌ **Uploading `public/hot` file**
- This file should not exist in production

❌ **Not clearing config cache after `.env` changes**
- Run `php artisan config:clear` after changing environment variables

