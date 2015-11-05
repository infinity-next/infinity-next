@extends('layouts.main.board')

@section('content')
<main class="board-index index-threaded mode-{{ $reply_to ? "reply" : "index" }} @if (isset($page)) page-{{ $page }} @endif">
	
	<section class="index-form">
		@include('content.board.post.form', [
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
					<div class="thread-interior">
						@include('content.board.thread', [
							'board'   => $board,
							'thread'  => $thread,
							'updater' => !!$reply_to,
						])
					</div>
				</article>
			</li>
			@endforeach
		</ul>
		
	</section>
</main>

@include( 'widgets.ads.board_bottom_right' )
@stop

@section('footer-inner')
	@include('nav.board.pages', [
		'showCatalog' => true,
		'showIndex'   => !!$reply_to,
		'showPages'   => true,
	])
@stop