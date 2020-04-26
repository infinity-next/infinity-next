<div class="post-details">
    @set('catalog', isset($catalog) && $catalog)

    @if (isset($details['subject']) && $details['subject'] != "")
    <span class="post-detail post-subject">
        <h3 class="post-detail-item subject ugc">
        @if (!$catalog)
        {{ @$details['subject'] }}
        @else
        <a href="{{ $post->getUrl() }}" class="subject-link">{{ $post->subject }}</a>
        @endif
        </h3>
    </span>
    @endif

    <span class="post-detail post-author">
        <strong class="post-detail-item author ugc">
        @if (isset($details['email']) && !!$details['email'] && !$catalog)<a href="mailto:{{ $details['email'] }}" class="post-detail-item email">@endif
            {{ isset($details['author']) && !!$details['author'] ? $details['author'] : $board->getConfig('postAnonymousName', trans('board.anonymous')) }}
        @if (isset($details['email']) && !!$details['email'] && !$catalog)</a>@endif
        </strong>

        {!! $post->getTripcodeHtml() !!}

        {{-- Always here. Added in by CSS. --}}
        <span class="author-you">@lang('board.you')</span>

        @if (isset($details['capcode_id']) && $details['capcode_id'] > 0)
        <strong class="post-detail-item capcode">{{ $post->getCapcodeName() }}</strong>
        @endif
    </span>

    @if (isset($details['flag_id']) && !is_null($details['flag_id']))
        <span class="post-detail post-custom-flag" title="{{ $post->flag->getDisplayName() }}">{!! $post->flag->toHtml() !!}</span>
    @endif

    @if ($board->getConfig('postsAuthorCountry', false) && $post->getCountryCode() && (!isset($catalog) || !$catalog))
        <span class="post-detail post-country" title="{{ trans('country.' . $post->getCountryCode()) }}"><span class="flag flag-{{ $post->getCountryCode() }}"></span></span>
    @endif

    <span class="post-detail post-postedon">
        <span class="post-detail-item postedon">
            @include('widgets.time', [ 'carbon' => $post->created_at, ])
        </span>
    </span>

    @if (!$catalog)
        @if ($board->getConfig('postsThreadId', false))
        <span class="post-detail post-authorid" id="{{ $details['board_id'] }}">
            <span class="post-detail-item authorid authorid-colorized" style="background-color: {{ $post->getAuthorIdBackgroundColor() }}; color: {{ $post->getAuthorIdForegroundColor() }};">{{ $details['author_id'] }}</span>
        </span>
        @endif

        <span class="post-detail post-id" id="reply-{{ $details['board_id'] }}">
            <a href="{!! $post->getUrl() !!}" class="post-no" data-board_id="{!! $details['board_id'] !!}" data-instant>@lang('board.post_number')</a>
            <a href="{!! $post->getReplyUrl() !!}" class="post-reply" data-board_id="{!! $details['board_id'] !!}" {{ !$reply_to ? "data-instant" : "" }}>{!! $details['board_id'] !!}</a>
        </span>
    @endif

    @if (!$reply_to)
    @if ($post->isStickied())
    <span class="post-detail detail-icon post-sticky" title="@lang('board.detail.sticky')"><i class="fas fa-thumb-tack"></i></span>
    @elseif ($post->isBumplocked())
    <span class="post-detail detail-icon post-bumplocked" title="@lang('board.detail.bumplocked')"><i class="fas fa-anchor"></i></span>
    @endif

    @if ($post->isLocked())
    <span class="post-detail detail-icon post-locked" title="@lang('board.detail.locked')"><i class="fas fa-lock"></i></span>
    @endif
    @endif

    @if (isset($details['adventure_id']) && !is_null($details['adventure_id']))
    <span class="post-detail detail-icon post-adventurer" title="@lang('board.detail.adventurer')"><i class="fas fa-rocket"></i></span>
    @endif

    <span class="post-detail detail-icon post-deleted" title="@lang('board.detail.deleted')"><i class="fas fa-remove"></i></span>

    @if (!$catalog)
    <span class="post-detail post-actions">@include('content.board.post.single.actions')</span>
    @endif

    @if (!$catalog)
        @if (!$reply_to && $post->isOp())
        <span class="post-detail detail-open">
            {{-- Mobile Last 50 Open
            <a class="thread-replies-open only-mobile" href="{{ $post->getUrl('l50')}}">{{
                Lang::choice(
                    'board.omitted.show.open',
                    50,
                    [
                        'count' => 50,
                    ]
                ) }}
            </a> --}}

            {{-- Desktop Last 350 Open --}}
            <a class="thread-replies-open no-mobile" href="{{ $post->getUrl('l350')}}">{{
                Lang::choice(
                    'board.omitted.show.open',
                    350,
                    [
                        'count' => 350,
                    ]
                )
            }}</a>

            <a class="thread-replies-open no-mobile" href="{{ $post->getUrl() }}">@lang('board.omitted.show.reply')</a>
        </span>
        @endif

        <span class="post-detail detail-cites" data-no-instant>@include('content.board.post.single.cites')</span>
    @endif
</div>
