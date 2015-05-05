@extends('layouts.main')

@section('title', "{$board->title}")
@section('description', $board->description)

@section('content')
<main class="board-index page-1">
	<section class="index-form">
		<aside class="advertisement left"></aside>
		
		@include('content.forms.post', [ 'board' => $board ])
		
		<aside class="advertisement right"></aside>
	</section>
	
	<section class="index-threads static">
		
		<ul class="thread-list">
			@foreach ($threads as $thread)
			<li class="thread-item">
				<article class="thread">
					@include('content.post', [ 'board' => $board, 'thread' => $thread, 'posts' => $posts ])
				</article>
			</li>
			@endforeach
		</ul>
	</section>
</main>
@stop