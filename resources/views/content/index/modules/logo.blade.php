<figure id="site-logo">
	@if ($user->isAccountable())
	<img id="site-logo-img" src="@yield('header-logo', asset('static/img/logo.png'))" alt="{{ site_setting('siteName') }}" />
	@else
	<img id="site-logo-img" src="@yield('header-logo', asset('static/img/logo_tor.png'))" alt="{{ site_setting('siteName') }}" />
	@endif
</figure>
