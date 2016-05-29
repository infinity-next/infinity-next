@if ($catalog)
<div div class="post-action-bar action-bar-open">
	@if (!isset($reply_to) || !$reply_to)
	<div class="post-action-tab action-tab-reply">
		<a class="post-action-label post-action-reply" href="{!! $post->getUrl() !!}" data-instant>@lang('board.action.view')</a>
	</div>
	@endif
</div>
@endif
