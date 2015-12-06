@include('content.board.post', [
	'board' => $board,
	'post'  => $thread,
])

@if (!$thread->reply_to)
@spaceless
<ul class="thread-replies">
	@if ($thread->reply_count > count($thread->replies) && !$thread->reply_to)
	<div class="thread-replies-omitted">{{ Lang::get(
		$thread->reply_file_count > 0 ? 'board.omitted_text_both' : 'board.omitted_text_only',
		[
			'text_posts' => Lang::choice('board.omitted_replies', $thread->reply_count - $thread->getReplyCount(), [ 'count' => $thread->reply_count - $thread->getReplyCount() ]),
			'text_files' => Lang::choice('board.omitted_file', $thread->reply_file_count - $thread->getReplyFileCount(), [ 'count' => $thread->reply_file_count - $thread->getReplyFileCount() ]),
		]
	) }}</div>
	@endif
	
	@foreach ($thread->getReplies() as $reply)
	<li class="thread-reply">
		<article class="reply">
			@endspaceless
			@include('content.board.post', [
				'board'      => $board,
				'post'       => $reply,
				'multiboard' => false,
			])
			@spaceless
		</article>
	</li>
	@endforeach
	
	@if (isset($updater) && $updater === true)
	@include('widgets.thread-autoupdater')
	@endif
</ul>
@endspaceless
@endif