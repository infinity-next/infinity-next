@extends('layouts.main.panel')

@section('title', trans("panel.title.board_staff_edit", [
    'board_uri'  => $board->board_uri,
    'staff_name' => $staff->getDisplayName(),
]))

@section('actions')
    @if (!$roles->count())
    <a class="panel-action" href="{{ $board->getPanelUrl('staff.create') }}">+ @lang('panel.action.add_role')</a>
    @endif
@endsection

@section('body')
    {!! Form::open([
        'url'    => Request::url(),
        'method' => "PATCH",
        'files'  => true,
        'id'     => "config-staff",
        'class'  => "form-staff",
    ]) !!}
        @if ($roles->count())
            @include('content.panel.board.staff.castes')

            <div class="field row-submit">
                <button type="submit" class="field-submit">@lang('panel.action.edit_staff')</button>
            </div>
        @else
            <p>@lang('panel.error.staff.no_roles')</p>
        @endif
    {!! Form::close() !!}
@endsection
