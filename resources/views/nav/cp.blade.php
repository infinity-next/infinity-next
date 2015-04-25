<nav class="cp-top">
	<section class="cp-linklists">
		<ul class="cp-linkgroups">
			<li class="cp-linkgroup">
				<a class="linkgroup-name linkgroup-home" href="{!! url('cp') !!}">Home</a>
			</li>
		</ul>
		
		<ul class="cp-linkgroups linkgroups-user">
			<li class="cp-linkgroup">
				Signed in as {!! Auth::user()->username !!}
			</li>
			<li class="cp-linkgroup">
				<a class="linkgroup-name" href="{!! url('cp/auth/logout') !!}">Logout</a>
			</li>
		</ul>
	</section>
</nav>