<nav class="gnav">
	<div class="grid-container">
		<div class="grid-100">
			<ul class="gnav-groups">
				<li class="gnav-group">
					<ul class="gnav-items">
						<!-- Site Index -->
						<li class="gnav-item gnav-active"><a href="{!! url("/") !!}" class="gnav-link">@lang('nav.global.home')</a></li>
						
						<!-- Control Panel -->
						<li class="gnav-item"><a href="{!! url("boards.html") !!}" class="gnav-link">@lang('nav.global.boards')</a></li>
						
						<!-- Overboard -->
						<li class="gnav-item"><a href="{!! url("boards.html") !!}" class="gnav-link">@lang('nav.global.recent_posts')</a></li>
						
						<!-- Control Panel -->
						<li class="gnav-item"><a href="{!! url("cp") !!}" class="gnav-link">@lang('nav.global.panel')</a></li>
						
						{{--
						@if (isset($user) && $user->canCreateBoard())
						<!-- Create a Board -->
						<li class="gnav-item"><a href="{!! url("cp/boards/create") !!}" class="gnav-link">@lang('nav.global.new_board')</a></li>
						@endif
						
						@if (env('CONTRIB_ENABLED', false))
						<!-- Fundraiser Page -->
						<li class="gnav-item"><a href="{!! url("contribute") !!}" class="gnav-link">@lang('nav.global.contribute')</a></li>
						
						<!-- Donation Page -->
						<li class="gnav-item"><a href="{!! secure_url("cp/donate") !!}" class="gnav-link">@lang('nav.global.donate')</a></li>
						@endif
						
						@if (isset($c) && $c->option('adventureEnabled'))
						<!-- Adventure! -->
						<li class="gnav-item"><a href="{!! url("cp/adventure") !!}" class="gnav-link" data-no-instant>@lang('nav.global.adventure')</a></li>
						@endif
						--}}
					</ul>
				</li>
			</ul>
			
			@if(isset($boardbar))
			<!-- Yes, this is only here so you can style it back into existance. -->
			<div class="gnav-row row-boards" {{isset($board) ? "data-instant" : ""}}>
				<ul class="gnav-categories">
				@foreach ($boardbar as $boards)
					<li class="gnav-category">
						<ul class="gnav-items">
							@foreach ($boards as $board)
							<li class="gnav-item"><a href="{!! url($board->board_uri) !!}" class="gnav-link">{!! $board->board_uri !!}</a></li>
							@endforeach
						</ul>
					</li>
				@endforeach
				</ul>
			</div>
			@endif
		</div>
	</div>
</nav>
