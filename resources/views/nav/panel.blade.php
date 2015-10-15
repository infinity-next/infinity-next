<nav class="cp-top">
	<section class="cp-linklists">
		@if (!$user->isAnonymous())
		<ul class="cp-linkgroups">
			<li class="cp-linkgroup">
				<a class="linkgroup-name linkgroup-home" href="{!! url('cp') !!}">@lang('nav.panel.primary.home')</a>
			</li>
			
			@if ($user->canCreateBoard() || $user->canEditAnyConfig())
			<li class="cp-linkgroup">
				<a class="linkgroup-name linkgroup-boards" href="{!! url('cp/boards') !!}">@lang('nav.panel.primary.board')</a>
			</li>
			@endif
			
			@if ($user->canAdminConfig())
			<li class="cp-linkgroup">
				<a class="linkgroup-name linkgroup-site" href="{!! url('cp/site') !!}">@lang('nav.panel.primary.site')</a>
			</li>
			@endif
			
			@if ($user->canAdminUsers() || $user->canAdminPermissions())
			<li class="cp-linkgroup">
				<a class="linkgroup-name linkgroup-users" href="{!! url('cp/users') !!}">@lang('nav.panel.primary.users')</a>
			</li>
			@endif
		</ul>
		@endif
		
		<ul class="cp-linkgroups linkgroups-user" data-no-instant>
			@if (!$user->isAnonymous())
			<li class="cp-linkgroup">
				@lang('panel.authed_as', [ 'name' => $user->username ])
			</li>
			<li class="cp-linkgroup">
				<a class="linkgroup-name" href="{!! url('cp/auth/logout') !!}">@lang('nav.panel.primary.logout')</a>
			</li>
			@else
			<li class="cp-linkgroup">
				<a class="linkgroup-name" href="{!! url('cp/auth/register') !!}">@lang('nav.panel.primary.register')</a>
			</li>
			<li class="cp-linkgroup">
				<a class="linkgroup-name" href="{!! url('cp/auth/login') !!}">@lang('nav.panel.primary.login')</a>
			</li>
			@endif
		</ul>
	</section>
</nav>