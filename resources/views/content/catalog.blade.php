@extends('layouts.main.board')

@section('content')
<main class="board-index index-catalog catalog-flyout">
    @can('post', $board)
    <section class="index-form">
        @include('content.board.post.form', [
        'board'   => $board,
        'actions' => ["thread"],
        ])
    </section>
    @endcan

    @include('nav.board.pages', [
        'showCatalog' => false,
        'showIndex'   => true,
        'showPages'   => false,
        'header'      => true,
    ])

    <section class="index-threads static">
        <div class="threads">
            @foreach ($posts as $post)
            <article class="thread catalog-thread">
                @include('content.board.catalog', [
                    'board'      => $board,
                    'post'       => $post,
                    'multiboard' => false,
                    'preview'    => false,
                ])
            </article>
            @endforeach
        </div>
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
