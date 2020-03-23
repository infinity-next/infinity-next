<div class="thread-autoupdater require-js autoupdater" data-widget="autoupdater" data-url="{!! $thread->getApiUrl('thread') !!}">
    <span class="autoupdater-item item-enabled">
        <label><input class="autoupdater-enabled" type="checkbox" checked /> @lang('widget.autoupdater.enable')</label>
    </span>
    <div class="autoupdater-item item-timer">
        (<span class="autoupdater-timer" data-time="3">3s</span>)
    </span>
    <span class="autoupdater-item item-update">
        (<a href="#update-now" class="autoupdater-update">@lang('widget.autoupdater.update')</a><span class="autoupdater-updating">@lang('widget.autoupdater.updating')</span>)
    </span>
</div>
