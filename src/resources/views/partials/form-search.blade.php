<form action="{{route('activity')}}" method="get">
	<div class="row mb-3">
		@if(in_array('description',explode(',', config('LaravelLogger.searchFields'))))
			<div class="col-12 col-sm-4 col-lg-2 mb-2">
				<input type="text" name="description" value="{{request()->get('description') ? request()->get('description'):null}}" class="form-control" placeholder="Description">
			</div>
		@endif
		@if(in_array('user',explode(',', config('LaravelLogger.searchFields'))))
			<div class="col-12 col-sm-4 col-lg-2 mb-2">
				<select class="form-control" name="user">
					<option value="" selected>All</option>
					@foreach($users as $user)
					<option value="{{$user->id}}" {{request()->get('user') && request()->get('user') == $user->id ? 'selected':''}}>{{$user->name}}</option>
					@endforeach
				</select>
			</div>
		@endif
		@if(in_array('method',explode(',', config('LaravelLogger.searchFields'))))
			<div class="col-12 col-sm-4 col-lg-2 mb-2">
				<select class="form-control" name="method">
					<option value="" selected>All</option>
					<option value="GET" {{request()->get('method') && request()->get('method') == 'GET' ? 'selected':''}}>GET</option>
					<option value="POST" {{request()->get('method') && request()->get('method') == 'POST' ? 'selected':''}}>POST</option>
					<option value="PUT" {{request()->get('method') && request()->get('method') == 'PUT' ? 'selected':''}}>PUT</option>
					<option value="DELETE" {{request()->get('method') && request()->get('method') == 'DELETE' ? 'selected':''}}>DELETE</option>
					<option value="CONNECT" {{request()->get('method') && request()->get('method') == 'CONNECT' ? 'selected':''}}>CONNECT</option>
					<option value="OPTIONS" {{request()->get('method') && request()->get('method') == 'OPTIONS' ? 'selected':''}}>OPTIONS</option>
					<option value="TRACE" {{request()->get('method') && request()->get('method') == 'TRACE' ? 'selected':''}}>TRACE</option>
					<option value="PATCH" {{request()->get('method') && request()->get('method') == 'PATCH' ? 'selected':''}}>PATCH</option>
					<option value="CONNECT" {{request()->get('method') && request()->get('method') == 'CONNECT' ? 'selected':''}}>CONNECT</option>
				</select>
			</div>
		@endif
		@if(in_array('route',explode(',', config('LaravelLogger.searchFields'))))
			<div class="col-12 col-sm-4 col-lg-2 mb-2">
				<input type="text" name="route" class="form-control" value="{{request()->get('route') ? request()->get('route'):null}}" placeholder="Route">
			</div>
		@endif
		@if(in_array('ip',explode(',', config('LaravelLogger.searchFields'))))
			<div class="col-12 col-sm-4 col-lg-2 mb-2">
				<input type="text" name="ip_address" class="form-control" value="{{request()->get('ip_address') ? request()->get('ip_address'):null}}" placeholder="Ip Address">
			</div>
		@endif
		@if(in_array('description',explode(',', config('LaravelLogger.searchFields')))||in_array('user',explode(',', config('LaravelLogger.searchFields'))) ||in_array('method',explode(',', config('LaravelLogger.searchFields'))) || in_array('route',explode(',', config('LaravelLogger.searchFields'))) || in_array('ip',explode(',', config('LaravelLogger.searchFields'))))
			<div class="col-12 col-sm-4 col-lg-2 mb-2">
				<input type="submit" class="btn btn-primary btn-block" value="Search">
			</div>
		@endif
	</div>
</form>
