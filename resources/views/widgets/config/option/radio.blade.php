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
        @foreach ($option_choices as $option_choice_label => $option_choice)
        <div class="option-choice">
            {!! Form::radio(
                $option_name,
                $option_choice,
                $option_value == $option_choice || count($option_choices) === 1,
                [
                    'id'        => "{$option_name}-{$option_choice}",
                    'class'     => "field-control",
                        user()->cannot('setting-edit', [$board ?? null, $option ?? null]) ? 'disabled' : 'data-enabled',
            ]) !!}
            {!! Form::label(
                "{$option_name}-{$option_choice}",
                trans_choice($option_choice_label, 0),
                [
                    'class' => "field-label",
            ]) !!}
        </div>
        @endforeach

        @include('widgets.config.helper')
    </dd>
</dl>
