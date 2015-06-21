@if (count($permissions) > 0)
<fieldset class="form-fields">
	<legend class="form-legend">{{ trans("permissions.legend.bleh") }}</legend>
	
	@foreach ($permissions as $permission)
		@if ($user->can($permission->permission_id))
		@include('widgets.config.option.permission', [
			'option' => $permission,
			'value'  => $role->getPermission($permission),
		])
		@endif
	@endforeach
</fieldset>
@endif