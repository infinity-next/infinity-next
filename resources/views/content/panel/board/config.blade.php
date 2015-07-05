@extends('layouts.main.panel')

@section('body')
{!! Form::open([
	'url'    => Request::url(),
	'method' => "PATCH",
	'files'  => true,
	'id'     => "config-site",
	'class'  => "form-config",
]) !!}
	<h3 class="config-title">@lang("config.title.board", [
		"board_uri" => $board->board_uri,
	])</h3>
	
	{{-- Basic Details --}}
	<fieldset class="form-fields group-board_basic">
		<legend class="form-legend">{{ trans("config.legend.board_basic") }}</legend>
		
		@include("widgets.config.option.text" . ($user->canEditBoardUri($board) ? "" : "_plain"), [
			'option_name'    => "boardBasicUri",
			'option_value'   => $board->board_uri,
		])
		
		@include("widgets.config.option.text", [
			'option_name'  => "boardBasicTitle",
			'option_value' => $board->title,
		])
		
		@include("widgets.config.option.text", [
			'option_name'  => "boardBasicDesc",
			'option_value' => $board->description,
		])
		
		@include("widgets.config.option.onoff", [
			'option_name'  => "boardBasicOverboard",
			'option_value' => $board->is_overboard,
		])
		
		@include("widgets.config.option.onoff", [
			'option_name'  => "boardBasicIndexed",
			'option_value' => $board->is_indexed,
		])
		
		@include("widgets.config.option.onoff", [
			'option_name'  => "boardBasicWorksafe",
			'option_value' => $board->is_worksafe,
		])
		
	</fieldset>
	
	{{-- Config Options --}}
	@foreach ($groups as $group)
		@include('widgets.config.group')
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