@extends('layouts.main.simplebox')

@section('title', "Reset Password")

@section('body')
<form class="form-pw" role="form" method="POST" action="{{ route('password.update') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    <input type="hidden" name="token" value="{{ $token }}">

    <fieldset class="form-fields">
        <legend class="form-legend">@lang('panel.password.reset')</legend>

        <div class="field row-email">
            <label class="field-label" for="email">@lang('panel.field.email')</label>
            <input class="field-control"  id="email" name="email" type="email" value="{{ old('email') }}" />
        </div>

        <div class="field row-password">
            <label class="field-label" for="password">@lang('panel.field.password')</label>
            <input class="field-control"  id="password" name="password" type="password" />
        </div>

        <div class="field row-password_new">
            <label class="field-label" for="password_new">@lang('panel.field.password_confirm')</label>
            <input class="field-control"  id="password_new" name="password_confirmation" type="password" />
        </div>

        <div class="field row-submit">
            <button type="submit">@lang('panel.password.reset')</button>
        </div>
    </fieldset>
</form>
@endsection
