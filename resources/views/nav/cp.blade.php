<nav class="cp-top">
	<section class="cp-linklists">
		@if ($user)
		<ul class="cp-linkgroups">
			<li class="cp-linkgroup">
				<a class="linkgroup-name linkgroup-home" href="{!! url('cp') !!}">Home</a>
			</li>
		</ul>
		@endif
		
		<ul class="cp-linkgroups linkgroups-user">
			@if ($user)
			<li class="cp-linkgroup">
				Signed in as {!! $user->username !!}
			</li>
			<li class="cp-linkgroup">
				<a class="linkgroup-name" href="{!! url('cp/auth/logout') !!}">Logout</a>
			</li>
			@else
			<li class="cp-linkgroup">
				<a class="linkgroup-name" href="{!! url('cp/auth/register') !!}">Register</a>
			</li>
			<li class="cp-linkgroup">
				<a class="linkgroup-name" href="{!! url('cp/auth/login') !!}">Login</a>
			</li>
			@endif
		</ul>
	</section>
</nav>