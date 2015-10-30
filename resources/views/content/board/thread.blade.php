<div class="post-container {{ is_null($thread->reply_to) ? 'op-container' : 'reply-container' }} post-{{$thread->post_id}} post-{{$thread->board_uri}}-{{$thread->board_id}}" data-widget="post" data-updated-at="{{ $thread->updated_at->timestamp }}">
	@if ($thread->reports)
	@include('content.board.post.single', [
		'board'   => $board,
		'post'    => $thread,
		'catalog' => false,
	])
	
	<ul class="post-metas">
		@if ($thread->bans)
		@foreach ($thread->bans as $ban)
		<li class="post-meta meta-ban_reason">
			@if ($ban->justification != "")
			<i class="fa fa-ban"></i> @lang('board.meta.banned_for', [ 'reason' => $ban->justification ])
			@else
			<i class="fa fa-ban"></i> @lang('board.meta.banned')
			@endif
		</li>
		@endforeach
		@endif
		
		@if ($thread->updated_by)
		<li class="post-meta meta-updated_by">
			<i class="fa fa-pencil"></i> @lang('board.meta.updated_by', [ 'name' => $thread->updated_by_username, 'time' => $thread->updated_at->timestamp ])
		</li>
		@endif
	</ul>
	@else
	Post was hidden from view.
	@endif
</div>

{{--
	If we ask for $thread->replies here, it will run another query to check.
	Lets not do that until a reply-to-reply feature is added
--}}
@if ($op === $thread)
<ul class="thread-replies">
	@if ($thread->reply_count > count($thread->replies) && !$thread->reply_to)
	<div class="thread-replies-omitted">{{ Lang::get('board.omitted_text_only', ['text_posts' => $thread->reply_count - count($thread->replies)]) }}</div>
	@endif
	
	@foreach ($thread->getReplies() as $reply)
	<li class="thread-reply">
		<article class="reply">
			@include('content.board.thread', [
				'board'    => $board,
				'thread'   => $reply,
				'op'       => $op,
			])
		</article>
	</li>
	@endforeach
	
	@if ($thread->reply_to)
	@include('widgets.thread-autoupdater')
	@endif
</ul>
@endif