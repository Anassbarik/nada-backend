# Fix: 500 Error When Creating Accommodation

## Problem
After deploying to production, creating a new Accommodation resulted in a 500 server error.

## Root Cause
The `store` method in `EventController` was trying to access `$validated['organizer_name']` and `$validated['organizer_email']` (lines 95-96), but these fields were **not included in the validation rules**. This caused an "Undefined array key" error, resulting in a 500 error.

## Solution Applied
Added the missing validation rules for organizer fields:

```php
'organizer_name' => 'required|string|max:255',
'organizer_email' => 'required|email|max:255|unique:users,email',
```

## Files Modified
- `app/Http/Controllers/Admin/EventController.php` - Added validation rules for `organizer_name` and `organizer_email`

## Additional Checks Performed

### ✅ Storage Directories
The `DualStorageService` automatically creates directories if they don't exist, so storage directories should not be an issue.

### ✅ Accommodation Model Events
The `Accommodation` model has a `created` event that tries to copy content from a reference accommodation. This should work fine as long as:
- The reference accommodation exists (seafood4africa or any accommodation with "Seafood" in the name)
- If no reference exists, the event simply skips content copying (no error)

## Testing Checklist

After deploying this fix, verify:

1. ✅ **Create Accommodation Form**
   - Form loads correctly
   - All fields are visible (including organizer name and email)

2. ✅ **Create Accommodation Submission**
   - Fill in all required fields
   - Submit the form
   - Should create successfully without 500 error

3. ✅ **Organizer Creation**
   - Verify organizer user is created in database
   - Verify organizer is linked to accommodation
   - Verify organizer credentials PDF is generated (if applicable)

4. ✅ **File Uploads**
   - Test organizer logo upload
   - Test accommodation logo upload
   - Test banner upload
   - Verify files are stored in both storage locations

5. ✅ **Content Pages**
   - If reference accommodation exists, content pages should be copied
   - If no reference exists, accommodation should still be created (just without content pages)

## Production Deployment Steps

1. Upload the fixed `app/Http/Controllers/Admin/EventController.php` file
2. Clear caches on production:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   ```
3. Test creating a new accommodation

## If Issues Persist

If you still get a 500 error after this fix:

1. **Check Laravel Logs**
   - Location: `storage/logs/laravel.log`
   - Look for the actual error message

2. **Check Server Error Logs**
   - Check Apache/Nginx error logs
   - Check PHP error logs

3. **Common Issues:**
   - **Database connection**: Verify `.env` database credentials
   - **Permissions**: Check `storage/` and `bootstrap/cache/` permissions (775)
   - **Missing migrations**: Run `php artisan migrate` if needed
   - **Storage link**: Ensure `php artisan storage:link` has been run

4. **Enable Debug Mode Temporarily**
   - Set `APP_DEBUG=true` in `.env` (for debugging only!)
   - This will show the actual error message
   - **Remember to set it back to `false` after debugging!**

## Related Code

The form in `resources/views/admin/events/create.blade.php` includes:
- `organizer_name` field (line 140) - **Required**
- `organizer_email` field (line 147) - **Required**

These fields are now properly validated in the controller.

