<dl class="option option-{{{ $option_name }}}">
	<dt class="option-term"></dt>
	<dd class="option-definition">
		{!! Form::checkbox(
			$option_name,
			1,
			!!$option_value,
			[
				'id'        => $option_name,
				'class'     => "field-control",
		]) !!}
		{!! Form::label(
			$option_name,
			trans("config.option.{$option_name}"),
			[
				'class' => "field-label",
		]) !!}
	</dd>
</dl>
