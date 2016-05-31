@extends('layouts.main')

@section('content')
<main class="multiboard-index index-threaded">

    <section class="index-threads">
        @include( 'widgets.ads.board_top_left' )

        <ul class="thread-list">
            @if (isset($threads) && !is_null($threads))
            {{-- Multiboard for whole threads (overboard) --}}
            @foreach ($threads as $thread)
            <li class="thread-item">
                <article class="thread">
                    <div class="thread-interior">
                        @include('content.board.thread', [
                            'board'      => $thread->board,
                            'thread'     => $thread,
                            'multiboard' => isset($multiboard) ? !!$multiboard : true,
                        ])
                    </div>
                </article>
            </li>
            @endforeach
            @elseif (isset($posts) && !is_null($posts))
            {{-- Multiboard for individual messages (history) --}}
            @foreach ($posts as $post)
            <li class="thread-item">
                <article class="thread">
                    @include('content.board.post', [
                        'board'      => $post->board,
                        'post'       => $post,
                        'multiboard' => isset($multiboard) ? !!$multiboard : true,
                    ])
                </article>
            </li>
            @endforeach
            @endif
        </ul>
    </section>

    @include('content.board.sidebar')
</main>
@stop
