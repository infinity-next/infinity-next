<section id="site-recent-images" class="grid-50 tablet-grid-50 mobile-grid-50 {{ $rtl ? 'push-50' : ''}}">
    <div class="smooth-box">
        <h2 class="index-title">@lang('index.title.recent_images')</h2>
        <ul class="recent-images selfclear">
            @foreach (\App\PostAttachment::getRecentImages(30, false) as $attachment)
            <li class="recent-image {{ $attachment->post->board->isWorksafe() ? 'sfw' : 'nsfw' }}">
                <a class="recent-image-link" href="{{ $attachment->post->getUrl() }}">
                    {!! $attachment->toHtml(116) !!}
                </a>
            </li>
            @endforeach
        </ul>
    </div>
</section>
