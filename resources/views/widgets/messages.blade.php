<ul class="form-messages" data-widget="notice">
	@if(isset($messages))
	@foreach($messages as $message)
	<li class="form-message message-info">{!! $message !!}</li>
	@endforeach
	@endif
	
	@if(isset($status))
	<li class="form-message message-success">{!! $status !!}</li>
	@endif
	
	@if(isset($errors))
	@foreach($errors->all() as $error)
	<li class="form-message message-error">{!! $error !!}</li>
	@endforeach
	@endif
</ul>