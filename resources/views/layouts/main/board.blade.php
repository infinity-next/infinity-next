@extends('layouts.main')

@section('title', e("{$board->title} - /{$board->board_uri}/"))
@section('description', e($board->description))

@section('css')
	@parent
	
	@if ($board->getStylesheet())
	<link href="/{{ $board->board_uri }}/style.css" rel="stylesheet" />
	@elseif (!$board->is_worksafe)
	<link href="/css/app/skins/yotsuba.css" rel="stylesheet" />
	@endif
@stop

@section('header-logo', $board->getBannerURL())