{{--
    BE CAREFUL
    This is one of the most important templates in the entire application.
    Changes made here can break every view if not done correctly.
    Many, many things depend on this template and its dependencies.

    ABOUT "$details"
    We use $details (derived from $post->getAttributes() method) instead of
    $post->attribute_name most of the time now because it is faster. Laravel's
    __get magic method calls getAttributes which calls getAttribute which calls
    a bunch of other stuff.
--}}
@set('details',     $post->getAttributes())
@set('catalog',     $catalog ?? false)
@set('multiboard',  $multiboard ?? false)
@set('preview',     $preview ?? (!isset($updater) || !$updater) && $details['body_too_long'])
@set('reply_to',    $reply_to ?? false)

@include('content.board.post.single.container', [
    'post' => $post,
    'details' => $details,
])
    {{-- The interboard crown applied to posts in Overboard. --}}
    @if (!$catalog && !$reply_to && ($crown ?? false || $multiboard))
    @include('content.board.crown', [
        'board' => $post->getRelation('board'),
    ])
    @endif

    <div class="post-interior">
        @if (true || !isset($details['reports']))
        @include('content.board.post.single', [
            'board'   => $board,
            'post'    => $post,
            'catalog' => isset($catalog) ? !!$catalog : false,
        ])

        {{-- Each condition for an item must also be supplied as a condition so the <ul> doesn't appear inappropriately. --}}
        @if ($preview || isset($details['bans']) || isset($details['updated_by']))
        <div class="post-metas">
            @if ($preview)
            <div class="post-meta meta-see_more">@lang('board.preview_see_more', [
                'url' => $post->getUrl(),
            ])</div>
            @endif

            @foreach ($details['bans'] ?? [] as $ban)
            <div class="post-meta meta-ban_reason">
                @if ($ban->justification != "")
                <i class="fa fa-ban"></i> @lang('board.meta.banned_for', [ 'reason' => $ban->justification ])
                @else
                <i class="fa fa-ban"></i> @lang('board.meta.banned')
                @endif
            </div>
            @endforeach

            @if (isset($details['updated_by']))
            <div class="post-meta meta-updated_by">
                <i class="fa fa-pencil"></i> @lang('board.meta.updated_by', [
                    'name' => $details['updated_by_username'],
                    'time' => $post->updated_at
                ])
            </div>
            @endif
        </div>
        @endif
        @endif
    </div>
</article>
