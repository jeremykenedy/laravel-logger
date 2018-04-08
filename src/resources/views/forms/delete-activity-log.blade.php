{!! Form::open(array('route' => 'destroy-activity', 'class' => 'mb-0')) !!}
    {!! Form::hidden('_method', 'DELETE') !!}
    {!! Form::button('<i class="fa fa-fw fa-eraser" aria-hidden="true"></i>' . trans('LaravelLogger::laravel-logger.dashboardCleared.menu.deleteAll'), array('type' => 'button', 'class' => 'text-danger dropdown-item', 'data-toggle' => 'modal', 'data-target' => '#confirmDelete', 'data-title' => trans('LaravelLogger::laravel-logger.modals.deleteLog.title'),'data-message' => trans('LaravelLogger::laravel-logger.modals.deleteLog.message'))) !!}
{!! Form::close() !!}
