@if ($featured_boards->count() > 0)
<section class="grid-container">
    <div class="infobox" id="site-featured-boards">
        <div class="infobox-title">@lang('index.title.featured_boards')</div>

        <div id="featured-boards">
            @foreach ($featured_boards as $featured_board)
            <div class="post-crown grid-33 tablet-grid-33 mobile-grid-100">
                <a href="/{!! $featured_board->board_uri !!}/" class="crown-link">
                    <figure class="crown-figure">
                        <img class="crown-image" src="{{ $featured_board->getIconURL() }}" />
                        <figcaption class="crown-title">/{{ $featured_board->board_uri }}/ - {{ $featured_board->getDisplayName() }}</figcaption>
                    </figure>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
