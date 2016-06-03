@if (count($group->options) > 0)
<fieldset class="form-fields group-{{{ $group->group_name }}}">
    <legend class="form-legend">{{ trans("config.legend.{$group->group_name}") }}</legend>

    @foreach ($group->options as $optionIndex => $option)
        @include($option->getTemplate($c), [
            'option'            => $option,
            'option_name'       => $option->option_name,
            'option_value'      => $option->getDisplayValue(),
            'format_parameters' => $option->getFormatParameters(),
        ])
    @endforeach
</fieldset>
@endif
