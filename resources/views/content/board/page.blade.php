@extends('layouts.main')

@section('title', $page->name)

@section('content')
<main class="page grid-container" id="page-{{ $page->page_id }}">
    <section class="board-page grid-100">
        <div class="smooth-box">
            {{ $page }}
        </div>
    </section>
</main>
@stop
