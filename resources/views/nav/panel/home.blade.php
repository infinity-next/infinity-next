<nav class="cp-side">
	<section class="cp-linklists">
		<ul class="cp-linkgroups">
			@if (!$user->isAnonymous())
			<li class="cp-linkgroup">
				<a class="linkgroup-name">@lang('nav.panel.secondary.home.account')</a>
				
				<ul class="cp-linkitems">
					<li class="cp-linkitem">
						<a class="linkitem-name" href="{!! url('cp/password') !!}">@lang('nav.panel.secondary.home.password_change')</a>
					</li>
				</ul>
			</li>
			@endif
			
			@if (env('CONTRIB_ENABLED', false))
			<li class="cp-linkgroup">
				<a class="linkgroup-name">@lang('nav.panel.secondary.home.sponsorship')</a>
				
				<ul class="cp-linkitems">
					<li class="cp-linkitem">
						<a class="linkitem-name" href="{!! secure_url('cp/donate') !!}">@lang('nav.panel.secondary.home.donate')</a>
					</li>
				</ul>
			</li>
			@endif
		</ul>
	</section>
</nav>