@extends('layouts.main')

@section('title', e("{$board->title} - /{$board->board_uri}/"))
@section('page-title', e("/{$board->board_uri}/ - {$board->title}") . View::make('widgets.boardfav', [ 'board' => $board ]))
@section('description', e($board->description))
@section('body-class', "view-board board-{$board->board_uri}")

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
	'board_settings' : {
		'postAttachmentsMax' : "{{ $board->getConfig('postAttachmentsMax') }}",
		'postAttachmentsMin' : "{{ $board->getConfig('postAttachmentsMin') }}",
		'postMaxLength'      : "{{ $board->getConfig('postMaxLength') }}",
		'postMinLength'      : "{{ $board->getConfig('postMinLength') }}"
	},
@stop

@section('content-addendum')
	<div id="attachment-preview">
		<img id="attachment-preview-img" src="" />
	</div>
@stop

@section('header-logo', $board->getBannerURL())
