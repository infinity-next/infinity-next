@extends('layouts.main.panel')

@section('title', trans("panel.title.board_role_edit", [
	'board_uri' => $board->board_uri,
	'role'      => $role->getDisplayName(),
]))

@section('actions')
	<a class="panel-action" href="{{ $role->getPermissionsURLForBoard() }}">@lang('panel.list.field.permissions')</a>
@endsection

@section('body')
{!! Form::open([
	'url'    => Request::url(),
	'method' => "PATCH",
	'files'  => true,
	'id'     => "config-role",
	'class'  => "form-config",
]) !!}
	
	@include('content.panel.board.roles.form', [
		'role' => $role,
	])
	
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
