@extends('layouts.main.simplebox')

@section('title', "Reset Password")

@section('body')
<form class="form-pw" role="form" method="POST" action="{{ route('panel.password.email') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <fieldset class="form-fields">
        <legend class="form-legend">@lang('panel.password.reset')</legend>

        <div class="field row-email">
            <label class="field-label" for="email">@lang('panel.field.email')</label>
            <input class="field-control"  id="email" name="email" type="email" value="{{ old('email') }}" />
        </div>

        <div class="field row-captcha">
            <label class="field-label" for="captcha" data-widget="captcha">{!! captcha() !!}</label>
            <input class="field-control" id="captcha" name="captcha" type="text" />
        </div>

        <div class="field row-submit">
            <button type="submit">Send Password Reset Link</button>
        </div>
    </fieldset>
</form>
@endsection
