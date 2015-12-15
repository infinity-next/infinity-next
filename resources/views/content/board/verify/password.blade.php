{!! Form::open([
	'url'    => Request::url(),
	'method' => "POST",
	'id'     => "post-verify-pass",
	'class'  => "form-verify",
]) !!}
	<fieldset class="form-fields">
		<legend class="form-legend">@lang('board.legend.verify_pass')</legend>
		
		<div class="field row-username">
			<label class="field-label" for="password">@lang('board.field.password')</label>
			<input class="field-control post-password" id="password" name="password" type="password" />
		</div>
		
		<div class="field row-submit">
			{!! Form::button(
				trans('board.submit.verify_password'),
				[
					'type'  => "submit",
					'class' => "field-submit",
					'name'  => "scope",
					'value' => "self",
			]) !!}
		</div>
	</fieldset>
{!! Form::close() !!}
