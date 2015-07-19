@extends('layouts.main.panel')

@section('title', trans("panel.title.board", [
	"board_uri" => $board->board_uri,
]))

@section('body')
{!! Form::open([
	'url'    => Request::url(),
	'method' => "PATCH",
	'files'  => true,
	'id'     => "config-site",
	'class'  => "form-config",
]) !!}
	
	@include($c->template("panel.board.config.{$tab}"))
	
{!! Form::close() !!}
@endsection