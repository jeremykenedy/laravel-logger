<div class="logger-table-scroll" tabindex="0" aria-label="{{ trans('LaravelLogger::laravel-logger.dashboard.title') }}">
    <table class="{{ $loggerClasses['table'] }}">
        <thead><tr>
            <th scope="col">{{ trans('LaravelLogger::laravel-logger.dashboard.labels.description') }}</th>
            <th scope="col">{{ trans('LaravelLogger::laravel-logger.dashboard.labels.user') }}</th>
            <th scope="col">{{ trans('LaravelLogger::laravel-logger.searchLabels.method') }}</th>
            <th scope="col">{{ trans('LaravelLogger::laravel-logger.searchLabels.ip') }}</th>
            <th scope="col">{{ trans('LaravelLogger::laravel-logger.time') }}</th>
        </tr></thead>
        <tbody>
            @forelse($activities as $activity)
                <tr>
                    <td>
                        @if(config('LaravelLogger.enableDrillDown'))<a href="{{ url('activity/'.(($cleared ?? false) ? 'cleared/' : '').'log/'.$activity->id) }}">{{ $activity->description }}</a>@else{{ $activity->description }}@endif
                        <span class="logger-route">{{ $activity->route }}</span>
                    </td>
                    <td>{{ $activity->userDetails ? $activity->userDetails->email : $activity->userType }}</td>
                    <td><span class="logger-method">{{ $activity->methodType }}</span></td>
                    <td>{{ $activity->ipAddress }}</td>
                    <td><time datetime="{{ $activity->created_at->toIso8601String() }}">{{ $activity->created_at->format('Y-m-d H:i:s') }}</time>@if($cleared ?? false)<span class="logger-route">{{ $activity->deleted_at }}</span>@endif</td>
                </tr>
            @empty
                <tr><td colspan="5" class="logger-empty">{{ trans('LaravelLogger::laravel-logger.noActivities') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($activities instanceof \Illuminate\Contracts\Pagination\Paginator)
    <nav class="logger-pagination" aria-label="{{ trans('LaravelLogger::laravel-logger.paginationLabel') }}">
        @if($activities->previousPageUrl())<a class="{{ $loggerClasses['button'] }}" href="{{ $activities->previousPageUrl() }}">{{ trans('LaravelLogger::laravel-logger.previous') }}</a>@endif
        @if($activities->nextPageUrl())<a class="{{ $loggerClasses['button'] }}" href="{{ $activities->nextPageUrl() }}">{{ trans('LaravelLogger::laravel-logger.next') }}</a>@endif
    </nav>
@endif
