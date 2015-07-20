<nav class="boardlist">
	<div class="boardlist-row row-pages">
		<ul class="boardlist-categories">
			<li class="boardlist-category">
				<ul class="boardlist-items">
					<!-- Site Index -->
					<li class="boardlist-item"><a href="{!! url("/") !!}" class="boardlist-link">Home</a></li>
					
					<!-- Fundraiser Page -->
					<li class="boardlist-item"><a href="{!! url("cp") !!}" class="boardlist-link">Control Panel</a></li>
					
					@if ($user->canCreateBoard())
					<!-- Create a Board -->
					<li class="boardlist-item"><a href="{!! url("cp/boards/create") !!}" class="boardlist-link">Create Board</a></li>
					@endif
					
					@if (env('CONTRIB_ENABLED', false))
					<!-- Fundraiser Page -->
					<li class="boardlist-item"><a href="{!! secure_url("contribute") !!}" class="boardlist-link">Contribute</a></li>
					
					<!-- Donation Page -->
					<li class="boardlist-item"><a href="{!! url("cp/donate") !!}" class="boardlist-link">Donate</a></li>
					@endif
				</ul>
			</li>
		</li>
	</div>
	
	@if(isset($boardbar))
	<div class="boardlist-row row-boards">
		<ul class="boardlist-categories">
		@foreach ($boardbar as $boards)
			<li class="boardlist-category">
				<ul class="boardlist-items">
					@foreach ($boards as $board)
					<li class="boardlist-item"><a href="{!! url($board->board_uri) !!}" class="boardlist-link">{!! $board->board_uri !!}</a></li>
					@endforeach
				</ul>
			</li>
		@endforeach
		</ul>
	</div>
	@endif
</nav>
