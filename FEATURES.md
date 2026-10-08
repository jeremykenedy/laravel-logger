# Filtering and exports

Activity lists and exports share description, user, method, route, IP, and date filters. Date ranges include both endpoints. Preset periods are `today`, `yesterday`, `last_7_days`, `last_30_days`, `last_3_months`, `last_6_months`, and `last_year`. Date ranges and periods can be combined. Unknown periods retain the existing unfiltered behavior.

Dates must use `YYYY-MM-DD`. Malformed dates and array-valued search inputs produce validation errors. Set `enableDateFiltering` or `enableSearch` to false to disable the relevant filters. Page links retain filter parameters; cursor pagination sorts by timestamp and ID so records with identical timestamps are not skipped.

CSV exports quote fields and prefix spreadsheet formula-like values with an apostrophe. JSON preserves the existing export keys. Excel exports contain an actual XLSX workbook and store activity values as text cells. Exports exclude soft-deleted activity. Setting `enableExport=false` disables the endpoint as well as its controls.

Modern dashboards use the existing routes and authentication middleware. Legacy Bootstrap 3/4 views remain available. Modern views add Bootstrap 5 and Tailwind class choices, date filters, export links, explicit activity links, and theme controls without an additional frontend runtime.
