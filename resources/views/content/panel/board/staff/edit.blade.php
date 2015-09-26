@extends('layouts.main.panel')

@section('title', trans("panel.title.board_staff_edit", [
	'board_uri'  => $board->board_uri,
	'staff_name' => $staff->getDisplayName(),
]))

@section('body')
	{!! Form::open([
		'url'    => Request::url(),
		'method' => "PUT",
		'files'  => true,
		'id'     => "config-staff",
		'class'  => "form-staff",
	]) !!}
		
		@include('content.panel.board.staff.castes')
		
		<div class="field row-submit">
			<button type="submit" class="field-submit">@lang('panel.action.edit_staff')</button>
		</div>
	{!! Form::close() !!}
@endsection

