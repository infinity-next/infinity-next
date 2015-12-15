{!! Form::open([
	'url'    => Request::url(),
	'method' => "POST",
	'id'     => "post-verify-mod",
	'class'  => "form-verify",
]) !!}
	<input type="hidden" name="confirm" value="1" />
	<fieldset class="form-fields">
		<legend class="form-legend">@lang('board.legend.verify_mod')</legend>
		
		<p class="form-desc">@lang('board.verify.mod')</p>
		
		<div class="field row-submit">
			{!! Form::button(
				trans('board.submit.verify_mod'),
				[
					'type'  => "submit",
					'class' => "field-submit",
					'name'  => "scope",
					'value' => "other",
			]) !!}
		</div>
	</fieldset>
{!! Form::close() !!}
