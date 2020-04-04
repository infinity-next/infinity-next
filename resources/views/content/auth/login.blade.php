@extends('layouts.main.simplebox')

@section('title', "Login")

@section('body')
<form class="form-auth" role="form" method="POST" action="{{ route('auth.login.attempt') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <fieldset class="form-fields">
        <legend class="form-legend">
            @lang('panel.field.login')
        </legend>

        <div class="field row-username">
            <label class="field-label" for="username">
                @lang('panel.field.uid')
            </label>
            <input class="field-control" id="username" name="username" type="text" maxlength="64" />
        </div>

        <div class="field row-password">
            <label class="field-label" for="password">
                @lang('panel.field.password')
            </label>
            <input class="field-control" id="password" name="password" type="password" maxlength="255" />
        </div>

        <div class="field row-forgot">
            <a href="{{ route('auth.password.request') }}">
                @lang('panel.field.login_link.password_forgot')
            </a>
        </div>

        <div class="field row-register">
            <a href="{{ route('auth.register') }}">
                @lang('panel.field.login_link.register')
            </a>
        </div>

        <div class="field row-submit">
            <button type="submit" class="field-submit">
                @lang('panel.field.login')
            </button>
            <label class="field-label-inline">
                <input type="checkbox" name="remember"/>
                @lang('panel.field.remember')</label>
        </div>
    </fieldset>
</form>
@endsection
