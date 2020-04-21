<!-- Yes, this is only here so you can style it back into existance. -->
<nav class="boardlist" data-instant>
    <span class="boardlist-row row-links">
        <span class="boardlist-categories">
            <span class="boardlist-category">
                <span class="boardlist-items">
                    @foreach ($navLinks as $navItem => $navUrl)
                    <span class="boardlist-item">
                        <a href="{!! $navUrl !!}" class="boardlist-link">{{ trans("nav.global.{$navItem}") }}</a>
                    </span>
                    @endforeach
                </span>
            </span>
        </span>
    </span>

    @if ($showBoardList)
    <span class="boardlist-row row-boards">
        <span class="boardlist-categories">
        @foreach ($navBoards as $groupname => $boards)
            <span class="boardlist-category">
                <span class="boardlist-items">
                    @foreach ($boards as $board)
                    <span class="boardlist-item">
                        <a href="{!! $board->getUrl() !!}" class="boardlist-link">{!! $board->board_uri !!}</a>
                    </span>
                    @endforeach
                </span>
            </span>
        @endforeach
        </span>
    </span>
    @endif

    <span class="boardlist-row row-right">
        <span class="boardlist-categories">
            <span class="boardlist-category">
                <span class="boardlist-items">
                    <span class="boardlist-link" data-widget="js-config">@lang("nav.global.options")</a>
                </span>
            </span>
        </span>
    </span>
</nav>
