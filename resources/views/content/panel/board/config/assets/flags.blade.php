<!-- Board Flags  -->
{!! Form::open([
	'url'    => Request::url(),
	'method' => "PUT",
	'files'  => true,
	'id'     => "flags-upload",
	'class'  => "form-config",
]) !!}
	<fieldset class="form-fields group-board_flags">
		<legend class="form-legend">{{ trans("config.legend.board_flags") }}</legend>
		
		<dl class="option option-board_flags">
			<dt class="option-term">
				{!! Form::label(
					null,
					trans("config.option.{boardAssetFlagUpload"),
					[
						'class' => "field-label",
				]) !!}
			</dt>
			<dd class="option-definition">
				<ul class="option-list">
					@for ($option_list_i = 0; $option_list_i < count([]) + 1; ++$option_list_i)
						@if (isset($option_value[$option_list_i]))
						<li class="option-item">
							{!! Form::text(
								"board_flags[name][]",
								$option_value[$option_list_i],
								[
									'class'     => "field-control",
									'disabled',
							]) !!}
						</li>
						@endif
					@endfor
				</ul>
			</dd>
		</dl>
	</fieldset>
	
	
	<fieldset class="form-fields group-new_board_flag">
		<legend class="form-legend">{{ trans("config.legend.board_flag") }}</legend>
		
		<dl class="option option-new_board_flag">
			<dt class="option-term">
				{!! Form::label(
					"new_board_flag",
					trans("config.option.boardAssetbannedUpload"),
					[
						'class' => "field-label",
				]) !!}
			</dt>
			<dd class="option-definition">
				<input class="field-control" id="new_board_flag" name="new_board_flag" type="file" />
			</dd>
		</dl>
	</fieldset>
	
	{!! Form::hidden('asset_type', 'board_flag') !!}
	
	<div class="field row-submit">
		{!! Form::button(
			trans("config.upload"),
			[
				'type'      => "submit",
				'class'     => "field-submit",
		]) !!}
	</div>
{!! Form::close() !!}