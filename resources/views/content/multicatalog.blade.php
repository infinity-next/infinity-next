@extends('layouts.main')

@section('content')
<main class="multiboard-index index-catalog">

    <section class="index-threads static">

        <ul class="thread-list">
            @foreach ($threads as $thread)
            <li class="thread-item">
                <article class="thread">
                    @include($c->template('board.catalog'), [
                        'board'      => $board,
                        'post'       => $thread,
                        'multiboard' => false,
                        'preview'    => false,
                    ])
                </article>
            </li>
            @endforeach
        </ul>
    </section>

    @include('content.board.sidebar')
</main>
@stop
