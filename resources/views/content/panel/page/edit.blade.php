@extends('layouts.main.panel')

@section('title', trans($page ? "panel.title.page_update" : "panel.title.page_create"))

@section('actions')
    @if ($page)
    <a class="panel-action" href="{{ route( $board->exists
        ? 'panel.board.page.show'
        : 'panel.page.show', [
            'page'  => $page,
            'board' => $board,
        ]
    ) }}">@lang('panel.action.view_page')</a>
    @endif
@endsection

@section('body')
{!! Form::open([
    'route' => [
        // Route Name
        'panel.' .
        ($board->exists ? 'board.' : '') .
        'page.' .
        ($page ? 'update' : 'store'),

        // Page
        'board' => $board->exists ? $board : null,
        'page' => $page ? $page->page_id : null,
    ],
    'method' => $page ? 'PATCH' : 'POST',
    'id'     => "config-page",
    'class'  => "form-config",
]) !!}

    @include('content.panel.page.form')

    <div class="field row-submit">
        <button type="submit" class="field-submit">{{ trans(
            $page ? 'panel.action.update_page' : 'panel.action.create_page'
        ) }}</button>
    </div>
{!! Form::close() !!}
@endsection
