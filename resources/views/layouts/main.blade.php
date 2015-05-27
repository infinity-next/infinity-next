<!DOCTYPE html>
<html class="no-js">
<head>
	<title>@yield('title', 'Infinity')</title>
	
	@section('css')
		{!! Minify::stylesheetDir('/css/') !!}
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
		
		{!! Minify::javascriptDir('/js/') !!}
	@show
	
	@yield('head')
</head>
<body class="larachan">
	<header class="board-header">
		@include('nav.boardlist')
		
		@section('header-inner')
			<figure class="page-head">
					<img id="logo" src="/img/logo.png" alt="Infinity" />
				
				<figcaption class="page-details">
					<h1 class="page-title">@yield('title')</h1>
					<h2 class="page-desc">@yield('description')</h2>
				</figcaption>
			</figure>
			
			@include('widgets.announcement')
		@show
	</header>
	
	@yield('content')
	
	<footer>
		@yield('footer')
		
		@include('nav.boardlist')
		
		<section id="footnotes">
			<div>Infinity Next &copy; Infinity Next Development Group 2015</div>
		</section>
	</footer>
</body>
</html>
