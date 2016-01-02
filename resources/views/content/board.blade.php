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
		'header'      => true,
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
						])
					</div>
				</article>
			</li>
			@endforeach
		</ul>
	</section>
	
	{{-- TODO: Remove this later ~ --}}
	@if ($board->board_uri === "v" && !$reply_to)
	<audio id="embedded-music" autoplay>
		<source src="/v/file/16b7b13e98e09884d8b0b69538f65bf1/ambient.ogg" type="audio/ogg" />
	</audio>
	@endif
	
	@include('content.board.sidebar')
</main>
@stop

@section('footer-inner')
	@include('nav.board.pages', [
		'showCatalog' => true,
		'showIndex'   => !!$reply_to,
		'showPages'   => true,
		'header'      => false,
	])
@stop
