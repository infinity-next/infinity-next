<div class="post-container {{ is_null($post->reply_to) ? 'op-container' : 'reply-container' }} post-{{$post->post_id}} post-{{$post->board_uri}}-{{$post->board_id}}" data-widget="post" data-updated-at="{{ $post->updated_at->timestamp }}">
	@if ($post->reports)
	@include('content.board.post.single', [
		'board'   => $board,
		'post'    => $post,
		'catalog' => isset($catalog) ? !!$catalog : false,
	])
	
	<ul class="post-metas">
		@if ($post->bans)
		@foreach ($post->bans as $ban)
		<li class="post-meta meta-ban_reason">
			@if ($ban->justification != "")
			<i class="fa fa-ban"></i> @lang('board.meta.banned_for', [ 'reason' => $ban->justification ])
			@else
			<i class="fa fa-ban"></i> @lang('board.meta.banned')
			@endif
		</li>
		@endforeach
		@endif
		
		@if ($post->updated_by)
		<li class="post-meta meta-updated_by">
			<i class="fa fa-pencil"></i> @lang('board.meta.updated_by', [ 'name' => $post->updated_by_username, 'time' => $post->updated_at ])
		</li>
		@endif
	</ul>
	@else
	Post was hidden from view.
	@endif
</div>