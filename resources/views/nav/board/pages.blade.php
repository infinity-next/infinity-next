<nav class="pagination @if($showPages) pagination-full @endif @if($showCatalog) pagination-catalog @endif @if($showIndex) pagination-index @endif">
	<div class="pagination-buttons buttons-pages">
		@if ($showIndex)
		<a class="button pagination-button" href="/{{ $board->board_uri }}">Index</a>
		@endif
		
		@if ($showCatalog)
		<a class="button pagination-button" href="/{{ $board->board_uri }}/catalog">Catalog</a>
		@endif
		
		<a class="button pagination-button" href="/{{ $board->board_uri }}/logs">Logs</a>
	</div>
	
	@if (isset($pages) && $showPages)
	<div class="pagination-buttons buttons-before">
		@if ($page > 1)
			<a class="button pagination-button pagination-first" href="{{{url($board->board_uri)}}}" title="@lang('board.first')">&lt;&lt;</a>
		@else
			<button class="pagination-button pagination-first" title="@lang('board.first')" disabled>&lt;&lt;</button>
		@endif
		
		@if ($pagePrev !== false)
			<a class="button pagination-button pagination-prev" href="{{{url("{$board->board_uri}/{$pagePrev}")}}}" title="@lang('board.previous')">&lt;</a>
		@else
			<button class="pagination-button pagination-prev" title="@lang('board.previous')" disabled>&lt;</button>
		@endif
	</div>
	
	<ul class="pagination-pages">
		@for ($i = 1; $i <= $pages; ++$i)
		<li class="pagination-page">
			<a class="pagination-link @if ($i == $page) pagination-active @endif" href="{{{url("{$board->board_uri}/{$i}")}}}">{{{$i}}}</a>
		</li>
		@endfor
	</ul>
	
	<div class="pagination-buttons buttons-after">
		@if ($pageNext !== false)
			<a class="button pagination-button pagination-next" href="{{{url("{$board->board_uri}/{$pageNext}")}}}" title="@lang('board.next')">&gt;</a>
		@else
			<button class="pagination-button pagination-next" title="@lang('board.next')" disabled>&gt;</button>
		@endif
		
		@if ($page < $pages)
			<a class="button pagination-button pagination-last" href="{{{url("{$board->board_uri}/{$pages}")}}}" title="@lang('board.last')">&gt;&gt;</a>
		@else
			<button class="pagination-button pagination-last" title="@lang('board.last')" disabled>&gt;&gt;</button>
		@endif
	</div>
	@endif
</nav>