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
	
	@include('content.panel.board.roles.form')
	
	<div class="field row-submit">
		<button type="submit" class="field-submit">@lang('panel.action.add_role')</button>
	</div>
{!! Form::close() !!}
@endsection
