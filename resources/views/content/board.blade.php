@extends('layouts.main.board')

@section('body-class')@parent {{ $reply_to ? 'single-thread' : 'board-index' }}@endsection

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
	<button class="post-form-open">@lang($reply_to
		? 'board.button.reply'
		: 'board.button.thread'
	)</button>

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

	@include('content.board.sidebar')

	<button class="post-form-open">@lang($reply_to
		? 'board.button.reply'
		: 'board.button.thread'
	)</button>
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
