<nav class="gnav" data-widget="gnav">
    <div class="grid-container">
        <div class="grid-100 gnav-binder">
            <ul class="gnav-groups">
                <li class="gnav-group">
                    <ul class="gnav-items">
                        <li class="gnav-item item-config require-js">
                            <span class="gnav-link" data-widget="js-config">{{ trans("nav.global.options") }}</a>
                        </li>

                        @foreach (Settings::getNavigationPrimary() as $navItem => $navUrl)
                        <li class="gnav-item item-{{ $navItem }} {{ false ? 'gnav-active' : '' }}">
                            <a href="{!! $navUrl !!}" class="gnav-link" data-item="{{ $navItem }}">{{ trans("nav.global.{$navItem}") }}</a>
                        </li>
                        @endforeach
                    </ul>
                </li>
            </ul>

            @if (Settings::get('boardListShow', false))
            <div class="flyout" id="flyout-boards" data-no-instant>
                <div class="flyout-container">
                    <div class="flyout-header">
                        <a class="flyout-headlink" href="{{ url('/boards.html') }}"><i class="fa fa-th-list"></i>&nbsp;@lang('nav.global.view_all_boards')</a>
                    </div>

                    <ul class="flyout-cols">
                        <li class="flyout-col" id="favorite-boards">
                            <div class="flyout-col-title">{{ trans("nav.global.flyout.favorite_boards") }} <i class="fa fa-star"></i></div>
                            <ul class="flyout-list">
                            {{-- This is now stored in gnav.widget.js!
                                <li class="flyout-item">
                                    <a href="{!! url('b') !!}" class="flyout-link">
                                        <span class="flyout-uri">/b/</span>
                                        <span class="flyout-title">Random</span>
                                    </a>
                                </li>
                            --}}
                            </ul>
                        </li>

                        @if (is_array(Settings::getNavigationPrimaryBoards()))
                        @foreach (Settings::getNavigationPrimaryBoards() as $groupname => $boards)
                        @if ($boards->count())
                        <li class="flyout-col">
                            <div class="flyout-col-title">{{ trans("nav.global.flyout.{$groupname}") }}</div>
                            <ul class="flyout-list">
                                @foreach ($boards as $board)
                                <li class="flyout-item">
                                    <a href="{!! $board->getURL() !!}" class="flyout-link">
                                        <span class="flyout-uri">/{!! $board->board_uri !!}/</span>
                                        <span class="flyout-title">{!! $board->title !!}</span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </li>
                        @endif
                        @endforeach
                        @endif
                    </ul>
                </div>
            </div>
            @endif

        </div>
    </div>
</nav>

@include('nav.boardlist')
