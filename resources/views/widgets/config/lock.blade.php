@if (isset($board) && isset($option) && (user()->can('setting-lock', $option) || $option->isLocked()))
<label class="field-lock" title="{{ user()->can('setting-lock', $option) ? trans('config.locking') : trans('config.locked') }}">
    (<i class="fas fa-lock"></i>@can('setting-edit', [$board, $option])&nbsp;{!! Form::checkbox(
        "lock[{$option_name}]",
        1,
        $option->is_locked,
        [
            'id'        => "lock-{$option_name}",
            'class'     => "field-lockbox",
    ]) !!}
    @endcan)
</label>
@endif
