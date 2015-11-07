<nav class="pagination pagination-automatic ">
	@if ($paginator->count() > 1)
	<div class="pagination-buttons buttons-before">
		@if ($paginator->currentPage() > 1)
			<a class="button pagination-button pagination-first" href="{{ $boards->url(1) }}" title="@lang('board.first')" data-instant>&lt;&lt;</a>
			<a class="button pagination-button pagination-prev" href="{{ $boards->previousPageUrl() }}" title="@lang('board.previous')" data-instant>&lt;</a>
		@else
			<button class="pagination-button pagination-first" title="@lang('board.first')" disabled>&lt;&lt;</button>
			<button class="pagination-button pagination-prev" title="@lang('board.previous')" disabled>&lt;</button>
		@endif
	</div>
	
	<ul class="pagination-pages">
		@for ($i = 1; $i <= $paginator->lastPage(); ++$i)
		<li class="pagination-page">
			<a class="pagination-link @if ($i == $paginator->currentPage()) pagination-active @endif" href="{{ $paginator->url($i) }}" data-instant>{{{$i}}}</a>
		</li>
		@endfor
	</ul>
	
	<div class="pagination-buttons buttons-after">
		@if ($paginator->hasMorePages())
			<a class="button pagination-button pagination-next" href="{{ $boards->nextPageUrl() }}" title="@lang('board.next')" data-instant>&gt;</a>
			<a class="button pagination-button pagination-last" href="{{ $boards->url($paginator->count()) }}" title="@lang('board.last')">&gt;&gt;</a>
		@else
			<button class="pagination-button pagination-next" title="@lang('board.next')" disabled>&gt;</button>
			<button class="pagination-button pagination-last" title="@lang('board.last')" disabled>&gt;&gt;</button>
		@endif
	</div>
	@endif
</nav>