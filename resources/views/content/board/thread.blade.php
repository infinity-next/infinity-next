@include('content.board.post', [
	'board' => $board,
	'post'  => $thread,
])

@if (!$thread->reply_to)
<ul class="thread-replies">
	@if ($thread->reply_count > count($thread->replies) && !$thread->reply_to)
	<div class="thread-replies-omitted">{{ Lang::get('board.omitted_text_only', ['text_posts' => $thread->reply_count - count($thread->replies)]) }}</div>
	@endif
	
	@foreach ($thread->getReplies() as $reply)
	@spaceless
	<li class="thread-reply">
		<article class="reply">
			@endspaceless
			@include('content.board.post', [
				'board' => $board,
				'post'  => $reply,
			])
			@spaceless
		</article>
	</li>
	@endspaceless
	@endforeach
	
	@if (isset($updater) && $updater === true)
	@include('widgets.thread-autoupdater')
	@endif
</ul>
@endif