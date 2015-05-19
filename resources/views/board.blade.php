@extends('layouts.main')

@section('title', "{$board->title}")
@section('description', $board->description)

@section('content')
<main class="board-index page-1">
	<section class="index-form">
		@include('content.forms.post', [
			'board'   => $board,
			'actions' => [ $reply_to ? "reply" : "thread" ],
		])
	</section>
	
	<section class="index-threads static">
		@include('ads.board_top_left')
		
		<ul class="thread-list">
			@foreach ($posts as $thread)
			<li class="thread-item">
				<article class="thread">
					@include('content.thread', [
						'board'  => $board,
						'thread' => $thread,
						'op'     => $thread,
					])
				</article>
			</li>
			@endforeach
		</ul>
		
		@include('ads.board_bottom_right')
	</section>
</main>
@stop

@section('footer')
	@include('nav.boardpages')
@stop