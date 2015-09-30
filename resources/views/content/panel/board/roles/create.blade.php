@extends('layouts.main.panel')

@section('title', trans("panel.title.board_role_add", [
	'board_uri' => $board->board_uri,
]))

@section('body')
{!! Form::open([
	'url'    => Request::url(),
	'method' => "PUT",
	'files'  => true,
	'id'     => "config-role",
	'class'  => "form-config",
]) !!}
	
	<fieldset class="form-fields group-role_basic">
		<legend class="form-legend">{{ trans("config.legend.role_basic") }}</legend>
		
		@include("widgets.config.option.radio", [
			'option_name'    => "roleType",
			'option_value'   => "",
			'option_choices' => $choices,
		])
		
		@include("widgets.config.option.text", [
			'option_name'    => "roleCaste",
			'option_value'   => "",
		])
		
		@include("widgets.config.option.text", [
			'option_name'    => "roleName",
			'option_value'   => "",
		])
		
		@include("widgets.config.option.text", [
			'option_name'    => "roleCapcode",
			'option_value'   => "",
		])
	</fieldset>
	
	<div class="field row-submit">
		<button type="submit" class="field-submit">@lang('panel.action.add_role')</button>
	</div>
{!! Form::close() !!}
@endsection
