{!! Form::open(array('route' => 'restore-activity', 'method' => 'POST')) !!}
    {!! Form::button('<i class="fa fa-fw fa-history" aria-hidden="true"></i>' . trans('LaravelLogger::laravel-logger.dashboardCleared.menu.restoreAll'), array('type' => 'button', 'class' => 'text-success', 'data-toggle' => 'modal', 'data-target' => '#confirmRestore', 'data-title' => trans('LaravelLogger::laravel-logger.modals.restoreLog.title'),'data-message' => trans('LaravelLogger::laravel-logger.modals.restoreLog.message'))) !!}
{!! Form::close() !!}
