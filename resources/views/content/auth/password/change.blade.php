@extends('layouts.main.panel')

@section('title', "Change Password")

@section('body')
<form class="form-pw grid-33" role="form" method="POST" action="{{ route('panel.password.update') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <fieldset class="form-fields">
        <legend class="form-legend">
            @lang('panel.password.reset')
        </legend>

        <input id="username" name="username" type="hidden" value="{!! Auth::user()->username !!}" />

        <div class="field row-password">
            <label class="field-label" for="password">
                @lang('panel.field.password_current')
            </label>
            <input class="field-control"  id="password" name="password" type="password" />
        </div>

        <div class="field row-password_new">
            <label class="field-label" for="password_new">
                @lang('panel.field.password_new')
            </label>
            <input class="field-control"  id="password_new" name="password_new" type="password" />
        </div>

        <div class="field row-password_new_copassword_new_confirmationnfirm">
            <label class="field-label" for="password_new_confirmation">
                @lang('panel.field.password_new_confirm')
            </label>
            <input class="field-control"  id="password_new_confirmation" name="password_new_confirmation" type="password" />
        </div>

        <div class="field row-submit">
            <button type="submit">Change Password</button>
        </div>
    </fieldset>
</form>
@endsection
