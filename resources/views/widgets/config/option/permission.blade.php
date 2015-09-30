<dl class="option option-permission">
	<dt class="option-term">@lang("config.permission.{$permission->permission_id}")</dt>
	<dd class="option-definition">
		<label class="option-permission option-permission-allow" for="{{ $permission->permission_id }}-allow">
			{!! Form::radio(
				"permission[{$permission->permission_id}]",
				"allow",
				$value === true,
				[
					'id'    => "{$permission->permission_id}-allow",
					'class' => "field-control",
			]) !!}
		</label>
		
		<label class="option-permission option-permission-unset" for="{!! $permission->permission_id !!}-unset">
			{!! Form::radio(
				"permission[{$permission->permission_id}]",
				"unset",
				is_null($value),
				[
					'id'        => "{$permission->permission_id}-no",
					'class'     => "field-control",
			]) !!}
		</label>
		<label class="option-permission option-permission-revoke" for="{{ $permission->permission_id }}-revoke">
			{!! Form::radio(
				"permission[{$permission->permission_id}]",
				"revoke",
				$value === false,
				[
					'id'    => "{$permission->permission_id}-revoke",
					'class' => "field-control",
			]) !!}
		</label>
		
		{{-- <label class="option-permission option-permission-deny" for="{{ $permission->permission_id }}-deny">
			{!! Form::radio(
				"permission[{$permission->permission_id}]",
				"deny",
				$value === false,
				[
					'id'    => "{$permission->permission_id}-deny",
					'class' => "field-control",
			]) !!}
		</label> --}}
	</dd>
</dl>
