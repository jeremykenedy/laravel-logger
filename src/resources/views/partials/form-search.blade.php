<form action="{{route('activity')}}" method="get">
	<div class="row">
		@if(config('LaravelLogger.descriptionSearch'))
		<div class="col">
			<input type="text" name="description" value="{{request()->get('description') ? request()->get('description'):null}}" class="form-control" placeholder="Description">
		</div>
		@endif
		@if(config('LaravelLogger.userSearch'))
		<div class="col">
			<select class="form-control" name="user">
				<option value="" selected>All</option>
				@foreach($users as $user)
				<option value="{{$user->id}}" {{request()->get('user') && request()->get('user') == $user->id ? 'selected':''}}>{{$user->name}}</option>
				@endforeach
			</select>
		</div>
		@endif
		@if(config('LaravelLogger.methodSearch'))
		<div class="col">
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
		@if(config('LaravelLogger.routeSearch'))
		<div class="col">
			<input type="text" name="route" class="form-control" value="{{request()->get('route') ? request()->get('route'):null}}" placeholder="Route">
		</div>
		@endif
		@if(config('LaravelLogger.ipAddressSearch'))
		<div class="col">
			<input type="text" name="ip_address" class="form-control" value="{{request()->get('ip_address') ? request()->get('ip_address'):null}}" placeholder="Ip Address">
		</div>
		@endif
	</div>
	<br>
	@if(config('LaravelLogger.descriptionSearch') ||config('LaravelLogger.userSearch') ||config('LaravelLogger.methodSearch') || config('LaravelLogger.routeSearch') || config('LaravelLogger.ipAddressSearch'))
	<div class="row">
		<input type="submit" class="btn btn-primary" style="margin-left: 800px;" value="Search">
	</div>
	@endif
</form>
<br>
