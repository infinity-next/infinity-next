@extends('layouts.main')
@section('title', trans("board.overboard"))

@section('content')
<main class="multiboard-index index-threaded">
	
	<section class="index-threads">
		@include( 'widgets.ads.board_top_left' )
		
		<ul class="thread-list">
			@foreach ($threads as $thread)
			<li class="thread-item">
				<article class="thread">
					@include('content.board.thread', [
						'board'      => $thread->board,
						'thread'     => $thread->op ?: $thread,
						'multiboard' => isset($multiboard) ? !!$multiboard : true,
					])
				</article>
			</li>
			@endforeach
		</ul>
		
	</section>
</main>

@include( 'widgets.ads.board_bottom_right' )
@stop