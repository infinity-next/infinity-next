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
			@set('option_value', $board->getWordfilters())

			@foreach ($option_value as $option_find => $option_replace)
			<li class="option-item">
				{!! Form::text(
					$option_name . "[find][]",
					$option_find,
					[
						'class'     => "field-control",
						isset($board) && isset($option) && !$user->canEditSetting($board, $option) ? 'disabled' : 'data-enabled',
				]) !!}
				{!! Form::text(
					$option_name . "[replace][]",
					$option_replace,
					[
						'class'     => "field-control",
						isset($board) && isset($option) && !$user->canEditSetting($board, $option) ? 'disabled' : 'data-enabled',
				]) !!}
			</li>
			@endforeach

			@if (!$option->isLocked())
			<li class="option-item option-item-template">
				{!! @Form::text(
					$option_name . "[find][]",
					"",
					[
						'class'     => "field-control",
				]) !!}
				{!! @Form::text(
					$option_name . "[replace][]",
					"",
					[
						'class'     => "field-control",
				]) !!}
			</li>
			@endif
		</ul>

		@include('widgets.config.helper')
	</dd>
</dl>
