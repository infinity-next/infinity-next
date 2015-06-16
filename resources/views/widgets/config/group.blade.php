@if (count($group->options) > 0)
<fieldset class="form-fields group-{{{ $group->group_name }}}">
	<legend class="form-legend">{{ trans("config.legend.{$group->group_name}") }}</legend>
	
	@foreach ($group->options as $option)
		@include( $option->getTemplate($controller), [
			'option' => $option,
			'value'  => $option->option_value ?: $option->default_value,
			'group'  => $group,
		])
	@endforeach
</fieldset>
@endif