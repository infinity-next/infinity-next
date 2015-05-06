<ul class="form-messages">
	@if(isset($messages))
	@foreach($messages as $message)
	<li class="form-message message-info">{!! $message !!}</li>
	@endforeach
	@endif
</ul>