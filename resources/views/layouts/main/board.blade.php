@extends('layouts.main')

@section('title', e("{$board->title} - /{$board->board_uri}/"))
@section('description', e($board->description))

@section('page-css')
	@if ($board->hasStylesheet())
	<link id="page-stylesheet" href="{{ $board->getStylesheetUrl('style.css') }}" rel="stylesheet" data-instant-track />
	@else
	<link id="page-stylesheet" rel="stylesheet" data-instant-track />
	@endif
@stop

@section('app-js')
	'board'          : "{{ $board->board_uri }}",
	'board_url'      : "{{ $board->getUrl() }}",
@stop

@section('header-logo', $board->getBannerURL())