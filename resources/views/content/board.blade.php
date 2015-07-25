@extends('layouts.main.board')

@section('content')
<main class="board-index index-threaded @if (isset($page)) page-{{ $page }} @endif">
	<section class="index-form">
		@include( $c->template('board.post.form'), [
			'board'   => $board,
			'actions' => [ $reply_to ? "reply" : "thread" ],
		])
	</section>
	
	@include('nav.board.pages', [
		'showCatalog' => true,
		'showIndex'   => !!$reply_to,
		'showPages'   => false,
	])
	
	<section class="index-threads">
		@include( 'widgets.ads.board_top_left' )
		
		<ul class="thread-list">
			@foreach ($posts as $thread)
			<li class="thread-item">
				<article class="thread">
					@include($c->template('board.thread'), [
						'board'   => $board,
						'thread'  => $thread,
						'op'      => $thread,
					])
				</article>
			</li>
			@endforeach
		</ul>
		
		@include('widgets.ads.board_bottom_right')
	</section>
</main>
@stop

@section('footer-inner')
	@include('nav.board.pages', [
		'showCatalog' => true,
		'showIndex'   => !!$reply_to,
		'showPages'   => true,
	])
@stop