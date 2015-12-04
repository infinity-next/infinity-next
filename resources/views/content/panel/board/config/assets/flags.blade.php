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
					trans("config.option.boardAssetFlagUpload"),
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
						@else
						<li class="option-item">
							{!! Form::text(
								"board_flags[name][]",
								"",
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