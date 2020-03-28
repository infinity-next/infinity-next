@extends('layouts.main.panel')

@section('title', trans("panel.title.board_staff_delete", [
    'board_uri' => $board->board_uri,
]))

@section('body')
{!! Form::open([
    'url'    => $staff->getBoardStaffUrl($board, 'destroy'),
    'method' => "DELETE",
    'files'  => true,
    'id'     => "config-staff",
    'class'  => "form-config",
]) !!}
    <div class="field row-delete">

        @if ($staff->user_id === user()->user_id)
        <span class="field-confirm">
            @lang('panel.confirm.delete_staff_self')

            <p>
                <label>
                    <input name="confirmation" value="1" type="checkbox" />
                    @lang('config.confirm')
                </label>
            </p>
        </span>
        @else
        <span class="field-confirm">
            @lang('config.confirm')
        </span>
        @endif

        <button type="submit" class="field-delete">@lang('panel.action.delete_staff')</button>
    </div>
{!! Form::close() !!}
@endsection
