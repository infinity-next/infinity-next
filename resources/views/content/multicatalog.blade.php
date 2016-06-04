@extends('layouts.main')

@section('content')
<main class="multiboard-index index-catalog">
    <label class="overwatch js-only" data-widget="overwatch">
        <input type="checkbox" class="overwatch-toggle" />
        <span class="overwatch-label label-enter">Enter Overwatch</span>
        <span class="overwatch-label label-pause">OVERWATCHING</span>
        <div class="overwatch-label label-reading"><tt>Waiting for you to finish reading ...</tt></div>
    </label>

    <section class="index-threads static">
        <ul class="thread-list" id="CatalogMix">
            @foreach ($threads as $thread)
            <li class="thread-item mix board-{{ $thread->board_uri }}" data-id="{{ $thread->post_id }}" data-bumped="{{ $thread->bumped_last->timestamp }}">
                <article class="thread">
                    @include('content.board.catalog', [
                        'board'      => $board,
                        'post'       => $thread,
                        'multiboard' => true,
                        'preview'    => true,
                    ])
                </article>
            </li>
            @endforeach
        </ul>
    </section>

    @include('content.board.sidebar')
</main>
@stop
