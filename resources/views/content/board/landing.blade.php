@extends('layouts.main.board')

@section('meta')
<meta http-equiv="refresh" content="1;URL='{{ $url }}'" />
@parent
@stop

@section('content')
<main class="board-landing" id="landing">
    <p class="landing-message"><a href="{{ $url }}">{{ $message }}</a></p>
</main>
@stop
