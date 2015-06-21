<dl class="option option-{{{ $option->option_name }}}">
	<dt class="option-term">
		{!! Form::label(
			$option->option_name,
			trans("config.option.{$option->option_name}"),
			[
				'class' => "field-label",
		]) !!}
	</dt>
	<dd class="option-definition">
		{!! Form::number(
			$option->option_name,
			$value,
			[
				'id'        => $option->option_name,
				'class'     => "field-control",
		]) !!}
	</dd>
</dl>
