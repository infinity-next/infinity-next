@extends('layouts.main.panel')

@section('title', trans("panel.title.delete_page"))

@section('body')
{!! Form::open([
    'route' => [
        // Route Name
        'panel.' .
        ($board->exists ? 'board.' : '') .
        'page.' .
        'destroy',

        // Page
        'board' => $board->exists ? $board : null,
        'page' => $page->page_id,
    ],
    'method' => 'DELETE',
    'id'     => "config-page",
    'class'  => "form-config",
]) !!}
    <div class="field row-delete">
        <span class="field-confirm">@lang('config.confirm')</span>
        <button type="submit" class="field-delete">@lang('panel.action.delete')</button>
    </div>
{!! Form::close() !!}
@endsection
