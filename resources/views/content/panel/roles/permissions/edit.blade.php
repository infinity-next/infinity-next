@extends('layouts.main.panel')

@section('body')
{!! Form::open([
	'url'    => Request::url(),
	'method' => "PATCH",
	'files'  => true,
	'id'     => "config-permissions",
	'class'  => "form-config",
]) !!}
	<h3 class="config-title">@lang("panel.title.permissions", [ 'role' => $role->name ])</h3>
	
	<dl class="option option-permission">
		<dt class="option-term">@lang('config.permission.master.help.quickcheck')</dt>
		<dd class="option-definition">
			<label class="option-permission option-permission-unset option-master" id="permission-master-inherit" title="@lang('config.permission.master.help.inherit')">
				@lang('config.permission.master.inherit')
			</label>
			<label class="option-permission option-permission-allow option-master" id="permission-master-allow" title="@lang('config.permission.master.help.allow')">
				@lang('config.permission.master.allow')
			</label>
			<label class="option-permission option-permission-deny option-master" id="permission-master-deny" title="@lang('config.permission.master.help.deny')">
				@lang('config.permission.master.deny')
			</label>
		</dd>
	</dl>
	
	@foreach ($groups as $group)
		@include('widgets.config.permissions',[
			'permissions' => $group->permissions,
		])
	@endforeach
	
	<div class="field row-submit">
		{!! Form::button(
			trans("config.submit"),
			[
				'type'      => "submit",
				'class'     => "field-submit",
		]) !!}
	</div>
{!! Form::close() !!}
@endsection