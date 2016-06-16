@extends('layouts.main')

@section('title', "{$board->title}")
@section('description', $board->description)

@section('content')
<main class="post-moderation">
    <section class="moderate-post grid-container">

        @if (isset($actions))
            @include( "content.board.post.mod.{$form}", [
                'actions'  => $actions,
                'board'    => $board,
                'post'     => $post,
                'reply_to' => false,
            ])
        @endif

        <article class="moderated-content">
            @include( 'content.board.post', [
                'board'      => $board,
                'post'       => $post,
                'multiboard' => false,
                'preview'    => false,
                'catalog'    => false,
            ])
        </article>
    </section>
</main>
@stop
