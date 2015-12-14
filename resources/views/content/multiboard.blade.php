@extends('layouts.main')

@section('content')
<main class="multiboard-index index-threaded">
	
	<section class="index-threads">
		@include( 'widgets.ads.board_top_left' )
		
		<ul class="thread-list">
			@if (isset($threads) && !is_null($threads))
			@foreach ($threads as $thread)
			<li class="thread-item">
				<article class="thread">
					@include('content.board.thread', [
						'board'      => $thread->board,
						'thread'     => isset($thread->op) ? $thread->op : $thread,
						'multiboard' => isset($multiboard) ? !!$multiboard : true,
					])
				</article>
			</li>
			@endforeach
			@elseif (isset($posts) && !is_null($posts))
			@foreach ($posts as $post)
			<li class="thread-item">
				<article class="thread">
					@include('content.board.post', [
						'board'      => $post->board,
						'post'       => $post,
						'multiboard' => isset($multiboard) ? !!$multiboard : true,
					])
				</article>
			</li>
			@endforeach
			@endif
		</ul>
		
	</section>
	
	@include('content.board.sidebar')
</main>
@stop
