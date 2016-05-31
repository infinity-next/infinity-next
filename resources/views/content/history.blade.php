@extends('content.multiboard', [
    'posts'   => $posts,
    'threads' => null,
])

@section('title', trans("board.history.title", [
    'ip' => $ip,
]))

@section('description')
    <div id="sudo-lecture">@lang('board.history.lecture')</div>
@stop
