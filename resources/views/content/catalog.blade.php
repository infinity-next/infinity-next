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
            @foreach ($posts as $post)
            <li class="thread-item">
                <article class="thread">
                    @include('content.board.catalog'), [
                        'board'      => $board,
                        'post'       => $post,
                        'multiboard' => false,
                        'preview'    => false,
                    ])
                </article>
            </li>
            @endforeach
        </ul>
    </section>

    @can('create', App\Post::class)
    <section class="index-form">
        @include('content.board.post.form', [
            'board'   => $board,
            'actions' => "thread",
        ])
    </section>
    @endcan

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
