<dl class="option option-{{{ $option_name }}}">
    <dt class="option-term">
        {!! Form::label(
            $option_name,
            trans("config.option.{$option_name}"),
            [
                'class' => "field-label",
        ]) !!}
        @include('widgets.config.lock', [ 'option' => $option ])
    </dt>
    <dd class="option-definition">
        {!! Form::number(
            $option_name,
            $option_value,
            [
                'id'        => $option_name,
                'class'     => "field-control",
                    isset($board) && user()->cannot('setting-edit', [$board, $option ?? null]) ? 'disabled' : 'data-enabled',
        ]) !!}

        @include('widgets.config.helper')
    </dd>
</dl>
