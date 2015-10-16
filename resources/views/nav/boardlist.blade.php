@if(isset($boardbar))
<nav class="boardlist">
	<!-- Yes, this is only here so you can style it back into existance. -->
	<div class="boardlist-row row-boards" {{isset($board) ? "data-instant" : ""}}>
		<ul class="boardlist-categories">
		@foreach ($boardbar as $boards)
			<li class="boardlist-category">
				<ul class="boardlist-items">
					@foreach ($boards as $board)
					<li class="boardlist-item">
						<a href="{!! url($board->board_uri) !!}" class="boardlist-link">{!! $board->board_uri !!}</a>
					</li>
					@endforeach
				</ul>
			</li>
		@endforeach
		</ul>
	</div>
	
	<div class="board-row row-favorites" style="display: none;">
		<!-- This is for JS templating. -->
		<ul class="boardlist-categories">
			<li class="boardlist-category">
				<ul class="boardlist-items">
					<li class="boardlist-item">
						<a href="" class="boardlist-link"></a>
					</li>
				</ul>
			</li>
		</ul>
	</div>
</nav>
@endif