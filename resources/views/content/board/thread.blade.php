<div class="post-container @if ($op === $thread) op-container @else reply-container @endif" data-widget="post">
	@include( $c->template('board.post.single'), [
		'board'   => $board,
		'post'    => $thread,
		'catalog' => false,
	])
	
	<ul class="post-metas">
		@if ($thread->ban_id)
		<li class="post-meta meta-ban_reason">
			@if ($thread->ban_reason != "")
			<i class="fa fa-ban"></i> @lang('board.meta.banned_for', [ 'reason' => $thread->ban_reason ])
			@else
			<i class="fa fa-ban"></i> @lang('board.meta.banned')
			@endif
		</li>
		@endif
		
		@if ($thread->updated_by)
		<li class="post-meta meta-updated_by">
			<i class="fa fa-pencil"></i> @lang('board.meta.updated_by', [ 'name' => $thread->updated_by_username, 'time' => $thread->updated_at ])
		</li>
		@endif
	</ul>
</div>

{{--
	If we ask for $thread->replies here, it will run another query to check.
	Lets not do that until a reply-to-reply feature is added
--}}
@if ($op === $thread)
@if ($thread->reply_count > count($thread->replies))
<div class="thread-replies-omitted">{{ Lang::get('board.omitted_text_only', ['text_posts' => $thread->reply_count - count($thread->replies)]) }}</div>
@endif

<ul class="thread-replies">
	@foreach ($thread->getReplies() as $reply)
	<li class="thread-reply">
		<article class="reply">
			@include( $c->template('board.thread'), [
				'board'    => $board,
				'thread'   => $reply,
				'op'       => $op,
				'reply_to' => $reply_to,
			])
		</article>
	</li>
	@endforeach
</ul>
@endif