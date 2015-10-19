<fieldset class="form-fields group-role_basic">
	<legend class="form-legend">{{ trans("config.legend.role_basic") }}</legend>
	
	@if (is_null($role))
	@include("widgets.config.option.radio", [
		'option_name'    => "roleType",
		'option_value'   => "",
		'option_choices' => $choices,
	])
	@else
	@include("widgets.config.option.text_plain", [
		'option_name'    => "roleType",
		'option_value'   => $role->getDisplayName(),
	])
	@endif
	
	@include("widgets.config.option.text", [
		'option_name'    => "roleCaste",
		'option_value'   => is_null($role) ? "" : $role->caste,
	])
	
	@include("widgets.config.option.text", [
		'option_name'    => "roleName",
		'option_value'   => is_null($role) ? "" : $role->name,
	])
	
	@include("widgets.config.option.text", [
		'option_name'    => "roleCapcode",
		'option_value'   => is_null($role) ? "" : $role->capcode,
	])
</fieldset>