<li class="thread-autoupdater require-js autoupdater" data-widget="autoupdater" data-url="{!! url($thread->urlJson()) !!}">
	<ul class="autoupdater-items">
		<li class="autoupdater-item item-enabled">
			<label><input id="autoupdater-enabled" type="checkbox" checked /> @lang('widget.autoupdater.enable')</label>
		</li>
		<li class="autoupdater-item item-timer">
			(<span id="autoupdater-timer" data-time="3">3s</span>)
		</li>
		<li class="autoupdater-item item-update">
			(<a href="#update-now" class="autoupdater-update">@lang('widget.autoupdater.update')</a><span id="autoupdater-updating">@lang('widget.autoupdater.updating')</span>)
		</li>
	</ul>
</li>
