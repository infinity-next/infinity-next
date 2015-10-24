<!-- Yes, this is only here so you can style it back into existance. -->
<nav class="boardlist" data-instant>
	
	<div class="boardlist-row row-links">
		<ul class="boardlist-categories">
			<li class="boardlist-category">
				<ul class="boardlist-items">
					@foreach (Settings::getNavigationPrimary() as $navItem => $navUrl)
					<li class="boardlist-item">
						<a href="{!! $navUrl !!}" class="boardlist-link">{{ trans("nav.global.{$navItem}") }}</a>
					</li>
					@endforeach
				</ul>
			</li>
		</ul>
	</div>
	
	<div class="boardlist-row row-boards">
		<ul class="boardlist-categories">
		@foreach (Settings::getNavigationPrimaryBoards() as $boards)
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
	
</nav>