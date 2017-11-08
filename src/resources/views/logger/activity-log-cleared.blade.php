@extends('layouts.app')

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

@section('content')

    <div class="container-fluid">

        @if(config('LaravelLogger.enablePackageFlashMessageBlade'))
            @include('LaravelLogger::partials.form-status')
        @endif

        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        <div style="display: flesx; justify-content: spacse-between; align-items: censter;">

                            @lang('LaravelLogger::laravel-logger.dashboardCleared.title')

                            <sup class="label">
                                {{ $totalActivities }} @lang('LaravelLogger::laravel-logger.dashboardCleared.subtitle')
                            </sup>

                            <div class="btn-group pull-right btn-group-xs">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v fa-fw" aria-hidden="true"></i>
                                    <span class="sr-only">
                                        @lang('LaravelLogger::laravel-logger.dashboard.menu.alt')
                                    </span>
                                </button>
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