@include('content.board.post', [
	'board' => $board,
	'post'  => $thread,
	'multiboard' => isset($multiboard) ? $multiboard : false,
])

@if (!$thread->reply_to)
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
<ul class="thread-replies">
@foreach ($thread->getReplies() as $reply)
	<li class="thread-reply">
		<article class="reply">
			@endspaceless
			@include('content.board.post', [
				'board'      => $board,
				'post'       => $reply,
				'reply_to'   => $thread,
				'multiboard' => isset($multiboard) ? $multiboard : false,
			])
			@spaceless
		</article>
	</li>
	@endforeach
	
	@if (isset($updater) && $updater === true)
	@include('widgets.thread-autoupdater')
	@endif
</ul>
@endif
@endspaceless
@endif
