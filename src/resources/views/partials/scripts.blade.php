
@if(config('LaravelLogger.enablejQueryCDN'))
    <script type="text/javascript" src="{{ config('LaravelLogger.JQueryCDN') }}"></script>
@endif

@if(config('LaravelLogger.enableBootstrapJsCDN'))
    <script type="text/javascript" src="{{ config('LaravelLogger.bootstrapJsCDN') }}"></script>
@endif

@if(config('LaravelLogger.enablePopperJsCDN'))
    <script type="text/javascript" src="{{ config('LaravelLogger.popperJsCDN') }}"></script>
@endif

@if(config('LaravelLogger.loggerDatatables'))
    @if (count($userActivities) > 10)
        @include('LaravelLogger::scripts.datatables')
    @endif
@endif