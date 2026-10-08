@if(config('LaravelLogger.enableDateFiltering') || config('LaravelLogger.enableSearch'))
    <form class="{{ $loggerClasses['card'] }} logger-filters" method="GET" action="{{ route(($cleared ?? false) ? 'cleared' : 'activity') }}">
        @if(config('LaravelLogger.enableDateFiltering'))
            <label>{{ trans('LaravelLogger::laravel-logger.fromDate') }}<input class="{{ $loggerClasses['input'] }}" type="date" name="date_from" value="{{ request('date_from') }}"></label>
            <label>{{ trans('LaravelLogger::laravel-logger.toDate') }}<input class="{{ $loggerClasses['input'] }}" type="date" name="date_to" value="{{ request('date_to') }}"></label>
            <label>{{ trans('LaravelLogger::laravel-logger.quickPeriod') }}
                <select class="{{ $loggerClasses['input'] }}" name="period">
                    <option value="">{{ trans('LaravelLogger::laravel-logger.allTime') }}</option>
                    @foreach(['today' => 'today', 'yesterday' => 'yesterday', 'last_7_days' => 'last7Days', 'last_30_days' => 'last30Days', 'last_3_months' => 'last3Months', 'last_6_months' => 'last6Months', 'last_year' => 'lastYear'] as $value => $label)
                        <option value="{{ $value }}" @if(request('period') === $value) selected @endif>{{ trans('LaravelLogger::laravel-logger.'.$label) }}</option>
                    @endforeach
                </select>
            </label>
        @endif
        @if(config('LaravelLogger.enableSearch') && !($cleared ?? false))
            @foreach(['description' => 'description', 'method' => 'method', 'route' => 'route', 'ip' => 'ip_address'] as $field => $name)
                @if(in_array($field, explode(',', config('LaravelLogger.searchFields'))))
                    <label>{{ trans('LaravelLogger::laravel-logger.searchLabels.'.$field) }}<input class="{{ $loggerClasses['input'] }}" type="text" name="{{ $name }}" value="{{ request($name) }}"></label>
                @endif
            @endforeach
            @if(in_array('user', explode(',', config('LaravelLogger.searchFields'))))
                <label>{{ trans('LaravelLogger::laravel-logger.dashboard.labels.user') }}<input class="{{ $loggerClasses['input'] }}" type="number" name="user" min="1" value="{{ request('user') }}" list="logger-users"></label>
                <datalist id="logger-users">@foreach($users as $user)<option value="{{ $user->{config('LaravelLogger.defaultUserIDField')} }}">{{ $user->email }}</option>@endforeach</datalist>
            @endif
        @endif
        <div class="logger-actions">
            <button class="{{ $loggerClasses['primary'] }}" type="submit">{{ trans('LaravelLogger::laravel-logger.filter') }}</button>
            <a class="{{ $loggerClasses['button'] }}" href="{{ route(($cleared ?? false) ? 'cleared' : 'activity') }}">{{ trans('LaravelLogger::laravel-logger.clearFilters') }}</a>
        </div>
    </form>
@endif
@if(config('LaravelLogger.enableExport') && !($cleared ?? false))
    <div class="logger-export" aria-label="{{ trans('LaravelLogger::laravel-logger.exportData') }}">
        <span>{{ trans('LaravelLogger::laravel-logger.exportData') }}</span>
        @foreach(['csv' => 'exportCSV', 'json' => 'exportJSON', 'excel' => 'exportExcel'] as $format => $label)
            <a class="{{ $loggerClasses['button'] }}" href="{{ route('export-activity', array_merge(request()->query(), ['format' => $format])) }}">{{ trans('LaravelLogger::laravel-logger.'.$label) }}</a>
        @endforeach
    </div>
@endif
