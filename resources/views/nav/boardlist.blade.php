<nav class="boardlist">
	<div class="boardlist-row row-pages">
		<ul class="boardlist-categories">
			<li class="boardlist-category">
				<ul class="boardlist-items">
					<li class="boardlist-item"><a href="{!! url("/") !!}" class="boardlist-link">Home</a></li>
					<li class="boardlist-item"><a href="{!! url("contribute") !!}" class="boardlist-link">Contribute</a></li>
				</ul>
			</li>
		</li>
	</div>
	<div class="boardlist-row row-boards">
		<ul class="boardlist-categories">
		@foreach ($boardbar as $boards)
			<li class="boardlist-category">
				<ul class="boardlist-items">
					@foreach ($boards as $board)
					<li class="boardlist-item"><a href="{!! url($board->uri) !!}" class="boardlist-link">{!! $board->uri !!}</a></li>
					@endforeach
				</ul>
			</li>
		@endforeach
		</ul>
	</div>
</nav>