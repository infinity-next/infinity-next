@extends('layouts.main')

@section('meta')
    @parent

    @if ($board)
    <meta http-equiv="refresh" content="1;url={{ $board->getUrl() }}" />
    @endif
@stop

@section('content')
<main id="adventure">
    <section class="adventure-flair">
        <figure class="adventure-figure">
            @if ($board)
            <a class="adventure-link" href="{{ $board->getUrl() }}">
                <i class="fas fa-rocket"></i>
                <figcaption class="adventure-caption">
                    @lang('panel.adventure.go')
                </figcaption>
            </a>
            @else
            <span class="adventure-sad">@lang('panel.adventure.sad')
            @endif
        </figure>
    </section>
</main>
@stop
