@extends('layouts.main')

@section('title', e("{$board->title} - /{$board->board_uri}/"))
@section('description', e($board->description))

@section('page-css')
	<link id="page-stylesheet" href="/{{ $board->board_uri }}/style.css" rel="stylesheet" data-instant-track />
@stop

@section('app-js')
	'board'          : "{{ $board->board_uri }}",
	'board_url'      : "{{ $board->getUrl() }}",
@stop

@section('header-logo', $board->getBannerURL())