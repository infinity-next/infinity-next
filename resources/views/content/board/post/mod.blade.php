@extends('layouts.main')

@section('title', "{$board->title}")
@section('description', $board->description)

@section('content')
<main class="post-moderation">
	<section class="moderate-post grid-container">
		
		@if (isset($actions))
			@include( $c->template("board.post.mod.{$form}"), [ 'actions' => $actions, 'board' => $board, 'post' => $post ])
		@endif
		
		<article class="moderated-content">
			<div class="post-container">
				@include( $c->template('board.post.single'), [ 'board' => $board, 'post' => $post ])
			</div>
		</article>
	</section>
</main>
@stop