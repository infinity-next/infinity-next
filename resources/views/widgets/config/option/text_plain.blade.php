<dl class="option option-{{{ $option_name }}}">
    <dt class="option-term">
        <span class="field-label">
            @lang("config.option.{$option_name}")
        </span>
    </dt>
    <dd class="option-definition">
        <span class="field-control">{{ $option_value }}</span>

        @include('widgets.config.helper')
    </dd>
</dl>
