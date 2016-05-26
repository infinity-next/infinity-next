@extends('layouts.main.panel')

@section('title', trans("panel.title.board", [
	"board_uri" => $board->board_uri,
]))

@section('actions')
	<a class="panel-action" href="{{ route('panel.board.feature', [
		'board' => $board,
	]) }}">+ @lang('panel.action.feature')</a>
@endsection

@section('body')
	@include($c->template("panel.board.config.{$tab}"))
@endsection
