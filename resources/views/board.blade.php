<!DOCTYPE html>
<html class="no-js">
<head>
	<title>Larachan</title>
	
	<link rel="stylesheet" type="text/css" href="/css/boilerplate.css" />
	<link rel="stylesheet" type="text/css" href="/css/grid-responsive.css" />
	<link rel="stylesheet" type="text/css" href="/css/style.css" />
</head>
<body class="larachan module-board view-index">
	<header class="board-header">
		<nav class="boardlist top">
			<ul class="boardlist-categories">
				<li class="boardlist-category">
					<ul class="boardlist-items">
						<li class="boardlist-item"><a href="#" class="boardlist-link">larachan</a></li>
						<li class="boardlist-item"><a href="#" class="boardlist-link">meta</a></li>
						<li class="boardlist-item"><a href="#" class="boardlist-link">b</a></li>
						<li class="boardlist-item"><a href="#" class="boardlist-link">isis</a></li>
					</ul>
				</li>
			</ul>
		</nav>
		
		<figure class="board-head">
			<img class="board-banner" src="/img/logo.png" />
			
			<figcaption class="board-details">
				<h1 class="board-title">{{{ $board->title }}}</h1>
				<h2 class="board-description">{{{ $board->description }}}</h2>
			</figcaption>
		</figure>
		
		<aside class="announcement"></aside>
	</header>
	
	<main class="board-index page-1">
		<section class="index-form">
			<aside class="advertisement left"></aside>
			
			<form class="form-post">
				
			</form>
			
			<aside class="advertisement right"></aside>
		</section>
		
		<section class="index-threads">
			
			<ul class="thread-list">
				@foreach ($threads as $thread)
				<li class="thread-item">
					<article class="thread">
						@include('content.post', [ 'thread' => $thread, 'posts' => $posts ])
					</article>
				</li>
				@endforeach
			</ul>
		</section>
	</main>
	
	<footer class="board-footer">
		<!--<nav class="board-pages">
			<a class="board-page-prev" href="#">Previous</a>
			
			<ul class="board-page-list">
				<li class="board-page">
					<a class="page-link">1</a>
				</li>
			</ul>
		</nav>-->
		
		<nav class="boardlist bottom">
			<ul class="boardlist-categories">
				<li class="boardlist-category">
					<ul class="boardlist-items">
						<li class="boardlist-item"><a href="#" class="boardlist-link">larachan</a></li>
						<li class="boardlist-item"><a href="#" class="boardlist-link">meta</a></li>
						<li class="boardlist-item"><a href="#" class="boardlist-link">b</a></li>
						<li class="boardlist-item"><a href="#" class="boardlist-link">isis</a></li>
					</ul>
				</li>
			</ul>
		</nav>
		
		<section id="footnotes">
			Larachan &copy; Larachan Development Group 2015
		</section>
	</footer>
</body>
</html>
