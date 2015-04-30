<nav class="boardlist">
	<ul class="boardlist-categories">
	@foreach ($boardbar as $boards)
		<li class="boardlist-category">
			<ul class="boardlist-items">
				@foreach ($boards as $board)
				<li class="boardlist-item"><a href="/{!! $board->uri !!}/" class="boardlist-link">{!! $board->uri !!}</a></li>
				@endforeach
			</ul>
		</li>
	@endforeach
	</ul>
</nav>