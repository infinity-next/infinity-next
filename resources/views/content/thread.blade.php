<div class="post-container">
	<div class="post-content">
		<a name="{!! $thread->board_id !!}"></a>
		<ul class="post-details">
			<li class="post-detail post-subject"><h3 class="subject">{{{ $thread->subject }}}</h3></li>
			<li class="post-detail post-author"><strong class="author">{{{ $thread->author ?: $board->getSetting('defaultName') }}}</strong></li>
			<li class="post-detail post-postedon"><time class="postedon">{{{ $thread->created_at }}}</time></li>
			<li class="post-detail post-authorid"><span class="authorid"></span></li>
			<li class="post-detail post-id">
				@if ($thread->reply_to)
				<a href="{!! url("{$board->board_uri}/thread/{$op->board_id}#{$thread->board_id}") !!}" class="post-no">@lang('board.post_number')</a>
				<a href="{!! url("{$board->board_uri}/thread/{$op->board_id}#reply-{$thread->board_id}") !!}" class="post-reply">{!! $thread->board_id !!}</a>
				@else
				<a href="{!! url("{$board->board_uri}/thread/{$op->board_id}") !!}" class="post-no">@lang('board.post_number')</a>
				<a href="{!! url("{$board->board_uri}/thread/{$op->board_id}#reply-{$thread->board_id}") !!}" class="post-reply">{!! $thread->board_id !!}</a>
				@endif
			</li>
		</ul>
		
		<ul class="post-attachments">
			@foreach ($thread->attachments as $attachment)
			<li class="post-attachment">
				<figure class="attachment">
					<a class="attachment-link" target="_new" href="{!! url("{$board->board_uri}/file/{$attachment->hash}/{$attachment->pivot->filename}") !!}">
						<img class="attachment-img" src="{!! url("{$board->board_uri}/file/{$attachment->hash}/{$attachment->pivot->filename}") !!}" alt="{{ $attachment->pivot->filename }}" />
					</a>
				</figure>
			</li>
			@endforeach
		</ul>
		
		<blockquote class="post ugc">
			{!! $thread->getBodyFormatted() !!}
		</blockquote>
	</div>
	
	<ul class="post-actions">
		<li class="post-action">
			@if ($thread->canDelete($user))
			<a class="post-action-link" href="{!! url("{$board->board_uri}/post/{$thread->board_id}/delete") !!}">@lang('board.action_delete')</a>
			@endif
			
			@if ($thread->canEdit($user))
			<a class="post-action-link" href="{!! url("{$board->board_uri}/post/{$thread->board_id}/edit") !!}">@lang('board.action_edit')</a>
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
			@include('content.thread', [ 'board' => $board, 'thread' => $reply, 'op' => $op])
		</article>
	</li>
	@endforeach
</ul>
@endif