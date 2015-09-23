<nav class="cp-side">
	<section class="cp-linklists">
		<ul class="cp-linkgroups">
			<li class="cp-linkgroup">
				<ul class="cp-linkitems">
					<li class="cp-linkitem">
						<a class="linkitem-name linkitem-name-createboard" href="{!! url('cp/boards/create') !!}">@lang('panel.nav.secondary.board.create')</a>
					</li>
				</ul>
			</li>
			
			<li class="cp-linkgroup">
				<a class="linkgroup-name">@lang('panel.nav.secondary.board.boards')</a>
				
				<ul class="cp-linkitems">
					@if ($user->canEditConfig())
					<li class="cp-linkitem">
						<a class="linkitem-name" href="{!! url('cp/boards/assets') !!}">@lang('panel.nav.secondary.board.assets')</a>
					</li>
					
					<li class="cp-linkitem">
						<a class="linkitem-name" href="{!! url('cp/boards/config') !!}">@lang('panel.nav.secondary.board.config')</a>
					</li>
					
					<li class="cp-linkitem">
						<a class="linkitem-name" href="{!! url('cp/boards/staff') !!}">@lang('panel.nav.secondary.board.staff')</a>
					</li>
					@endif
				</ul>
			</li>
			
			<li class="cp-linkgroup">
				<a class="linkgroup-name">@lang('panel.nav.secondary.board.discipline')</a>
				
				<ul class="cp-linkitems">
					<li class="cp-linkitem">
						<a class="linkitem-name" href="{!! url('cp/boards/reports') !!}">@lang('panel.nav.secondary.board.reports')</a>
					</li>
				</ul>
			</li>
		</ul>
	</section>
</nav>