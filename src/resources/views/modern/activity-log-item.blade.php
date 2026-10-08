@extends('LaravelLogger::modern.layout', ['loggerTitle' => trans('LaravelLogger::laravel-logger.drilldown.title', ['id' => $activity->id])])

@section('logger_content')
    <a class="{{ $loggerClasses['button'] }}" href="{{ route($isClearedEntry ? 'cleared' : 'activity') }}">{{ trans('LaravelLogger::laravel-logger.drilldown.buttons.back') }}</a>
    <section class="{{ $loggerClasses['card'] }} logger-details">
        <h2>{{ trans('LaravelLogger::laravel-logger.drilldown.title-details') }}</h2>
        <dl>
            @foreach(['id', 'description', 'details', 'route', 'ipAddress', 'userAgent', 'locale', 'referer', 'methodType', 'created_at', 'updated_at', 'deleted_at'] as $field)
                <dt>{{ trans('LaravelLogger::laravel-logger.detailLabels.'.$field) }}</dt><dd>{{ $activity->{$field} ?? '-' }}</dd>
            @endforeach
            <dt>{{ trans('LaravelLogger::laravel-logger.dashboard.labels.user') }}</dt><dd>{{ $userDetails ? $userDetails->email : $activity->userType }}</dd>
            @if(config('LaravelLogger.rolesEnabled') && $userDetails)
                <dt>{{ trans('LaravelLogger::laravel-logger.drilldown.labels.userRoles') }}</dt><dd>@foreach($userDetails->roles as $role){{ $role->name }} @endforeach</dd>
            @endif
            @foreach($ipAddressDetails ?? [] as $label => $value)<dt>{{ $label }}</dt><dd>{{ $value }}</dd>@endforeach
        </dl>
    </section>
    @if(!$isClearedEntry)
        <section class="{{ $loggerClasses['card'] }}">
            <div class="logger-toolbar"><h2>{{ trans('LaravelLogger::laravel-logger.drilldown.title-user-activity') }}</h2></div>
            @include('LaravelLogger::modern.table', ['activities' => $userActivities])
        </section>
    @endif
@endsection
