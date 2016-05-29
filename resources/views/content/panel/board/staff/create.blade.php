@extends('layouts.main.panel')

@section('title', trans("panel.title.board_staff_add", [
    'board_uri' => $board->board_uri,
]))

@section('actions')
    @if (!$roles->count())
    <a class="panel-action" href="{{ $board->getPanelUrl('roles.create') }}">+ @lang('panel.action.add_role')</a>
    @endif
@endsection

@section('body')
    {!! Form::open([
        'url'    => Request::url(),
        'method' => "PUT",
        'files'  => true,
        'id'     => "config-staff",
        'class'  => "form-staff",
    ]) !!}
        @if ($roles->count())
        <div class="form-select-group">
            {!! Form::radio(
                "staff-source",
                "existing",
                old("staff-source") == "existing",
                [
                    'id'        => "add-existing",
                    'class'     => "field-control form-selection",
            ]) !!}

            <label class="form-selector" for="add-existing">
                @lang('panel.staff.select_existing_form')
            </label>

            <fieldset class="form-fields form-selectable" id="fields-existing">
                <legend class="form-legend">@lang('config.legend.account_existing')</legend>

                <div class="field row-existinguser">
                    <label class="field-label" for="existinguser">@lang('panel.field.username')</label>
                    <input class="field-control" id="existinguser" name="existinguser" value="{{ old('existinguser') }}" type="text" maxlength="64" />
                </div>

            </fieldset>
        </div>

        <div class="form-select-group">
            {!! Form::radio(
                "staff-source",
                "register",
                old("staff-source") != "existing",
                [
                    'id'        => "register-new",
                    'class'     => "field-control form-selection",
            ]) !!}

            <label class="form-selector" for="register-new">@lang('panel.staff.select_register_form')</label>

            <div class="form-selectable">
                @include('content.panel.auth.register.form', [ 'captchaless' => true ])
            </div>
        </div>

        @include('content.panel.board.staff.castes')

        <div class="field row-captcha">
            <label class="field-label" for="captcha">
                {!! captcha() !!}
            </label>
            <input class="field-control" id="captcha" name="captcha" type="text" />
        </div>

        <div class="field row-submit">
            <button type="submit" class="field-submit">@lang('panel.action.add_staff')</button>
        </div>
        @else
            <p>@lang('panel.error.staff.no_roles')</p>
        @endif
    {!! Form::close() !!}
@endsection
