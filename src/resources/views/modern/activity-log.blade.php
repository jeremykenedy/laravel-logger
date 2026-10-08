@extends('LaravelLogger::modern.layout', ['loggerTitle' => trans('LaravelLogger::laravel-logger.dashboard.title')])

@section('logger_content')
    @include('LaravelLogger::modern.filters')
    <section class="{{ $loggerClasses['card'] }}">
        <div class="logger-toolbar">
            <h2>{{ trans('LaravelLogger::laravel-logger.dashboard.title') }} @if(!config('LaravelLogger.loggerCursorPaginationEnabled'))<span class="logger-count">{{ $totalActivities }}</span>@endif</h2>
            @if(config('LaravelLogger.enableSubMenu'))
                <form method="POST" action="{{ route('clear-activity') }}" data-confirm="{{ trans('LaravelLogger::laravel-logger.modals.clearLog.message') }}">
                    @csrf @method('DELETE')
                    <button class="{{ $loggerClasses['button'] }}" type="submit">{{ trans('LaravelLogger::laravel-logger.dashboard.menu.clear') }}</button>
                </form>
            @endif
        </div>
        @include('LaravelLogger::modern.table')
    </section>
@endsection
