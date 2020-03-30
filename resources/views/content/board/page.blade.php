@extends('layouts.main')

@section('title', $page->name)
@section('description', $board ? "This page is maintained by the owners of /{$page->board_uri}/." : "This page is maintained by the site administration.")

@section('content')
<main class="page grid-container" id="page-{{ $page->page_id }}">
    <section class="board-page grid-100">
        <div class="smooth-box">
            {{ $page }}
        </div>
    </section>
</main>
@stop
