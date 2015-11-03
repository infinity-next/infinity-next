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
						'multiboard' => true,
					])
				</article>
			</li>
			@endforeach
		</ul>
		
	</section>
</main>

@include( 'widgets.ads.board_bottom_right' )
@stop