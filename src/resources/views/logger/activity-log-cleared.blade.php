@extends(config('LaravelLogger.loggerBladeExtended'))

@section('template_title')
    @lang('LaravelLogger::laravel-logger.dashboardCleared.title')
@endsection

@if(config('LaravelLogger.enableBladeJsPlacement'))
    @section('template_linked_css')
        @include('LaravelLogger::partials.styles')
    @endsection
@else
    @include('LaravelLogger::partials.styles')
@endif

@php
    switch (config('LaravelLogger.bootstapVersion')) {
        case '4':
            $containerClass = 'card';
            $containerHeaderClass = 'card-header';
            $containerBodyClass = 'card-body';
            break;
        case '3':
        default:
            $containerClass = 'panel panel-default';
            $containerHeaderClass = 'panel-heading';
            $containerBodyClass = 'panel-body';
    }
    $bootstrapCardClasses = (is_null(config('LaravelLogger.bootstrapCardClasses')) ? '' : config('LaravelLogger.bootstrapCardClasses'));
@endphp

@section('content')

    <div class="container-fluid">

        @if(config('LaravelLogger.enablePackageFlashMessageBlade'))
            @include('LaravelLogger::partials.form-status')
        @endif

        <div class="row">
            <div class="col-sm-12">
                <div class="{{ $containerClass }} {{ $bootstrapCardClasses }}">
                    <div class="{{ $containerHeaderClass }}">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span>
                                @lang('LaravelLogger::laravel-logger.dashboardCleared.title')
                                <sup class="label">
                                    {{ $totalActivities }} @lang('LaravelLogger::laravel-logger.dashboardCleared.subtitle')
                                </sup>
                            </span>
                            <div class="btn-group pull-right btn-group-xs">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v fa-fw" aria-hidden="true"></i>
                                    <span class="sr-only">
                                        @lang('LaravelLogger::laravel-logger.dashboard.menu.alt')
                                    </span>
                                </button>
                                @if(config('LaravelLogger.bootstapVersion') == '4')
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a href="{{route('activity')}}" class="dropdown-item">
                                            <span class="text-primary">
                                                <i class="fa fa-fw fa-mail-reply" aria-hidden="true"></i>
                                                @lang('LaravelLogger::laravel-logger.dashboard.menu.back')
                                            </span>
                                        </a>
                                        @if($totalActivities)
                                            @include('LaravelLogger::forms.delete-activity-log')
                                            @include('LaravelLogger::forms.restore-activity-log')
                                        @endif
                                    </div>
                                @else
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="{{route('activity')}}">
                                                <span class="text-primary">
                                                    <i class="fa fa-fw fa-mail-reply" aria-hidden="true"></i>
                                                    @lang('LaravelLogger::laravel-logger.dashboard.menu.back')
                                                </span>
                                            </a>
                                        </li>
                                        @if($totalActivities)
                                            <li>
                                                @include('LaravelLogger::forms.delete-activity-log')
                                            </li>
                                            <li>
                                                @include('LaravelLogger::forms.restore-activity-log')
                                            </li>
                                        @endif
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        @include('LaravelLogger::logger.partials.activity-table', ['activities' => $activities, 'hoverable' => true])
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('LaravelLogger::modals.confirm-modal', ['formTrigger' => 'confirmDelete', 'modalClass' => 'danger', 'actionBtnIcon' => 'fa-trash-o'])
    @include('LaravelLogger::modals.confirm-modal', ['formTrigger' => 'confirmRestore', 'modalClass' => 'success', 'actionBtnIcon' => 'fa-check'])

@endsection

@if(config('LaravelLogger.enableBladeJsPlacement'))
    @section('footer_scripts')
@endif

    @if(config('LaravelLogger.enablejQueryCDN'))
        <script type="text/javascript" src="{{ config('LaravelLogger.JQueryCDN') }}"></script>
    @endif

    @if(config('LaravelLogger.enableBootstrapJsCDN'))
        <script type="text/javascript" src="{{ config('LaravelLogger.bootstrapJsCDN') }}"></script>
    @endif

    @if(config('LaravelLogger.enablePopperJsCDN'))
        <script type="text/javascript" src="{{ config('LaravelLogger.popperJsCDN') }}"></script>
    @endif

    @include('LaravelLogger::scripts.confirm-modal', ['formTrigger' => '#confirmDelete'])
    @include('LaravelLogger::scripts.confirm-modal', ['formTrigger' => '#confirmRestore'])

    @if(config('LaravelLogger.loggerDatatables'))
        @if (count($activities) > 10)
            @include('LaravelLogger::scripts.datatables')
        @endif
    @endif

    @if(config('LaravelLogger.enableDrillDown'))
        @include('LaravelLogger::scripts.clickable-row')
        @include('LaravelLogger::scripts.tooltip')
    @endif

@if(config('LaravelLogger.enableBladeJsPlacement'))
    @endsection
@endif
