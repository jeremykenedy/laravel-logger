@extends('layouts.app')

@section('template_title')
    @lang('LaravelLogger::laravel-logger.dashboard.title')
@endsection

@section('template_linked_css')
    @if(config('LaravelLogger.loggerDatatables'))
        <link rel="stylesheet" type="text/css" href="{{config('LaravelLogger.loggerDatatablesCSScdn')}}">
    @endif
    <style type="text/css" media="screen">
        .clickable-row:hover {
          cursor: pointer;
        }
        .table-responsive {
            border: none;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div style="display: flesx; justify-content: spacse-between; align-items: censter;">
                            @lang('LaravelLogger::laravel-logger.dashboard.title')
                            @if(config('LaravelLogger.enableSubMenu'))
                                <small>
                                    <sup class="label label-default">
                                        {{count($activities)}}
                                        <span class="hidden-sms">
                                            @lang('LaravelLogger::laravel-logger.dashboard.subtitle')
                                        </span>
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
                                            <a href="#">
                                                <i class="fa fa-fw fa-eraser" aria-hidden="true"></i>
                                                @lang('LaravelLogger::laravel-logger.dashboard.menu.clear')
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <i class="fa fa-fw fa-history" aria-hidden="true"></i>
                                                @lang('LaravelLogger::laravel-logger.dashboard.menu.show')
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            @else
                                <span class="pull-right label label-default">
                                    {{count($activities)}}
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

    {{-- @include('modals.modal-delete') --}}

@endsection

@section('footer_scripts')

    {{-- @include('scripts.delete-modal-script')--}}

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