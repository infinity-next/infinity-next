<div class="thread-autoupdater require-js autoupdater" data-widget="autoupdater" data-id="{{ $thread->post_id }}" data-url="{!! $thread->getApiUrl('thread') !!}">
    <div class="autoupdater-poll" style="display: none;">
        <span class="autoupdater-item item-enabled">
            <label><input class="autoupdater-enabled" type="checkbox" checked /> @lang('widget.autoupdater.enable')</label>
        </span>
        <span class="autoupdater-item item-timer">
            (<span class="autoupdater-timer" data-time="3">3s</span>)
        </span>
        <span class="autoupdater-item item-update">
            (<a href="#update-now" class="autoupdater-update">@lang('widget.autoupdater.update')</a><span class="autoupdater-updating">@lang('widget.autoupdater.updating')</span>)
        </span>
    </div>
    <div class="autoupdater-ws" style="display: none;">
        <i class="fa fa-wifi"></i> @lang('widget.autoupdater.websocket')
    </div>
</div>
