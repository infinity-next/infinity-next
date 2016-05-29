<fieldset class="form-fields" id="fields-register">
    <legend class="form-legend">@lang('config.legend.account_basic')</legend>

    <div class="field row-username">
        <label class="field-label" for="username">@lang('panel.field.username')</label>
        <input class="field-control" id="username" name="username" value="{{ old('username') }}" type="text" maxlength="64" />
    </div>

    <div class="field row-email">
        <label class="field-label" for="email">@lang('panel.field.email')</label>
        <span class="field-description">@lang('panel.field.desc.email')</span>

        <input class="field-control" id="email" name="email" value="{{ old('email') }}" type="email" maxlength="254" />
    </div>

    <div class="field row-password">
        <label class="field-label" for="password">@lang('panel.field.password')</label>
        <input class="field-control" id="password" name="password" type="password" />
    </div>

    <div class="field row-password_confirmation">
        <label class="field-label" for="password_confirmation">@lang('panel.field.password_confirm')</label>
        <input class="field-control" id="password_confirmation" name="password_confirmation" type="password" />
    </div>

    @if (!isset($captchaless) || !$captchaless)
    <div class="field row-captcha">
        <label class="field-label" for="captcha" data-widget="captcha">
            {!! captcha() !!}
        </label>
        <input class="field-control" id="captcha" name="captcha" type="text" />
    </div>
    @endif
</fieldset>
