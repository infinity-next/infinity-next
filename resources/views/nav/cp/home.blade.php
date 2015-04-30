<nav class="cp-side">
	<section class="cp-linklists">
		<ul class="cp-linkgroups">
			@if ($user)
			<li class="cp-linkgroup">
				<a class="linkgroup-name">Account</a>
				
				<ul class="cp-linkitems">
					<li class="cp-linkitem">
						<a class="linkitem-name" href="{!! url('cp/password') !!}">Change Password</a>
					</li>
				</ul>
			</li>
			@endif
			
			<li class="cp-linkgroup">
				<a class="linkgroup-name">Sponsorship</a>
				
				<ul class="cp-linkitems">
					<li class="cp-linkitem">
						<a class="linkitem-name" href="{!! url('cp/donate') !!}">Donate</a>
					</li>
				</ul>
			</li>
		</ul>
	</section>
</nav>