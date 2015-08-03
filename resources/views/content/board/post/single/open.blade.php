@if ($catalog)
<div class="post-action-tab tab-view-thread">
	<a class="post-action-label post-action-open" href="{!! $thread->getURL() !!}" data-instant>@lang('board.action.view')</a>
</div>
@endif