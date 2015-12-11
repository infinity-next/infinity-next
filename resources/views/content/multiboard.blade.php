@extends('layouts.main')

@section('content')
<main class="multiboard-index index-threaded">
	
	<section class="index-threads">
		@include( 'widgets.ads.board_top_left' )
		
		<ul class="thread-list">
			@foreach ($posts as $post)
			<li class="thread-item">
				<article class="thread">
					@include('content.board.post', [
						'board'      => $post->board,
						'thread'     => $post->op ?: $post,
					])
				</article>
			</li>
			@endforeach
		</ul>
		
		@include('nav.paginator', [
			'paginator' => $posts,
		])
		
	</section>
	
	@include('content.board.sidebar')
</main>
@stop
