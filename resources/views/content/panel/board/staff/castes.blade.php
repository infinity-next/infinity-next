<fieldset class="form-fields" id="fields-staff-castes">
    <legend class="form-legend">@lang('config.legend.staff_castes')</legend>

    @foreach ($roles as $role)
    <div class="field row-castes">
        <input class="field-control" name="castes[{{ $role->role_id }}]" value="{{ $role->role_id }}" type="checkbox" id="caste-{{ $role->role }}-{{ $role->caste ?: '' }}"
            {{ old("castes.{$role->role_id}") || (isset($staff) && isset($staff->roles) && $staff->roles->contains($role)) ? "checked" : "" }} />
        <label class="field-label" for="caste-{{ $role->role }}-{{ $role->caste ?: '' }}">{{ $role->getDisplayName() }}</label>
    </div>
    @endforeach

</fieldset>
