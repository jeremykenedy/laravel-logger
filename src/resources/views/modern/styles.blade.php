@if($loggerCss !== 'tailwind' && config('LaravelLogger.enableBootstrapCssCDN'))
    <link rel="stylesheet" href="{{ $loggerCss === 'bootstrap5' ? 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css' : config('LaravelLogger.bootstrapCssCDN') }}">
@endif
<link rel="stylesheet" href="{{ rtrim(config('LaravelLogger.assetUrl', '/vendor/laravel-logger'), '/') }}/dashboard.css">
