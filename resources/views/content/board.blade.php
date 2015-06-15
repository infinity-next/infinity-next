@extends('layouts.main')

@section('title', "{$board->title} - /{$board->board_uri}/")
@section('description', $board->description)

@section('content')
<main class="board-index page-1">
	<section class="index-form">
		@include( $c->template('board.post.form'), [
			'board'   => $board,
			'actions' => [ $reply_to ? "reply" : "thread" ],
		])
	</section>
	
	<section class="index-threads static">
		@include( 'widgets.ads.board_top_left' )
		
		<ul class="thread-list">
			@foreach ($posts as $thread)
			<li class="thread-item">
				<article class="thread">
					@include($c->template('board.thread'), [
							'board'  => $board,
							'thread' => $thread,
							'op'     => $thread,
					])
				</article>
			</li>
			@endforeach
		</ul>
		
		@include('widgets.ads.board_bottom_right')
	</section>
</main>
@stop

@section('footer')
	@include('nav.board.pages')
@stop