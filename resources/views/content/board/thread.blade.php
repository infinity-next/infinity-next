<span class="wingding"><span class="no-mobile">&gt;&gt;</span></span>{!! $thread->toHtml(
        false,
        isset($multiboard) ? $multiboard : false,
        false
) !!}

@if (!$thread->reply_to)
@include('meta.post')
@spaceless
@if ($thread->reply_count > count($thread->replies) && !$thread->reply_to)
<div class="thread-replies-omitted">
{{-- Normal Reply Count --}}
<span class="thread-replies-count">{{ Lang::get(
    $thread->reply_file_count > 0 ? 'board.omitted.text.both' : 'board.omitted.text.only',
    [
        'text_posts' => Lang::choice(
            'board.omitted.count.replies',
            $thread->reply_count - $thread->getReplyCount(),
            [
                'count' => $thread->reply_count - $thread->getReplyCount()
            ]
        ),
        'text_files' => Lang::choice(
            'board.omitted.count.files',
            $thread->reply_file_count - $thread->getReplyFileCount(),
            [
                'count' => $thread->reply_file_count - $thread->getReplyFileCount()
            ]
        ),
    ]
) }}</span>
{{-- JavaScript Expand Inline
<a class="thread-replies-expand no-mobile require-js" href="#">{{ Lang::get(
    'board.omitted.show.inline'
) }}</a> --}}
</div>
@endif

@if (!isset($catalog) || !$catalog)
<div class="replies">
    @foreach ($thread->getReplies() as $reply)
    <div class="reply">
        <span class="wingding"><span class="no-mobile">&gt;&gt;</span></span>
        @endspaceless{!! $reply->toHtml(
                false,
                isset($multiboard) ? $multiboard : false,
                false
        ) !!}@spaceless
    </div>
    @endforeach

    @if (isset($updater) && $updater === true)
    @include('widgets.thread-autoupdater')
    @endif
</div>
@endif
@endspaceless
@endif
