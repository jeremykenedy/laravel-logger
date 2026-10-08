# Testing

Install development dependencies with `composer install`, then run:

```bash
composer test
composer lint
composer analyse
npm ci
npx playwright install chromium
npm run test:browser
```

The PHP suite boots the package with Orchestra Testbench and SQLite `:memory:` by default. The MySQL job uses a disposable `laravel_logger_testing` database on a local MySQL 8.4 service. The test configuration fixes the database name and resets its tables between tests. It exercises real routes, authentication, role middleware, activity logging, all authentication listeners, exclusions, related records, date/search filters, cursor pagination, exports, soft deletion, restoration, and permanent deletion. Excel tests open the generated workbook and inspect its XML. Command tests use temporary environment files and verify preservation of configuration and custom views. Crawler tests run the bundled detector against the reference browser, crawler, and client-hint samples, then verify logged fields, alternate headers, request changes, custom bindings, and legacy facade/provider aliases without the external packages installed.

Browser tests use a local fixture server that boots Testbench, creates an in-memory database, registers the real package routes, and renders the real package views. The fixture is only reachable on the loopback address. Browser cases cover legacy Bootstrap 3/4 and modern Bootstrap 5/Tailwind views, desktop/mobile layouts, theme changes, date filters, details, and exports.

GitHub Actions runs Laravel 8 through 13 across compatible PHP versions, including Laravel 8 on PHP 7.3, plus MySQL 8.4, formatting, static analysis, Composer validation, dependency auditing, browser tests, and coverage. Scrutinizer runs the same suite with MySQL and coverage enabled; its worker SQLite library is too old for current Laravel schema inspection. The PHP 7.3 job omits Pint, which requires PHP 8; formatting runs separately on current PHP. Legacy matrix jobs allow Composer to resolve historical framework dependencies; the current dependency audit remains strict. Composer's runtime PHP constraint and the original default framework are unchanged.

To generate coverage when a coverage driver is enabled:

```bash
vendor/bin/phpunit --coverage-clover coverage/clover.xml
```

Historical migrations are kept unchanged. The existing 2025 anonymous migration requires Laravel 8.37 or later to load; its rollback can fail on SQLite because it drops an indexed column before dropping the index. The suite runs forward migrations against disposable databases rather than applying that rollback to user data.
