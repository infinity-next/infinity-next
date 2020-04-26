@extends('layouts.main.board')

@section('body-class')@parent {{ $reply_to ? 'single-thread' : 'board-index' }}@endsection

@section('content')
<main class="board-index index-threaded mode-{{ $reply_to ? "reply" : "index" }} {{ isset($page) ? "page-{$page}" : '' }} {{ $board->isWorksafe() ? 'sfw' : 'nsfw' }}">
    @can($reply_to ? 'reply' : 'post', $reply_to ? $reply_to : $board)
    <section class="index-form">
        @include('content.board.post.form', [
            'board'   => $board,
            'actions' => [ $reply_to ? "reply" : "thread" ],
        ])
    </section>
    @endcan

    @include('nav.board.pages', [
        'showCatalog' => true,
        'showIndex'   => !!$reply_to,
        'showPages'   => false,
        'header'      => true,
    ])

    <section class="index-threads">
        @include( 'widgets.ads.board_top_left' )

        <div class="threads">
            @foreach ($posts as $thread)
            <article class="thread">
                @include('content.board.thread', [
                    'board'   => $board,
                    'thread'  => $thread,
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
        'showCatalog' => true,
        'showIndex'   => !!$reply_to,
        'showPages'   => true,
        'header'      => false,
    ])
@stop
