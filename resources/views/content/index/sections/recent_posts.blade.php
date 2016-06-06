<section id="site-recent-posts" class="grid-50 tablet-grid-50 mobile-grid-50 {{ $rtl ? 'pull-50' : ''}}">
    <div class="smooth-box">
        <h2 class="index-title">@lang('index.title.recent_posts')</h2>
        <div class="grid-container">
            <ul class="recent-posts">
                @foreach (\App\Post::getRecentPosts(24, false) as $post)
                    @if ($post->board)
                        @set('board_icon', "url('".$post->board->getAssetUrl('board_icon')."')")
                    @else
                        @set('board_icon', "none")
                    @endif
                    <li class="recent-post grid-25 tablet-grid-50 mobile-grid-100">
                        <span class="recent-post-bg" style="background-image: {{ $board_icon }};"></span>
                        <a class="recent-post-link @if ($post->isOp()) recent-post-op @endif" href="{{ $post->getURL() }}"></a>
                        <blockquote class="post ugc" {!! $post->getBodyDirectionAttr() !!}>
                            {!! $post->getBodyFormatted() !!}
                        </blockquote>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
