@extends('content.multiboard', [
    'posts'   => $posts,
    'threads' => null,
])

@section('title', trans("board.history.title", [
    'ip' => $ip,
]))

@section('header-details')
    <div id="sudo-lecture">@lang('board.history.lecture')</div>
@stop
