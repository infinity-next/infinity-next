@extends('layouts.main.panel')

@section('body')
{!! Form::open([
	'url'    => Request::url(),
	'method' => "PATCH",
	'files'  => true,
	'id'     => "config-permissions",
	'class'  => "form-config",
]) !!}
	<h3 class="config-title">@lang("config.title.permissions")</h3>
	
	{{-- @foreach ($permissions as $permission) --}}
	@include('widgets.config.permissions',[
		'permissions' => $permissions,
	])
	{{-- @endforeach --}}
	
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