# Laravel 9+ Language Directory Fix

## Problem
In Laravel 9+, the default language directory has moved from `/resources/lang` to `/lang`. The Laravel Logger package was still using the old structure, which could cause confusion and conflicts.

## Solution
Updated the package to support both Laravel 9+ and older versions:

### Changes Made:

1. **Moved language files** from `/src/resources/lang/` to `/src/lang/`
2. **Updated ServiceProvider** to detect and use the appropriate structure
3. **Added backward compatibility** for older Laravel versions
4. **Updated publish commands** to support both structures

### ServiceProvider Updates:

```php
// Load translations from new Laravel 9+ structure if available, fallback to old structure
if (is_dir(__DIR__.'/lang/')) {
    $this->loadTranslationsFrom(__DIR__.'/lang/', 'LaravelLogger');
} else {
    $this->loadTranslationsFrom(__DIR__.'/resources/lang/', 'LaravelLogger');
}
```

### Publishing Updates:

```php
// Publish language files to Laravel 9+ structure if available, fallback to old structure
if (is_dir(__DIR__.'/lang/')) {
    // Laravel 9+ structure
    $this->publishes([
        __DIR__.'/lang' => base_path('lang/vendor/'.$publishTag),
    ], $publishTag);
    
    // Also publish to old structure for backward compatibility
    $this->publishes([
        __DIR__.'/lang' => base_path('resources/lang/vendor/'.$publishTag),
    ], $publishTag.'-legacy');
} else {
    // Old structure fallback
    $this->publishes([
        __DIR__.'/resources/lang' => base_path('resources/lang/vendor/'.$publishTag),
    ], $publishTag);
}
```

## Benefits:

- ✅ **Laravel 9+ Compatible** - Uses new `/lang` directory structure
- ✅ **Backward Compatible** - Still works with older Laravel versions
- ✅ **No Breaking Changes** - Existing installations continue to work
- ✅ **Future Proof** - Ready for Laravel 10+ and beyond

## Usage:

### Laravel 9+ Projects:
```bash
php artisan vendor:publish --tag=LaravelLogger
# Language files will be published to /lang/vendor/LaravelLogger/
```

### Legacy Laravel Projects:
```bash
php artisan vendor:publish --tag=LaravelLogger-legacy
# Language files will be published to /resources/lang/vendor/LaravelLogger/
```

## References:
- [Laravel 9 Language Directory Change](https://laravel.com/docs/9.x/upgrade#language-directory)
- [GitHub Issue #151](https://github.com/jeremykenedy/laravel-logger/issues/151)
- [Stack Overflow Discussion](https://stackoverflow.com/questions/71084830/laravel-9-app-upgraded-from-8-lang-directory-not-working-as-expected)
