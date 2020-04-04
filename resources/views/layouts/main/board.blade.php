@extends('layouts.main')

@section('title', e("{$board->title} - /{$board->board_uri}/"))
@section('page-title')/{{ $board->board_uri }}/ - {{ $board->title }} {!! View::make('widgets.boardfav', [ 'board' => $board ]) !!}@stop
@section('description', $board->description)
@section('body-class', "view-board board-{$board->board_uri}")

@section('page-css')
    @if ($board->hasStylesheet())
    <link id="page-stylesheet" rel="stylesheet" href="{{ $board->getStylesheetUrl('style.css') }}" data-instant-track />
    @else
    <link id="page-stylesheet" rel="stylesheet" href="{{ asset('static/css/empty.css') }}" data-instant-track />
    @endif
@stop

@section('app-js')
    'board'          : "{{ $board->board_uri }}",
    'board_url'      : "{{ trim($board->getUrl('index'), '/') }}",
    'board_settings' : {
        'postAttachmentsMax' : "{{ $board->getConfig('postAttachmentsMax') }}",
        'postAttachmentsMin' : "{{ $board->getConfig('postAttachmentsMin') }}",
        'postMaxLength'      : "{{ $board->getConfig('postMaxLength') }}",
        'postMinLength'      : "{{ $board->getConfig('postMinLength') }}"
    },
@stop

@section('header-logo', $board->getBannerURL())
