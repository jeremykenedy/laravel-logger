@extends('LaravelLogger::modern.layout', ['loggerTitle' => trans('LaravelLogger::laravel-logger.dashboardCleared.title')])

@section('logger_content')
    @include('LaravelLogger::modern.filters', ['cleared' => true, 'users' => collect()])
    <section class="{{ $loggerClasses['card'] }}">
        <div class="logger-toolbar">
            <h2>{{ trans('LaravelLogger::laravel-logger.dashboardCleared.title') }} @if(!config('LaravelLogger.loggerCursorPaginationEnabled'))<span class="logger-count">{{ $totalActivities }}</span>@endif</h2>
            @if(config('LaravelLogger.enableSubMenu'))
                <div class="logger-actions">
                    <form method="POST" action="{{ route('restore-activity') }}" data-confirm="{{ trans('LaravelLogger::laravel-logger.modals.restoreLog.message') }}">
                        @csrf
                        <button class="{{ $loggerClasses['button'] }}" type="submit">{{ trans('LaravelLogger::laravel-logger.dashboardCleared.menu.restoreAll') }}</button>
                    </form>
                    <form method="POST" action="{{ route('destroy-activity') }}" data-confirm="{{ trans('LaravelLogger::laravel-logger.modals.deleteLog.message') }}">
                        @csrf @method('DELETE')
                        <button class="{{ $loggerClasses['button'] }} logger-danger" type="submit">{{ trans('LaravelLogger::laravel-logger.dashboardCleared.menu.deleteAll') }}</button>
                    </form>
                </div>
            @endif
        </div>
        @include('LaravelLogger::modern.table', ['cleared' => true])
    </section>
@endsection
