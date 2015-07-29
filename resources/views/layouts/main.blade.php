<!DOCTYPE html>
<html class="no-js">
<head>
	<title>@yield('title', 'Infinity')</title>
	
	@section('css')
		{!! Minify::stylesheetDir('/vendor/')->withFullUrl() !!}
		{!! Minify::stylesheetDir('/css/app/')->withFullUrl() !!}
	@show
	
	@section('js')
		@yield('required-js')
		
		<script type="text/javascript">
			window.app = {
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
				
				'url'        : "{!! env('APP_URL', 'false') !!}"
			};
		</script>
		
		{!! Minify::javascriptDir('/vendor/')->withFullUrl() !!}
		{!! Minify::javascriptDir('/js/plugins/')->withFullUrl() !!}
		{!! Minify::javascriptDir('/js/app/')->withFullUrl() !!}
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
			<!-- Infinity Next is licensed under AGPL 3.0 and any modifications to this software must link to its source code which can be downloaded in a traditional format, such as a repository. -->
			<div class="copyright"><a class="agpl-compliance" href="https://github.com/infinity-next/infinity-next">Infinity Next</a> &copy; <a class="agpl-compliance" href="https://infinitydev.org">Infinity Next Development Group</a> 2015</div>
		</section>
	</footer>
	@show
</body>
</html>
