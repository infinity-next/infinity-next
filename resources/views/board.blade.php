@extends('layouts.main')

@section('content')
<header class="board-header">
	@include('widgets.boardlist')
	
	<figure class="board-head">
		<img class="board-banner" src="/img/logo.png" />
		
		<figcaption class="board-details">
			<h1 class="board-title">{{{ $board->title }}}</h1>
			<h2 class="board-desc">{{{ $board->description }}}</h2>
		</figcaption>
	</figure>
	
	<aside class="announcement"></aside>
</header>

<main class="board-index page-1">
	<section class="index-form">
		<aside class="advertisement left"></aside>
		
		@include('content.forms.post', [ 'board' => $board ])
		
		<aside class="advertisement right"></aside>
	</section>
	
	<section class="index-threads">
		
		<ul class="thread-list">
			@foreach ($threads as $thread)
			<li class="thread-item">
				<article class="thread">
					@include('content.post', [ 'board' => $board, 'thread' => $thread, 'posts' => $posts ])
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
	
	@include('widgets.boardlist')
	
	<section id="footnotes">
		<div>Larachan &copy; Larachan Development Group 2015</div>
	</section>
</footer>
@stop