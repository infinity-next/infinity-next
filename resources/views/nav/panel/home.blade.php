<nav class="cp-side">
	<section class="cp-linklists">
		<ul class="cp-linkgroups">
			@if ($user)
			<li class="cp-linkgroup">
				<a class="linkgroup-name">@lang('panel.nav.secondary.home.account')</a>
				
				<ul class="cp-linkitems">
					<li class="cp-linkitem">
						<a class="linkitem-name" href="{!! url('cp/password') !!}">@lang('panel.nav.secondary.home.password_change')</a>
					</li>
				</ul>
			</li>
			@endif
			
			@if (env('CONTRIB_ENABLED', false))
			<li class="cp-linkgroup">
				<a class="linkgroup-name">@lang('panel.nav.secondary.home.sponsorship')</a>
				
				<ul class="cp-linkitems">
					<li class="cp-linkitem">
						<a class="linkitem-name" href="{!! secure_url('cp/donate') !!}">@lang('panel.nav.secondary.home.donate')</a>
					</li>
				</ul>
			</li>
			@endif
		</ul>
	</section>
</nav>