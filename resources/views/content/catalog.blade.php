@extends('layouts.main.board')

@section('content')
<main class="board-index index-catalog">
	@include('nav.board.pages', [
		'showCatalog' => false,
		'showIndex'   => true,
		'showPages'   => false,
		'header'      => true,
	])
	
	<section class="index-threads static">
		<ul class="thread-list">
			@foreach ($posts as $thread)
			<li class="thread-item">
				<article class="thread">
					@include($c->template('board.catalog'), [
						'board'      => $board,
						'post'       => $thread,
						'multiboard' => false,
						'preview'    => false,
					])
				</article>
			</li>
			@endforeach
		</ul>
	</section>
	
	<section class="index-form">
		@include( $c->template('board.post.form'), [
			'board'   => $board,
			'actions' => [ $reply_to ? "reply" : "thread" ],
		])
	</section>
	
	@include('content.board.sidebar')
</main>
@stop

@section('footer-inner')
	@include('nav.board.pages', [
		'showCatalog' => false,
		'showIndex'   => true,
		'showPages'   => true,
		'header'      => false,
	])
@stop
