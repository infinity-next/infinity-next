
<dl class="option option-{{{ $option->option_name }}}">
	<dt class="option-term"></dt>
	<dd class="option-definition">
		{!! Form::checkbox(
			$option->option_name,
			is_null($option->option_value) ? $option->default_value : $option->option_value,
			[
				'id'        => $option->option_name,
				'class'     => "field-control",
		]) !!}
		{!! Form::label(
			$option->option_name,
			trans("config.option.{$option->option_name}"),
			[
				'class' => "field-label",
		]) !!}
	</dd>
</dl>
