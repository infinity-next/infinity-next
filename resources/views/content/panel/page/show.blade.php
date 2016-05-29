@extends('layouts.main.panel')

@section('title', trans("panel.title.page"))

@section('actions')
    <a class="panel-action" href="{{ route( $board->exists
        ? 'panel.board.page.edit'
        : 'panel.site.page.edit', [
            'page'  => $page,
            'board' => $board,
        ]
    ) }}">@lang('panel.action.update_page')</a>
@endsection

@section('body')
    {{ $page }}
@endsection
