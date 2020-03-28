<dl class="option option-{{{ $option_name }}}">
    <dt class="option-term">
        {!! Form::label(
            $option_name,
            trans("config.option.{$option_name}"),
            [
                'class' => "field-label",
        ]) !!}
        @include('widgets.config.lock')
    </dt>
    <dd class="option-definition">
        <select class="field-control" name="{{ $option_name }}">
            <option value="">@lang('config.any_language')</option>

            @foreach (trans('lang') as $option_choice => $option_choice_label)
            <option value="{{ $option_choice }}" {{ $option_choice == $option_value ? "selected=\"selected\"" : "" }}>{{ $option_choice_label }}</option>
            @endforeach
        </select>

        @include('widgets.config.helper')
    </dd>
</dl>
