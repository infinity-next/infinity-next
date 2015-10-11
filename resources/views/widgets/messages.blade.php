<ul class="form-messages" data-widget="notice">
	@if(Session::has('success'))
		@set('success', Session::get('success'))
	@endif
	
	@if(Session::has('status'))
		@set('status', Session::get('status'))
	@endif
		
	@if(isset($messages))
	@foreach($messages as $message)
	<li class="form-message message-info">{!! $message !!}</li>
	@endforeach
	@endif
	
	@if(isset($status))
	<li class="form-message message-success">{!! $status !!}</li>
	@endif
	
	@if(isset($success))
	<li class="form-message message-success">{!! $success !!}</li>
	@endif
	
	@if(isset($errors))
	@foreach($errors->all() as $error)
	<li class="form-message message-error">{!! $error !!}</li>
	@endforeach
	@endif
</ul>