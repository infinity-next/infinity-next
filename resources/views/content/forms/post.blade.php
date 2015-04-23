@if ($reply_to)
<form class="form-post" method="post" action="/{!! $board->uri !!}/post/{!! $reply_to !!}">
@else
<form class="form-post" method="post" action="/{!! $board->uri !!}/post">
@endif
	<input type="hidden" name="_token" value="{{ csrf_token() }}" />
	
	<fieldset class="post-fields">
		<legend class="post-action action-newthread">{{{ $reply_to ? "Reply" : "Create a Thread" }}} <button>ya ok submit the post</button></legend>
		
		<div class="post-field">
			<label class="post-label field-subject">Subject</label>
			<input class="post-input field-subject" name="subject" type="text" maxlength="255" />
		</div>
		
		<div class="post-field">
			<label class="post-label field-author">Name</label>
			<input class="post-input field-author" name="author" type="text" maxlength="255" />
		</div>
		
		<div class="post-field">
			<label class="post-label field-email">Email</label>
			<input class="post-input field-email" name="email" type="text" maxlength="255" />
		</div>
		
		<div class="post-field">
			<label class="post-label field-post">Post</label>
			<textarea class="post-input field-post" name="body" type="text"></textarea>
		</div>
	</fieldset>
</form>