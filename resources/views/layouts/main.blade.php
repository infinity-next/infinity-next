<!DOCTYPE html>
<html class="no-js">
<head>
	<title data-original="@yield('title', 'Infinity Next')">@yield('title', 'Infinity Next')</title>
	<link rel="shortcut icon" href="{{ asset('static/img/assets/Favicon_Yotsuba.ico') }}"
		data-normal="{{ asset('static/img/assets/Favicon_Yotsuba.ico') }}" data-alert="{{ asset('static/img/assets/Favicon_Yotsuba_new.ico') }}" />
	
	@section('css')
		{!! Minify::stylesheetDir('/static/vendor/')->withFullUrl() !!}
		{!! Minify::stylesheetDir('/static/css/app/')->withFullUrl() !!}
		
		@section('page-css')
			<link id="page-stylesheet" rel="stylesheet" data-instant-track />
		@show
	@show
	
	@section('js')
		@yield('required-js')
		
		<script type="text/javascript">
			document.getElementsByTagName('html')[0].class = "js";
			
			window.app = {
				'lang'     : {!! json_encode( Lang::parseKey('board') ) !!},
				
				'merchant' : "{{ env('CASHIER_SERVICE') }}",
				
			@if (env('APP_DEBUG'))
				'debug'      : true,
				
				@if (env('STRIPE_LIVE_PUBLIC') && env('CASHIER_SERVICE') == "stripe")
				'stripe_key' : "{!! env('STRIPE_TEST_PUBLIC', '') !!}",
				@endif
				
				@if (env('CASHIER_SERVICE') == "braintree" && isset($BraintreeClientKey))
				'braintree_key' : "{!! $BraintreeClientKey !!}",
				@endif
				
			@else
				'debug'      : false,
				
				@if (env('STRIPE_LIVE_PUBLIC') && env('CASHIER_SERVICE') == "stripe")
				'stripe_key' : "{!! env('STRIPE_LIVE_PUBLIC', '') !!}",
				@endif
				
				@if (env('CASHIER_SERVICE') == "braintree" && isset($BraintreeClientKey))
				'braintree_key' : "{!! $BraintreeClientKey !!}",
				@endif
			@endif
				
				'url'        : "{!! env('APP_URL', 'false') !!}",
				
				@yield('app-js')
				
				'settings'   : {!! $app['settings']->getJson() !!},
				
				'version'    : 0
			};
		</script>
		
		{!! Minify::javascriptDir('/static/vendor/', ['data-no-instant'])->withFullUrl() !!}
		{!! Minify::javascriptDir('/static/js/plugins/', ['data-no-instant'])->withFullUrl() !!}
		{!! Minify::javascriptDir('/static/js/app/', ['data-no-instant'])->withFullUrl() !!}
	@show
	
	@section('meta')
		<meta name="viewport" content="width=device-width" />
	@show
	
	@yield('head')
</head>

<body class="infinity-next responsive @yield('body-class')">
	<div id="page-container">
		@section('header')
		<header class="board-header header-height-1">
			@section('nav-header')
				@include('nav.gnav')
			@show
			
			@section('header-inner')
				<figure class="page-head">
					<img id="logo" src="@yield('header-logo', asset('static/img/logo.png'))" alt="Infinity" />
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
	
	@section('footer')
	<footer>
		@yield('footer-inner')
		
		@section('nav-footer')
			@include('nav.boardlist')
		@show
		
		<script type="text/javascript" data-no-instant>
			InstantClick.init(50);
		</script>
		
		<section id="footnotes">
			<!-- Infinity Next is licensed under AGPL 3.0 and any modifications to this software must link to its source code which can be downloaded in a traditional format, such as a repository. -->
			<div class="copyright"><a class="agpl-compliance" href="https://github.com/infinity-next/infinity-next">Infinity Next</a> &copy; <a class="agpl-compliance" href="https://infinitydev.org">Infinity Next Development Group</a> 2015</div>
		</section>
	</footer>
	@show
</body>
</html>
