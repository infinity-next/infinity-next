@extends('layouts.main.panel')

@section('title', trans("panel.title.delete_board", [ 'board_uri' => $board->board_uri ]))

@section('body')
{!! Form::open([
    'route' => [
        'panel.board.destroy',
        'board' => $board,
    ],
    'method' => 'DELETE',
    'id'     => "delete-page",
    'class'  => "form-config",
]) !!}
    <dl class="option option-explanation">
        <dt class="option-term"></dt>
        <dd class="option-definition">
            <p>@lang('panel.confirm.delete_board', [ 'board_uri' => $board->board_uri ])</p>
        </dd>
    </dl>

    <div class="field row-submit">
        <button type="submit" name="action" value="delete" class="field-submit delete">@lang('panel.action.delete')</button>
    </div>
{!! Form::close() !!}
@endsection
