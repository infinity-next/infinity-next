@extends('layouts.main.panel')

@section('title', trans("panel.title.feature_board"))

@section('body')
{!! Form::open([
    'route' => [
        'panel.board.feature.update',
        'board' => $board,
    ],
    'method' => 'POST',
    'id'     => "feature-page",
    'class'  => "form-config",
]) !!}
    <dl class="option option-explanation">
        <dt class="option-term"></dt>
        <dd class="option-definition">
            <p>@lang('panel.confirm.feature')</p>
            <p>@choice('panel.confirm.featured_at', !is_null($board->featured_at) ? 1 : 0, [
                'featured_at' => $board->featured_at,
            ])</p>
        </dd>
    </dl>

    <div class="field row-submit">
        <button type="submit" name="action" value="update" class="field-submit">@lang('panel.action.feature')</button>

        @if (!is_null($board->featured_at))
        <button type="submit" name="action" value="delete" class="field-delete">@lang('panel.action.unfeature')</button>
        @endif
    </div>
{!! Form::close() !!}
@endsection
