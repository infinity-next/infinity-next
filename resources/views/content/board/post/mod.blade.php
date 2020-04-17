@extends('layouts.main')

@section('title', "{$board->title}")
@section('description', $board->description)

@section('content')
<main class="post-moderation">
    <section class="moderate-post grid-container">
        {!! Form::open([
            'url'    => Request::url(),
            'method' => "POST",
            'files'  => true,
            'id'     => "mod-form",
            'class'  => "form-mod smooth-box",
        ]) !!}
            @include('widgets.messages')
            @include("content.board.post.mod.{$form}")
        {!! Form::close() !!}
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
