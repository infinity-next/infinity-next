@if (count($permissions) > 0)
<fieldset class="form-fields">
	<legend class="form-legend">{{ trans("config.legend.permissions.{$group->group_name}") }}</legend>
	
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