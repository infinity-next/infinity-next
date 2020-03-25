<figure id="site-logo">
	@can('be-accountable')
	<img id="site-logo-img" src="@yield('header-logo', asset('static/img/logo.png'))" alt="{{ site_setting('siteName') }}" />
	@else
	<img id="site-logo-img" src="@yield('header-logo', asset('static/img/logo_tor.png'))" alt="{{ site_setting('siteName') }}" />
	@endcan
</figure>
