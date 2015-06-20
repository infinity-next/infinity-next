<nav class="cp-top">
	<section class="cp-linklists">
		@if (!$user->isAnonymous())
		<ul class="cp-linkgroups">
			<li class="cp-linkgroup">
				<a class="linkgroup-name linkgroup-home" href="{!! url('cp') !!}">@lang('panel.nav.primary.home')</a>
			</li>
			
			@if ($user->canAny('board.config'))
			<li class="cp-linkgroup">
				<a class="linkgroup-name linkgroup-home" href="{!! url('cp/boards') !!}">@lang('panel.nav.primary.board')</a>
			</li>
			@endif
			
			@if ($user->can('site.config'))
			<li class="cp-linkgroup">
				<a class="linkgroup-name linkgroup-home" href="{!! url('cp/site') !!}">@lang('panel.nav.primary.site')</a>
			</li>
			
			<li class="cp-linkgroup">
				<a class="linkgroup-name linkgroup-home" href="{!! url('cp/users') !!}">@lang('panel.nav.primary.users')</a>
			</li>
			@endif
		</ul>
		@endif
		
		<ul class="cp-linkgroups linkgroups-user">
			@if (!$user->isAnonymous())
			<li class="cp-linkgroup">
				@lang('panel.authed_as', [ 'name' => $user->username ] )
			</li>
			<li class="cp-linkgroup">
				<a class="linkgroup-name" href="{!! url('cp/auth/logout') !!}">@lang('panel.field.logout')</a>
			</li>
			@else
			<li class="cp-linkgroup">
				<a class="linkgroup-name" href="{!! url('cp/auth/register') !!}">@lang('panel.field.register')</a>
			</li>
			<li class="cp-linkgroup">
				<a class="linkgroup-name" href="{!! url('cp/auth/login') !!}">@lang('panel.field.login')</a>
			</li>
			@endif
		</ul>
	</section>
</nav>