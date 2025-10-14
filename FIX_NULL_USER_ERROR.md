# Fix "Attempt to read property 'id' on null" Error

## Problem
When using `Auth::login($user)` from scheduled tasks or other non-HTTP contexts, the Laravel Logger package would throw an error:

```
Attempt to read property "id" on null
src\App\Http\Traits\ActivityLogger.php:29
```

## Root Cause
The issue was caused by using `Request::user()` instead of `Auth::user()` in the ActivityLogger trait. When running code outside of HTTP request context (like scheduled tasks), `Request::user()` returns `null` even when a user is authenticated via `Auth::login()`.

## Solution
Changed `Request::user()` to `Auth::user()` in the ActivityLogger trait:

### Before (Problematic):
```php
if (Auth::check()) {
    $userType = trans('LaravelLogger::laravel-logger.userTypes.registered');
    $userIdField = config('LaravelLogger.defaultUserIDField');
    $userId = Request::user()->{$userIdField}; // ❌ Returns null in non-HTTP context
}
```

### After (Fixed):
```php
if (Auth::check()) {
    $userType = trans('LaravelLogger::laravel-logger.userTypes.registered');
    $userIdField = config('LaravelLogger.defaultUserIDField');
    $userId = Auth::user()->{$userIdField}; // ✅ Works in all contexts
}
```

## Why This Fix Works

### `Request::user()` vs `Auth::user()`:

- **`Request::user()`**: Only works when there's an active HTTP request with user binding
- **`Auth::user()`**: Works in any context where a user is authenticated via `Auth::login()`

### Use Cases Where This Matters:

1. **Scheduled Tasks**: Running `Auth::login($user)` in cron jobs
2. **Artisan Commands**: Authenticating users in console commands
3. **Queue Jobs**: Processing jobs with authenticated users
4. **API Calls**: Making internal API calls with authenticated users
5. **Testing**: Unit tests that authenticate users without HTTP requests

## Benefits:

- ✅ **Fixes Null Pointer Exception** - No more "property 'id' on null" errors
- ✅ **Works in All Contexts** - HTTP requests, scheduled tasks, commands, etc.
- ✅ **No Breaking Changes** - Existing functionality remains unchanged
- ✅ **Better Reliability** - More robust user authentication handling

## Testing:

### Before Fix (Would Fail):
```php
// In a scheduled task
Auth::login($user);
ActivityLogger::activity('Test activity'); // ❌ Throws error
```

### After Fix (Works):
```php
// In a scheduled task
Auth::login($user);
ActivityLogger::activity('Test activity'); // ✅ Works perfectly
```

## References:
- [GitHub Issue #149](https://github.com/jeremykenedy/laravel-logger/issues/149)
- [Laravel Authentication Documentation](https://laravel.com/docs/authentication)
- [Laravel Request vs Auth Facades](https://laravel.com/docs/facades)

## Impact:
This fix resolves the issue reported in GitHub Issue #149 and makes the Laravel Logger package more reliable when used in non-HTTP contexts.
