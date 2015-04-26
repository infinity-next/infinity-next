
<form class="form-post" method="POST" action="{{{ url($board->uri . '/thread/' . ($reply_to ?: "")) }}}">
	<input type="hidden" name="_token" value="{{ csrf_token() }}" />
	
	<fieldset class="form-fields">
		<legend class="form-legend">{{{ $reply_to ? "Reply" : "Create a Thread" }}}</legend>
		
		<div class="field row-subject">
			<label class="field-label" for="subject">Subject</label>
			<input class="field-control" id="subject" name="subject" type="text" maxlength="255" />
		</div>
		
		<div class="field row-author">
			<label class="field-label" for="author">Name</label>
			<input class="field-control" id="author" name="author" type="text" maxlength="255" />
		</div>
		
		<div class="field row-email">
			<label class="field-label" for="email">Email</label>
			<input class="field-control" id="email" name="email" type="text" maxlength="255" />
		</div>
		
		<div class="field row-post">
			<label class="field-label" for="body">Post</label>
			<textarea class="field-control" id="body" name="body" type="text"></textarea>
		</div>
		
		<div class="field row-captcha">
			<label class="field-label" for="captcha">{!! Captcha::img() !!}</label>
			<input class="field-control" id="captcha" name="captcha" type="text" />
		</div>
		
		<div class="field row-submit">
			<button type="submit" class="field-submit">Submit</button>
		</div>
	</fieldset>
</form>