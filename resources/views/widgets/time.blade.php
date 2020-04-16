<time data-widget="time" datetime="{{ $carbon->toAtomString() }}">
    {{ $carbon->formatLocalized( trans('widget.time.format') )}}
</time>
