<div class="post-crown">
    <figure class="crown-figure">
        <img class="crown-image" src="{{ $board->getIconURL() }}" />
        <figcaption class="crown-title">
            <a href="/{!! $board->board_uri !!}/" class="crown-link">/{{ $board->board_uri }}/ - {{ $board->getDisplayName() }}</a>
        </figcaption>
    </figure>
</div>
