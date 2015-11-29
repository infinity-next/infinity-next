<dl class="option option-{{{ $option_name }}}">
	<dt class="option-term">
		{!! Form::label(
			$option_name,
			trans("config.option.{$option_name}"),
			[
				'class' => "field-label",
		]) !!}
		@include('widgets.config.lock')
	</dt>
	<dd class="option-definition">
		<ul class="option-list">
			@for ($option_list_i = 0; $option_list_i < count($option_value) + 1; ++$option_list_i)
				@if (isset($option_value[$option_list_i]))
				<li class="option-item">
					{!! Form::text(
						$option_name . "[]",
						$option_value[$option_list_i],
						[
							'class'     => "field-control",
							isset($board) && isset($option) && !$user->canEditSetting($board, $option) ? 'disabled' : 'data-enabled',
					]) !!}
				</li>
				@else if($option->isLocked())
				<li class="option-item option-item-template">
					{!! Form::text(
						$option_name . "[]",
						null,
						[
							'class'     => "field-control",
					]) !!}
				</li>
				@endif
			@endfor
		</ul>
		
		@include('widgets.config.helper')
	</dd>
</dl>
