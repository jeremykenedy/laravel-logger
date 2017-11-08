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
    .list-group {
        margin-bottom: 0;
    }


    .dropdown-menu > li button {
        padding: 3px 20px;
        background: transparent;
        border: none;
        outline: none;
        width: 100%;
        text-align: left;
    }
    .dropdown-menu > li button:hover {
        background: rgba(0,0,0,.04);
    }
</style>