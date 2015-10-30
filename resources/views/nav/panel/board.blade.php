<nav class="cp-side">
	<section class="cp-linklists">
		<ul class="cp-linkgroups">
			@if ($user->canCreateBoard())
			<li class="cp-linkgroup">
				<ul class="cp-linkitems">
					<li class="cp-linkitem">
						<a class="linkitem-name linkitem-name-createboard" href="{!! url('cp/boards/create') !!}">@lang('nav.panel.secondary.board.create')</a>
					</li>
				</ul>
			</li>
			@endif
			
			@if ($user->canEditAnyConfig())
			<li class="cp-linkgroup">
				<a class="linkgroup-name">@lang('nav.panel.secondary.board.boards')</a>
				
				<ul class="cp-linkitems">
					<li class="cp-linkitem">
						<a class="linkitem-name" href="{!! url('cp/boards/assets') !!}">@lang('nav.panel.secondary.board.assets')</a>
					</li>
					
					<li class="cp-linkitem">
						<a class="linkitem-name" href="{!! url('cp/boards/config') !!}">@lang('nav.panel.secondary.board.config')</a>
					</li>
					
					<li class="cp-linkitem">
						<a class="linkitem-name" href="{!! url('cp/boards/staff') !!}">@lang('nav.panel.secondary.board.staff')</a>
					</li>
				</ul>
			</li>
			@endif
			
			@if ($user->canViewReports() || $user->canViewReportsGlobally())
			<li class="cp-linkgroup">
				<a class="linkgroup-name">@lang('nav.panel.secondary.board.discipline')</a>
				
				<ul class="cp-linkitems">
					<li class="cp-linkitem">
						<a class="linkitem-name" href="{!! url('cp/boards/appeals') !!}">@lang('nav.panel.secondary.board.appeals')</a>
					</li>
					
					<li class="cp-linkitem">
						<a class="linkitem-name" href="{!! url('cp/boards/reports') !!}">@lang('nav.panel.secondary.board.reports')</a>
					</li>
				</ul>
			</li>
			@endif
		</ul>
	</section>
</nav>