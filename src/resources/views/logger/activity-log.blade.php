@extends('layouts.app')

@section('template_title')
    @lang('LaravelLogger::laravel-logger.dashboard.title')
@endsection

@section('template_linked_css')
    @include('LaravelLogger::partials.styles')
@endsection

@section('content')

    <div class="container-fluid">

        @if(config('LaravelLogger.enablePackageFlashMessageBlade'))
            @include('LaravelLogger::partials.form-status')
        @endif

        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div style="display: flesx; justify-content: spacse-between; align-items: censter;">
                            @lang('LaravelLogger::laravel-logger.dashboard.title')
                            @if(config('LaravelLogger.enableSubMenu'))
                                <small>
                                    <sup class="label label-default">
                                        {{ $totalActivities }} @lang('LaravelLogger::laravel-logger.dashboard.subtitle')
                                    </sup>
                                </small>
                                <div class="btn-group pull-right btn-group-xs">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-ellipsis-v fa-fw" aria-hidden="true"></i>
                                        <span class="sr-only">
                                            @lang('LaravelLogger::laravel-logger.dashboard.menu.alt')
                                        </span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            @include('LaravelLogger::forms.clear-activity-log')
                                        </li>
                                        <li>
                                            <a href="{{route('cleared')}}">
                                                <i class="fa fa-fw fa-history" aria-hidden="true"></i>
                                                @lang('LaravelLogger::laravel-logger.dashboard.menu.show')
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            @else
                                <span class="pull-right label label-default">
                                    {{ $totalActivities }}
                                    <span class="hidden-sms">
                                        @lang('LaravelLogger::laravel-logger.dashboard.subtitle')
                                    </span>
                                </span>
                            @endif

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

@endsection

@section('footer_scripts')

    @include('LaravelLogger::scripts.confirm-modal', ['formTrigger' => '#confirmDelete'])

    @if(config('LaravelLogger.loggerDatatables'))
        @if (count($activities) > 10)
            @include('LaravelLogger::scripts.datatables')
        @endif
    @endif

    @if(config('LaravelLogger.enableDrillDown'))
        @include('LaravelLogger::scripts.clickable-row')
        @include('LaravelLogger::scripts.tooltip')
    @endif

@endsection
