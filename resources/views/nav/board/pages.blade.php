<nav class="pagination @if($showPages) pagination-full @endif @if($showCatalog) pagination-catalog @endif @if($showIndex) pagination-index @endif">
	<div class="pagination-buttons buttons-pages">
		@if ($showIndex)
		<a class="button pagination-button" href="{{ $board->getUrl() }}" data-instant>@lang('board.button.index')</a>
		@endif

		@if ($showCatalog)
		<a class="button pagination-button" href="{{ $board->getUrl('catalog') }}" data-instant>@lang('board.button.catalog')</a>
		@endif

		@if ($user->canViewLogs($board))
		<a class="button pagination-button" href="{{ $board->getUrl('logs') }}" data-instant>@lang('board.button.logs')</a>
		@endif

		@if (isset($header))
		@if (!$header)
		<a class="button pagination-button go-to-top" href="#top">@lang('board.button.gotop')</a>
		@else
		<a class="button pagination-button go-to-bottom" href="#bottom">@lang('board.button.gobot')</a>
		@endif
		@endif
	</div>

	@if (isset($pages) && $showPages)
	<div class="pagination-buttons buttons-before">
		@if ($page > 1)
			<a class="button pagination-button pagination-first" href="{{ $board->getUrl() }}" title="@lang('board.first')" data-instant>&lt;&lt;</a>
		@else
			<button class="pagination-button pagination-first" title="@lang('board.first')" disabled>&lt;&lt;</button>
		@endif

		@if ($pagePrev !== false)
			<a class="button pagination-button pagination-prev" href="{{ $board->getUrl($pagePrev) }}" title="@lang('board.previous')" data-instant>&lt;</a>
		@else
			<button class="pagination-button pagination-prev" title="@lang('board.previous')" disabled>&lt;</button>
		@endif
	</div>

	<ul class="pagination-pages">
		@for ($i = 1; $i <= $pages; ++$i)
		<li class="pagination-page">
			<a class="pagination-link @if ($i == $page) pagination-active @endif" href="{{ $board->getUrl($i) }}" data-instant>{{{$i}}}</a>
		</li>
		@endfor
	</ul>

	<div class="pagination-buttons buttons-after">
		@if ($pageNext !== false)
			<a class="button pagination-button pagination-next" href="{{ $board->getUrl($pageNext) }}" title="@lang('board.next')" data-instant>&gt;</a>
		@else
			<button class="pagination-button pagination-next" title="@lang('board.next')" disabled>&gt;</button>
		@endif

		@if ($page < $pages)
			<a class="button pagination-button pagination-last" href="{{ $board->getUrl($pages) }}" title="@lang('board.last')" data-instant>&gt;&gt;</a>
		@else
			<button class="pagination-button pagination-last" title="@lang('board.last')" disabled>&gt;&gt;</button>
		@endif
	</div>
	@endif
</nav>
