<!DOCTYPE html>
<html class="no-js" data-widget="instantclick">
<head>
	<title data-original="@yield('title') - {{ site_setting('siteName') }}">@ifhas('title')@yield('title') - @endif{{ site_setting('siteName') }}</title>
	<link rel="shortcut icon" id="favicon" href="{{ asset('static/img/assets/Favicon_Vivian.ico') }}"
		data-normal="{{ asset('static/img/assets/Favicon_Vivian.ico') }}" data-alert="{{ asset('static/img/assets/Favicon_Vivian_new.ico') }}" />
	
	@section('css')
		{!! Minify::stylesheetDir('/static/vendor/', ['data-no-instant'])->withFullUrl() !!}
		{!! Minify::stylesheetDir('/static/css/app/', ['data-no-instant'])->withFullUrl() !!}
		
		@section('page-css')
			<link id="page-stylesheet" rel="stylesheet" data-instant-track />
		@show
		
		<link id="theme-stylesheet" rel="stylesheet" data-instant-track />
		<style id="user-css" type="text/css"></style>
	@show
	
	@yield('js')
	
	@section('meta')
		<meta name="viewport" content="width=device-width" />
	@show
	
	@yield('head')
</head>

<body class="infinity-next responsive ltr @yield('body-class')" id="top">
	<div id="page-container">
		@section('header')
		<header class="board-header header-height-1">
			@section('nav-header')
				@include('nav.gnav')
			@show
			
			@section('header-inner')
				<figure class="page-head">
					@if (user()->isAccountable())
					<img id="logo" src="@yield('header-logo', asset('static/img/logo.png'))" alt="{{ site_setting('siteName') }}" />
					@else
					<img id="logo" src="@yield('header-logo', asset('static/img/logo_tor.png'))" alt="{{ site_setting('siteName') }}" />
					@endif
					<figcaption class="page-details">
						@if (!isset($hideTitles))
							@if (array_key_exists('page-title', View::getSections()))
							<h1 class="page-title">@yield('page-title')</h1>
							@else
							<h1 class="page-title">@yield('title')</h1>
							@endif
						<h2 class="page-desc">@yield('description')</h2>
						@endif
						
						@yield('header-details')
					</figcaption>
				</figure>
				
				@include('widgets.announcement')
			@show
		</header>
		@show
		
		@yield('content')
	</div>
	
	@yield('footer')
</body>
</html>
