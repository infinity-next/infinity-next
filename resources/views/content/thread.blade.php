<div class="post-container">
	<div class="post-content">
		<a name="{!! $thread->board_id !!}"></a>
		<ul class="post-details">
			<li class="post-detail post-subject"><h3 class="subject">{{{ $thread->subject }}}</h3></li>
			<li class="post-detail post-author"><strong class="author">{{{ $thread->author ?: $board->getSetting('defaultName') }}}</strong></li>
			<li class="post-detail post-postedon"><time class="postedon">{{{ $thread->created_at }}}</time></li>
			<li class="post-detail post-authorid"><span class="authorid"></span></li>
			<li class="post-detail post-id">
				<a href="{!! url("{$board->uri}/thread/{$thread->board_id}#{$thread->board_id}") !!}" class="post-no">No.</a>
				<a href="{!! url("{$board->uri}/thread/{$thread->board_id}#reply-{$thread->board_id}") !!}" class="post-reply">{!! $thread->board_id !!}</a>
			</li>
		</ul>
		
		<ul class="post-attachments">
			@foreach ($thread->attachments as $attachment)
			<li class="post-attachment">
				<figure class="attachment">
					<a class="attachment-link" href="{!! url("{$board->uri}/file/{$attachment->storage->hash}/{$attachment->filename}") !!}">
						<img class="attachment-img" src="{!! url("{$board->uri}/file/{$attachment->storage->hash}/{$attachment->filename}") !!}" alt="{{ $attachment->filename }}" />
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
			<a class="post-action-link" href="{{{url("{$board->uri}/post/{$thread->board_id}/delete")}}}">Delete</a>
			@endif
			
			@if ($thread->canEdit($user))
			<a class="post-action-link" href="{{{url("{$board->uri}/post/{$thread->board_id}/edit")}}}">Edit</a>
			@endif
		</li>
	</ul>
</div>

<div class="thread-replies-omitted">

</div>

@if (isset($posts[ $thread->id ]))
<ul class="thread-replies">
	@foreach ($posts[ $thread->id ] as $thread)
	<li class="thread-reply">
		<article class="reply">
			@include('content.thread', [ 'thread' => $thread, 'posts' => $posts ])
		</article>
	</li>
	@endforeach
</ul>
@endif