@spaceless
<div class="dice {{
    $dice->rolls->count() > 1 ? 'roll-multiple' : 'roll-once'
}} {{
    $dice->isBinary() ? 'roll-binary' : 'roll-many'
}}" dir="ltr">
    <strong class="dice-throw">
        <span class="dice-heading">@lang($dice->isBinary() ? 'widget.dice.flipping' : 'widget.dice.rolling')</span>

        <abbr title="@lang('widget.dice.main')" class="dice-datum dice-main">
            <span class="dice-rolling">{!! $dice->rolling !!}</span>
            <span class="dice-d">d</span>
            <span class="dice-sides">{!! $dice->sides !!}</span>
        </abbr>

        @if ($dice->modifier !== 0)
        <abbr title="@lang('widget.dice.modifier')" class="dice-datum dice-modifier">
            @if ($dice->modifier > 0)<span class="dice-modifier-add">+</span>@endif
            @if ($dice->modifier < 0)<span class="dice-modifier-sub">-</span>@endif
            <span class="dice-modifier-amt">{{ abs($dice->modifier) }}</span>
        </abbr>
        @endif

        @if ($dice->minimum)
        <abbr title="@lang('widget.dice.minimum')" class="dice-rule rule-minimum">
            <span class="dice-symbol">^</span>
            <span class="dice-criterion">{{ $dice->minimum }}</span>
        </abbr>
        @endif

        @if ($dice->maximum)
        <abbr title="@lang('widget.dice.maximum')" class="dice-rule rule-maximum">
            <span class="dice-symbol">v</span>
            <span class="dice-criterion">{{ $dice->maximum }}</span>
        </abbr>
        @endif

        @if ($dice->greater_than)
        <abbr title="@lang('widget.dice.greater_than')" class="dice-rule rule-less_than">
            <span class="dice-symbol">&lt;</span>
            <span class="dice-criterion">{{ $dice->greater_than }}</span>
        </abbr>
        @endif

        @if ($dice->less_than)
        <abbr title="@lang('widget.dice.less_than')" class="dice-rule rule-greater_than">
            <span class="dice-symbol">&gt;</span>
            <span class="dice-criterion">{{ $dice->less_than }}</span>
        </abbr>
        @endif

        <abbr title="@lang('widget.dice.total')" class="dice-total">
            <span class="dice-symbol">=</span>
            <span class="dice-total-text">{{ $dice->total }}</span>

            @if ($dice->isBinary())
            <span class="dice-binary-total">
                <span class="dice-binary-heads">{{ $dice->rolls->filter(function($i){ return $i === 2; })->count() }}</span>
                <span class="dice-binary-tails">{{ $dice->rolls->filter(function($i){ return $i === 1; })->count() }}</span>
            </span>
            @endif
        </abbr>
    </strong>

    <span class="dice-rolls">
    @foreach($dice->rolls as $roll)
        <span class="dice-roll {{ (!$dice->isValidRoll($roll)
            ? 'dice-roll-invalid'
            : (!$dice->isCountedRoll($roll)
                ? 'dice-roll-ignored'
                : 'dice-roll-valid'
            )) }}">{{ $roll }}</span>
    @endforeach
    </span>
</div>
@endspaceless
