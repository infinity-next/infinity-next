<!DOCTYPE html>
<html class="no-js">
<head>
	<title>@yield('title', 'Infinity')</title>
	
	@section('css')
		<link rel="stylesheet" type="text/css" href="/css/font-awesome.css" />
		<link rel="stylesheet" type="text/css" href="/css/boilerplate.css" />
		<link rel="stylesheet" type="text/css" href="/css/grid-responsive.css" />
		<link rel="stylesheet" type="text/css" href="/css/main.css?1431077365" />
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
		
		<script type="text/javascript" src="/js/vendor/jquery-2.1.3.min.js"></script>
		<script type="text/javascript" src="/js/vendor/modernizr.custom.81355.js"></script>
		<script type="text/javascript" src="/js/plugins/jquery.creditCardValidator.js"></script>
		<script type="text/javascript" src="/js/plugins/jquery.blockUI.js"></script>
		<script type="text/javascript" src="/js/main.js?1431077365"></script>
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
