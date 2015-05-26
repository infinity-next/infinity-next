<div class="post-container @if ($op === $thread) op-container @else reply-container @endif">
	@include( $c->template('board.post.single'), [
		'board'  => $board,
		'post'   => $thread,
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
	
	<ul class="post-actions">
		<li class="post-action">
			@if ($thread->canEdit($user))
			<a class="post-action-link action-link-edit" href="{!! url("{$board->board_uri}/post/{$thread->board_id}/edit") !!}">@lang('board.action.edit')</a>
			@endif
			
			@if ($thread->canSticky($user) && $op)
			@if (!$thread->stickied_at)
			<a class="post-action-link action-link-sticky" href="{!! url("{$board->board_uri}/post/{$thread->board_id}/sticky") !!}">@lang('board.action.sticky')</a>
			@else
			<a class="post-action-link action-link-unsticky" href="{!! url("{$board->board_uri}/post/{$thread->board_id}/unsticky") !!}">@lang('board.action.unsticky')</a>
			@endif
			@endif
			
			@if ($board->canBan($user))
			<a class="post-action-link action-link-ban" href="{!! url("{$board->board_uri}/post/{$thread->board_id}/mod/ban") !!}">@lang('board.action.ban')</a>
			@endif
			
			@if ($thread->canDelete($user))
				<a class="post-action-link action-link-delete" href="{!! url("{$board->board_uri}/post/{$thread->board_id}/mod/delete") !!}">@lang('board.action.delete')</a>
				
				@if ($board->canDelete($user))
					<a class="post-action-link action-link-delete" href="{!! url("{$board->board_uri}/post/{$thread->board_id}/mod/delete/all") !!}">@lang('board.action.delete_board')</a>
				@endif
				
				@if ($board->canBan($user))
					<a class="post-action-link action-link-bandelete" href="{!! url("{$board->board_uri}/post/{$thread->board_id}/mod/ban/delete") !!}">@lang('board.action.ban_delete')</a>
					<a class="post-action-link action-link-delete" href="{!! url("{$board->board_uri}/post/{$thread->board_id}/mod/ban/delete/all") !!}">@lang('board.action.ban_delete_board')</a>
				@endif
				
				@if ($user->canDeleteGlobally())
					<a class="post-action-link action-link-delete" href="{!! url("{$board->board_uri}/post/{$thread->board_id}/mod/delete/global") !!}">@lang('board.action.delete_global')</a>
					
					@if ($user->canBanGlobally())
					<a class="post-action-link action-link-bandelete" href="{!! url("{$board->board_uri}/post/{$thread->board_id}/mod/ban/delete/global") !!}">@lang('board.action.ban_delete_global')</a>
					@endif
				@endif
			@endif
		</li>
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
	@foreach ($thread->replies as $reply)
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