@extends(config('LaravelLogger.loggerBladeExtended'))

@if(config('LaravelLogger.bladePlacement') === 'stack')
    @push(config('LaravelLogger.bladePlacementCss'))
        @include('LaravelLogger::modern.styles')
    @endpush
    @push(config('LaravelLogger.bladePlacementJs'))
        <script src="{{ rtrim(config('LaravelLogger.assetUrl', '/vendor/laravel-logger'), '/') }}/dashboard.js" defer></script>
    @endpush
@else
    @section(config('LaravelLogger.bladePlacementCss'))
        @include('LaravelLogger::modern.styles')
    @endsection
    @section(config('LaravelLogger.bladePlacementJs'))
        <script src="{{ rtrim(config('LaravelLogger.assetUrl', '/vendor/laravel-logger'), '/') }}/dashboard.js" defer></script>
    @endsection
@endif

@section('template_title', trans('LaravelLogger::laravel-logger.dashboard.title'))
@section('content')
    <main class="logger-dashboard {{ $loggerClasses['container'] }}" data-theme="{{ config('LaravelLogger.theme', 'system') }}" data-css="{{ $loggerCss }}">
        <header class="logger-heading">
            <div><h1>{{ $loggerTitle }}</h1><p>{{ trans('LaravelLogger::laravel-logger.dashboard.subtitle') }}</p></div>
            @if(config('LaravelLogger.enableThemeToggle', true))
                <button type="button" class="logger-theme" data-theme-toggle data-theme-label="{{ trans('LaravelLogger::laravel-logger.themeToggle') }}" aria-label="{{ trans('LaravelLogger::laravel-logger.themeToggle') }}: {{ trans('LaravelLogger::laravel-logger.themes.'.config('LaravelLogger.theme', 'system')) }}" title="{{ trans('LaravelLogger::laravel-logger.themes.'.config('LaravelLogger.theme', 'system')) }}">
                    @foreach(['light', 'dark', 'system'] as $theme)
                        <svg data-theme-icon="{{ $theme }}" data-label="{{ trans('LaravelLogger::laravel-logger.themes.'.$theme) }}" @if(config('LaravelLogger.theme', 'system') !== $theme) hidden @endif width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                            @if($theme === 'light')
                                <circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/>
                            @elseif($theme === 'dark')
                                <path d="M20.5 13A8.5 8.5 0 0 1 11 3.5 8.5 8.5 0 1 0 20.5 13Z"/>
                            @else
                                <rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8m-4-4v4"/>
                            @endif
                        </svg>
                    @endforeach
                </button>
            @endif
        </header>
        @if(config('LaravelLogger.enableSubMenu'))
            <nav class="logger-nav" aria-label="{{ trans('LaravelLogger::laravel-logger.dashboard.menu.alt') }}">
                <a href="{{ route('activity') }}" @if(request()->is('activity')) aria-current="page" @endif>{{ trans('LaravelLogger::laravel-logger.dashboard.title') }}</a>
                <a href="{{ route('cleared') }}" @if(request()->is('activity/cleared*')) aria-current="page" @endif>{{ trans('LaravelLogger::laravel-logger.dashboard.menu.show') }}</a>
            </nav>
        @endif
        @if(config('LaravelLogger.enablePackageFlashMessageBlade'))
            @foreach(['success', 'error'] as $status)
                @if(session($status))<p class="logger-notice" role="status">{{ session($status) }}</p>@endif
            @endforeach
        @endif
        @if($errors->any())<div class="logger-notice" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        @yield('logger_content')
    </main>
@endsection
