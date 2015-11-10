<div div class="post-action-bar">
	@if (!isset($reply_to) || !$reply_to)
	<div class="post-action-tab action-tab-reply">
		<a class="post-action-label post-action-reply" href="{!! $post->getURL() !!}" data-instant>@lang('board.action.view')</a>
	</div>
	@endif
</div>