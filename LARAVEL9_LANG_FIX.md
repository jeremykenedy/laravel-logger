# Language publishing

Package translations live in `src/lang` and retain the `LaravelLogger` namespace.

```bash
php artisan vendor:publish --tag=LaravelLogger
```

On Laravel 9 and newer, the main tag publishes to `lang/vendor/LaravelLogger`. Existing applications using the older directory can publish there with:

```bash
php artisan vendor:publish --tag=LaravelLogger-legacy
```

The service provider keeps the fallback for the former package language directory. Published translations continue to override package translations. View and translation registration paths are normalized without trailing slashes.
