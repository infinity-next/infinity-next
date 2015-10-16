<nav class="gnav" data-widget="gnav">
	<div class="grid-container">
		<div class="grid-100">
			<ul class="gnav-groups">
				<li class="gnav-group">
					<ul class="gnav-items">
						<!-- Site Index -->
						<li class="gnav-item item-home gnav-active"><a href="{!! url("/") !!}" class="gnav-link">@lang('nav.global.home')</a></li>
						
						<!-- Board Directory -->
						<li class="gnav-item item-boards">
							<a href="{!! url("boards.html") !!}" class="gnav-link">@lang('nav.global.boards')</a>
							
							@if(isset($boardbar))
							<div class="flyout flyout-boards">
								<ul class="flyout-cols">
									<li class="flyout-col" id="favorite-boards">
										<div class="flyout-col-title">{{ trans("nav.global.flyout.favorite_boards") }} <i class="fa fa-star"></i></div>
										<ul class="flyout-list">
											<li class="flyout-item">
												<a href="{!! url('b') !!}" class="flyout-link">
													<span class="flyout-uri">/b/</span>
													<span class="flyout-title">Random</span>
												</a>
											</li>
										</ul>
									</li>
									
									@foreach ($boardbar as $groupname => $boards)
									<li class="flyout-col">
										<div class="flyout-col-title">{{ trans("nav.global.flyout.{$groupname}") }}</div>
										<ul class="flyout-list">
											@foreach ($boards as $board)
											<li class="flyout-item">
												<a href="{!! url($board->board_uri) !!}" class="flyout-link">
													<span class="flyout-uri">/{!! $board->board_uri !!}/</span>
													<span class="flyout-title">{!! $board->title !!}</span>
												</a>
											</li>
											@endforeach
										</ul>
									</li>
									@endforeach
								</ul>
							</div>
							@endif
						</li>
						
						<!-- Overboard -->
						<li class="gnav-item item-recent"><a href="{!! url("overboard.html") !!}" class="gnav-link">@lang('nav.global.recent_posts')</a></li>
						
						<!-- Control Panel -->
						<li class="gnav-item item-panel"><a href="{!! url("cp") !!}" class="gnav-link">@lang('nav.global.panel')</a></li>
						
						@if (isset($user) && $user->canCreateBoard())
						<!-- Create a Board -->
						<li class="gnav-item item-newboard"><a href="{!! url("cp/boards/create") !!}" class="gnav-link">@lang('nav.global.new_board')</a></li>
						@endif
						
						@if (env('CONTRIB_ENABLED', false))
						<!-- Fundraiser Page -->
						<li class="gnav-item item-contribute"><a href="{!! url("contribute") !!}" class="gnav-link">@lang('nav.global.contribute')</a></li>
						
						<!-- Donation Page -->
						<li class="gnav-item item-donate"><a href="{!! secure_url("cp/donate") !!}" class="gnav-link">@lang('nav.global.donate')</a></li>
						@endif
						
						@if (isset($c) && $c->option('adventureEnabled'))
						<!-- Adventure! -->
						<li class="gnav-item item-adventure"><a href="{!! url("cp/adventure") !!}" class="gnav-link" data-no-instant>@lang('nav.global.adventure')</a></li>
						@endif
					</ul>
				</li>
			</ul>
	</div>
</nav>

@include('nav.boardlist')