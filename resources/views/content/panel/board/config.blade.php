@extends('layouts.main.panel')

@section('title', trans("panel.title.board", [
	"board_uri" => $board->board_uri,
]))

@section('body')
	@include($c->template("panel.board.config.{$tab}"))
@endsection