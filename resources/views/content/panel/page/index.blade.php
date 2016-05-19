@extends('layouts.main.panel')

@section('title')
    @if ($board->exists)
        @lang('panel.title.board_pages', [
            'board_uri' => $board->board_uri,
        ])
    @else
        @lang('panel.title.site_pages')
    @endif
@endsection

@section('actions')
    <a class="panel-action" href="{{ route( $board->exists
        ? 'panel.board.page.create'
        : 'panel.page.create', [
            'board' => $board,
        ]
    ) }}">+ @lang('panel.action.create_page')</a>
@endsection

@section('body')
    <div class="filterlist">
        <h4 class="filterlist-heading">@lang('panel.list.head.pages')</h4>

        <ol class="filterlist-list">
            @foreach ($pages as $page)
            <li class="filterlist-item">
                <a class="filterlist-secondary" href="{{ route($board->exists
                    ? 'panel.board.page.delete'
                    : 'panel.page.delete', [
                        'id' => $page->page_id,
                        'board' => $board,
                ]) }}"><i class="fa fa-remove"></i></a>
                <a class="filterlist-secondary" href="{{ route($board->exists
                    ? 'panel.board.page.edit'
                    : 'panel.page.edit', [
                        'id' => $page->page_id,
                        'board' => $board,
                ]) }}">@lang('panel.action.update')</a>
                <a class="filterlist-primary" href="{{ route($board->exists
                    ? 'panel.board.page.show'
                    : 'panel.page.show', [
                        'id' => $page->page_id,
                        'board' => $board,
                ]) }}">
                    <em>{{ $page->name }}</em>
                    <dfn>{{ $page->getUrl() }}</dfn>
                </a>
            </li>
            @endforeach
        </ol>
    </div>
@endsection
