<nav class="cp-side">
	<section class="cp-linklists">
		<ul class="cp-linkgroups">
			<li class="cp-linkgroup">
				<a class="linkgroup-name">@lang('panel.nav.secondary.board.boards')</a>
				
				<ul class="cp-linkitems">
					<li class="cp-linkitem">
						<a class="linkitem-name" href="{!! url('cp/boards/create') !!}">@lang('panel.nav.secondary.board.create')</a>
					</li>
					
					@if ($user->canEditConfig())
					<li class="cp-linkitem">
						<a class="linkitem-name" href="{!! url('cp/boards') !!}">@lang('panel.nav.secondary.board.config')</a>
					</li>
					@endif
				</ul>
			</li>
		</ul>
	</section>
</nav>