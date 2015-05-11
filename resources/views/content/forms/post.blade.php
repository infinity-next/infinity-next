<form class="form-post" method="POST" action="{{ url($board->uri . '/thread/' . ($reply_to ?: "")) }}" enctype="multipart/form-data">
	<input type="hidden" name="_token" value="{{ csrf_token() }}" />
	<input type="hidden" name="_method" value="PUT" />
	
	@include('widgets.messages')
	
	<fieldset class="form-fields">
		<legend class="form-legend">{{ $reply_to ? trans('board.form_reply') : trans('board.form_thread') }}</legend>
		
		<div class="field row-subject label-inline">
			<input class="field-control" id="subject" name="subject" type="text" maxlength="255" value="{{ old('subject') }}" />
			<label class="field-label" for="subject">@lang('board.field_subject')</label>
		</div>
		
		<div class="field row-author label-inline">
			<input class="field-control" id="author" name="author" type="text" maxlength="255" value="{{ old('author') }}" />
			<label class="field-label" for="author">@lang('board.field_author')</label>
		</div>
		
		<div class="field row-email label-inline">
			<input class="field-control" id="email" name="email" type="text" maxlength="255" value="{{ old('email') }}" />
			<label class="field-label" for="email">@lang('board.field_email')</label>
		</div>
		
		<div class="field row-post">
			<textarea class="field-control" id="body" name="body">{{ old('body') }}</textarea>
		</div>
		
		@if ($board->canAttach($user))
		<div class="field row-file">
			<input class="field-control" id="file" name="file" type="file" />
		</div>
		@endif
		
		<div class="field row-captcha">
			<label class="field-label" for="captcha">
				{!! Captcha::img() !!}
				<span class="field-validation"></span>
			</label>
			<input class="field-control" id="captcha" name="captcha" type="text" />
		</div>
		
		<div class="field row-submit">
			<button type="submit" class="field-submit">{{ $reply_to ? trans('board.action_reply') : trans('board.action_thread') }}</button>
		</div>
	</fieldset>
</form>