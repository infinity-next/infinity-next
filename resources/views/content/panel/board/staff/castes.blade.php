<fieldset class="form-fields" id="fields-staff-castes">
	<legend class="form-legend">@lang('config.legend.staff_castes')</legend>
	
	@foreach ($roles as $role)
	<div class="field row-castes">
		<input class="field-control" name="castes[{{ $role->caste }}]" value="1" type="checkbox" id="caste-{{ $role->caste ?: 'default' }}" />
		<label class="field-label" for="caste-{{ $role->caste ?: 'default' }}">{{ $role->getDisplayName() }}</label>
	</div>
	@endforeach
	
</fieldset>