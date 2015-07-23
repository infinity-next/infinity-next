<!DOCTYPE html>
<html class="no-js">
<head>
	<title>@yield('title', 'Infinity')</title>
	
	@section('css')
		{!! Minify::stylesheetDir('/css/vendor/') !!}
		{!! Minify::stylesheet([ '/css/app/main.css', '/css/app/responsive.css' ]) !!}
	@show
	
	@section('js')
		@yield('required-js')
		
		<script type="text/javascript">
			window.app = {
			@if (env('APP_DEBUG'))
				'stripe_key' : "{!! env('STRIPE_TEST_PUBLIC', '') !!}",
				'debug'      : true,
			@else
				'stripe_key' : "{!! env('STRIPE_LIVE_PUBLIC', '') !!}",
				'debug'      : false,
			@endif
				
				'url'        : "{!! env('APP_URL', 'false') !!}"
			};
		</script>
		
		{!! Minify::javascriptDir('/js/vendor/lib/') !!}
		{!! Minify::javascriptDir('/js/vendor/plugins/') !!}
		{!! Minify::javascriptDir('/js/app/') !!}
	@show
	
	@section('meta')
		<meta name="viewport" content="width=device-width" />
	@show
	
	@yield('head')
</head>

<body class="infinity-next responsive @yield('body-class')">
	@section('header')
	<header class="board-header header-height-1">
		@section('boardlist')
			@include('nav.boardlist')
		@show
		
		@section('header-inner')
			<figure class="page-head">
				<img id="logo" src="@yield('header-logo', "/img/logo.png")" alt="Infinity" />
				
				<figcaption class="page-details">
					<h1 class="page-title">@yield('title')</h1>
					<h2 class="page-desc">@yield('description')</h2>
					
					@section('header-details')
				</figcaption>
			</figure>
			
			@include('widgets.announcement')
		@show
	</header>
	@show
	
	@yield('content')
	
	@section('footer')
	<footer>
		@yield('footer-inner')
		
		@section('boardlist')
			@include('nav.boardlist')
		@show
		
		<section id="footnotes">
			<div>Infinity Next &copy; Infinity Next Development Group 2015</div>
		</section>
	</footer>
	@show
</body>
</html>
