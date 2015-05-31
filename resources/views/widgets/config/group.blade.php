<fieldset class="form-fields group-{{{ $group->group_name }}}">
	<legend class="form-legend">{{ trans("config.legend.{$group->group_name}") }}</legend>
	
	@foreach ($group->options as $option)
		@include( $option->getTemplate($controller), [
			'option' => $option,
			'group' => $group
		])
	@endforeach
</fieldset>