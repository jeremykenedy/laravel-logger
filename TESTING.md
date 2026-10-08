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

The PHP suite boots the package with Orchestra Testbench and SQLite `:memory:`. It exercises real routes, authentication, role middleware, activity logging, all authentication listeners, exclusions, related records, date/search filters, cursor pagination, exports, soft deletion, restoration, and permanent deletion. Excel tests open the generated workbook and inspect its XML. Command tests use temporary environment files and verify preservation of configuration and custom views.

Browser tests use a local fixture server that boots Testbench, creates an in-memory database, registers the real package routes, and renders the real package views. The fixture is only reachable on the loopback address. Browser cases cover legacy Bootstrap 3/4 and modern Bootstrap 5/Tailwind views, desktop/mobile layouts, theme changes, date filters, details, and exports.

GitHub Actions runs Laravel 8 through 13 across compatible PHP versions, plus formatting, static analysis, Composer validation, dependency auditing, browser tests, and coverage. The lowest-dependency job uses crawler-detect 1.1 or later, since earlier providers call Laravel's removed `share()` method. Legacy matrix jobs allow Composer to resolve historical framework dependencies; the current dependency audit remains strict. Composer's runtime PHP constraint and the original default framework are unchanged.

To generate coverage when a coverage driver is enabled:

```bash
vendor/bin/phpunit --coverage-clover coverage/clover.xml
```

Historical migrations are kept unchanged. The existing 2025 anonymous migration requires PHP 8.0 for execution; its rollback can fail on SQLite because it drops an indexed column before dropping the index. The suite runs forward migrations against disposable databases rather than applying that rollback to user data.
