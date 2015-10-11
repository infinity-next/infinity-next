@extends('layouts.main.panel')

@section('body')
{!! Form::open([
	'url'    => Request::url(),
	'method' => "PATCH",
	'files'  => true,
	'id'     => "config-permissions",
	'class'  => "form-config",
]) !!}
	<h3 class="config-title">@lang("panel.title.permissions", [ 'role' => $role->getDisplayName() ])</h3>
	
	@include('content.panel.roles.permissions.form')
	
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