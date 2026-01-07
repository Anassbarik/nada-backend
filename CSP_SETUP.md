# Content Security Policy (CSP) Setup Guide

This guide explains how to enable CSP headers for XSS protection when using Sanctum tokens.

## Overview

CSP (Content Security Policy) is a security feature that helps prevent XSS attacks by controlling which resources can be loaded and executed. The CSP middleware is already set up but **disabled by default** to allow development.

## Current Status

✅ **CSP Middleware**: Created and registered  
✅ **Configuration**: Added to `config/app.php`  
✅ **Environment-aware**: Automatically adjusts for dev/production  
❌ **Currently Disabled**: Set `CSP_ENABLED=false` in `.env`

## How to Enable CSP

### Step 1: Test in Report-Only Mode (Recommended First)

Add to your `.env` file:

```env
CSP_ENABLED=true
CSP_REPORT_ONLY=true
```

This mode will **report violations without blocking** resources, allowing you to see what needs to be fixed.

### Step 2: Check Browser Console

1. Open your browser's Developer Tools (F12)
2. Check the Console tab for CSP violation reports
3. Look for messages like: `Content Security Policy: The page's settings blocked...`

### Step 3: Fix Any Violations

Common issues and fixes:

- **Vite HMR not working**: Already handled - dev mode allows `localhost:5173`
- **External scripts blocked**: Already allowed - `unpkg.com` is whitelisted
- **Inline styles blocked**: Already allowed - `'unsafe-inline'` for styles
- **API calls blocked**: Check `connect-src` directive

### Step 4: Enable Full CSP (Production)

Once you've verified everything works in report-only mode:

```env
CSP_ENABLED=true
CSP_REPORT_ONLY=false
```

## Configuration Details

### What's Allowed (Current Configuration)

**Scripts:**
- `'self'` - Same origin
- `'unsafe-inline'` - Inline scripts (required for Alpine.js, Livewire)
- `'unsafe-eval'` - eval() calls (required for Vite HMR)
- `https://unpkg.com` - Lucide icons CDN
- `http://localhost:5173` (dev only) - Vite dev server

**Styles:**
- `'self'` - Same origin
- `'unsafe-inline'` - Inline styles (required for Tailwind CSS)
- `https://fonts.bunny.net` - Google Fonts alternative
- `http://localhost:5173` (dev only) - Vite dev server

**Fonts:**
- `'self'` - Same origin
- `data:` - Data URIs
- `https://fonts.bunny.net` - External fonts

**Images:**
- `'self'` - Same origin
- `data:` - Data URIs
- `https:` - Any HTTPS image
- `blob:` - Blob URLs

**Connections (API calls):**
- `'self'` - Same origin
- Sanctum stateful domains (from `config/sanctum.php`)
- `http://localhost:5173` (dev only) - Vite HMR WebSocket

### Environment-Specific Behavior

**Development (`APP_ENV=local`):**
- Automatically allows Vite dev server (`localhost:5173`)
- Allows WebSocket connections for HMR
- More permissive for easier debugging

**Production (`APP_ENV=production`):**
- Stricter CSP rules
- Forces HTTPS (`upgrade-insecure-requests`)
- No Vite dev server access

## Security Considerations

### Current Setup (Balanced Security)

✅ **HttpOnly Cookies**: Sanctum uses HttpOnly cookies (not accessible via JavaScript)  
✅ **CSRF Protection**: Laravel's CSRF tokens are included  
✅ **Same-Origin Policy**: Most resources restricted to same origin  
⚠️ **'unsafe-inline'**: Required for Alpine.js/Livewire, acceptable for authenticated admin panels  
⚠️ **'unsafe-eval'**: Required for Vite HMR, only in development

### Future Improvements (Optional)

For even stronger security, you could:

1. **Use Nonces Instead of 'unsafe-inline'**:
   - Generate nonces for each request
   - Add nonces to inline scripts/styles
   - More secure but requires view updates

2. **Remove 'unsafe-eval' in Production**:
   - Only needed for Vite HMR
   - Production builds don't need it
   - Can be conditionally removed

3. **Implement CSP Reporting Endpoint**:
   - Collect violation reports
   - Monitor and adjust policies
   - Useful for production debugging

## Troubleshooting

### Styles Not Loading

**Symptom**: Tailwind CSS classes not applying

**Solution**: 
- Check if `CSP_ENABLED=true` is set
- Verify `style-src` includes `'unsafe-inline'`
- Check browser console for CSP violations

### Scripts Not Working

**Symptom**: Alpine.js or Livewire not functioning

**Solution**:
- Ensure `script-src` includes `'unsafe-inline'` and `'unsafe-eval'`
- Check that external CDNs are whitelisted
- Verify Vite dev server is allowed (dev mode)

### Vite HMR Not Working

**Symptom**: Changes not hot-reloading in development

**Solution**:
- Ensure `APP_ENV=local` in `.env`
- Check that `connect-src` allows `ws://localhost:5173`
- Verify Vite port matches (default: 5173)

### API Calls Blocked

**Symptom**: Sanctum authentication failing

**Solution**:
- Verify `connect-src` includes your API domain
- Check `config/sanctum.php` stateful domains
- Ensure CORS is properly configured

## Testing Checklist

Before enabling CSP in production:

- [ ] Test in report-only mode first
- [ ] Verify all pages load correctly
- [ ] Check Alpine.js components work
- [ ] Test Livewire interactions
- [ ] Verify form submissions work
- [ ] Test Sanctum authentication
- [ ] Check external resources (fonts, icons) load
- [ ] Verify image uploads work
- [ ] Test in different browsers
- [ ] Monitor browser console for violations

## Quick Reference

**Enable CSP (Report-Only):**
```env
CSP_ENABLED=true
CSP_REPORT_ONLY=true
```

**Enable CSP (Full Protection):**
```env
CSP_ENABLED=true
CSP_REPORT_ONLY=false
```

**Disable CSP:**
```env
CSP_ENABLED=false
```

**Check Current Status:**
```bash
php artisan config:show app.csp_enabled
php artisan config:show app.csp_report_only
```

## Additional Resources

- [MDN: Content Security Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)
- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [CSP Evaluator](https://csp-evaluator.withgoogle.com/) - Test your CSP policy

