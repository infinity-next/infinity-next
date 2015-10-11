@extends('layouts.main.panel')

@section('title', trans("panel.title.board_role_delete", [
	'board_uri' => $board->board_uri,
]))

@section('body')
{!! Form::open([
	'url'    => Request::url(),
	'method' => "DELETE",
	'files'  => true,
	'id'     => "config-role",
	'class'  => "form-config",
]) !!}
	
	<div class="field row-delete">
		<span class="field-confirm">@lang('config.confirm')</span>
		<button type="submit" class="field-delete">@lang('panel.action.delete_role')</button>
	</div>
	
{!! Form::close() !!}
@endsection
