@extends('layouts.main')

@section('title', e("{$board->title} - /{$board->board_uri}/"))
@section('description', e($board->description))

@section('css')
	@parent
	
	<link href="/{{ $board->board_uri }}/style.css" rel="stylesheet" data-instant-track />
	
@stop

@section('header-logo', $board->getBannerURL())