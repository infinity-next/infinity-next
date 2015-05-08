<form class="form-post" method="POST" action="{{{ url($board->uri . '/thread/' . ($reply_to ?: "")) }}}">
	<input type="hidden" name="_token" value="{{ csrf_token() }}" />
	
	<fieldset class="form-fields">
		<legend class="form-legend">{{{ $reply_to ? "Reply" : "Create Thread" }}}</legend>
		
		<div class="field row-subject label-inline">
			<input class="field-control" id="subject" name="subject" type="text" maxlength="255" value="{{ old('subject') }}" />
			<label class="field-label" for="subject">Subject</label>
		</div>
		
		<div class="field row-author label-inline">
			<input class="field-control" id="author" name="author" type="text" maxlength="255" value="{{ old('author') }}" />
			<label class="field-label" for="author">Name</label>
		</div>
		
		<div class="field row-email label-inline">
			<input class="field-control" id="email" name="email" type="text" maxlength="255" value="{{ old('email') }}" />
			<label class="field-label" for="email">Email</label>
		</div>
		
		<div class="field row-post">
			<textarea class="field-control" id="body" name="body" type="text">{{ old('body') }}</textarea>
		</div>
		
		<div class="field row-captcha">
			<label class="field-label" for="captcha">
				{!! Captcha::img() !!}
				<span class="field-validation"></span>
			</label>
			<input class="field-control" id="captcha" name="captcha" type="text" />
		</div>
		
		<div class="field row-submit">
			<button type="submit" class="field-submit">{{{ $reply_to ? "Post Reply" : "Create Thread" }}}</button>
		</div>
	</fieldset>
</form>