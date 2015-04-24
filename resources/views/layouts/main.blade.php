<!DOCTYPE html>
<html class="no-js">
<head>
	<title>@yield('title') - Larachan</title>
	
	<link rel="stylesheet" type="text/css" href="/css/font-awesome.css" />
	<link rel="stylesheet" type="text/css" href="/css/boilerplate.css" />
	<link rel="stylesheet" type="text/css" href="/css/grid-responsive.css" />
	<link rel="stylesheet" type="text/css" href="/css/main.css" />
</head>
<body class="larachan">
	<header class="board-header">
		@include('widgets.boardlist')
		
		<figure class="page-head">
				<img id="logo" src="/img/logo.png" alt="Larachan" />
			
			<figcaption class="page-details">
				<h1 class="page-title">@yield('title')</h1>
				<h2 class="page-desc">@yield('description')</h2>
			</figcaption>
		</figure>
		
		@include('widgets.announcement')
	</header>

	@yield('content')
	
	<footer>
		@include('widgets.boardlist')
		
		<section id="footnotes">
			<div>Larachan &copy; Larachan Development Group 2015</div>
		</section>
	</footer>
</body>
</html>
