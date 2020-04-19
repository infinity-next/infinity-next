@extends('layouts.main.panel')

@section('title', trans("panel.title.board", [
    "board_uri" => $board->board_uri,
]))

@section('actions')
    @can('feature', $board)
    <a class="panel-action" href="{{ route('panel.board.feature', [
        'board' => $board,
    ]) }}">+ @lang('panel.action.feature')</a>
    @endcan
    @can('delete', $board)
    <a class="panel-action delete" href="{{ route('panel.board.delete', [
        'board' => $board,
    ]) }}">@lang('panel.action.delete')</a>
    @endcan
@endsection

@section('body')
    @include("content.panel.board.config.{$tab}")
@endsection
