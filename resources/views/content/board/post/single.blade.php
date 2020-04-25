{{-- Don't include this directly. Call `content.board.post`. --}}
<div class="post-content">
    <a name="{!! $details['board_id'] !!}"></a>
    <a name="reply-{!! $details['board_id'] !!}"></a>

    @if ($catalog ?? false)
        @if ($multiboard ?? false)
        @include('content.board.crown', [
            'board'  => $post->board,
        ])
        @endif

        <a class="catalog-open" href="{!! $post->getUrl() !!}" data-instant>
            {{ Lang::choice('board.detail.catalog_stats',
                isset($post->page_number) ? 1 : 0,
                [
                    'reply_count' => $post->reply_count,
                    'file_count'  => $post->reply_file_count,
                    'page'        => $post->page_number,
                ]
            ) }}
        </a>

        @if ($post->hasDetails())
            @include('content.board.post.single.details')
        @endif

        @if ($post->hasAttachments())
            @include('content.board.post.single.attachments')
        @endif

        @include('content.board.post.single.post')

        @if (isset($details['replies']))
        <div class="catalog-replies">
        @foreach ($post->getRelation('replies') as $reply)
        <a href="{{ $reply->getUrl() }}" class="catalog-reply">
            <time class="time-passed" datetime="{{ $reply->created_at->toDateTimeString() }}">{{ $reply->getTimeSince() }}</time> {{ $reply->getBodyExcerpt(100) }}
        </a>
        @endforeach
        </div>
        @endif
    @else
        @include('content.board.post.single.details')
        <div class="post-content-wrapper">
            @include('content.board.post.single.attachments')
            @include('content.board.post.single.post')
        </div>
    @endif
</div>
