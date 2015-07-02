<div class="post-content @if ($post->capcode_capcode > 0) capcode-{{{ $post->capcode_role }}} @endif">
	<a name="{!! $post->board_id !!}"></a>
	<ul class="post-details">
		@if ($post->subject)
		<li class="post-detail post-subject"><h3 class="subject">{{{ $post->subject }}}</h3></li>
		@endif
		
		<li class="post-detail post-author">
			<strong class="author">
			@if ($post->email)<a href="mailto:{{{ $post->email }}}" class="email">@endif
				{{{ $post->author ?: $board->getSetting('defaultName', trans('board.anonymous')) }}}
			@if ($post->email)</a>@endif
			</strong>
			
			@if ($post->capcode_id > 0)
			<strong class="capcode">{{{ $post->capcode_name }}}</strong>
			@endif
		</li>
		<li class="post-detail post-postedon"><time class="postedon">{{{ $post->created_at }}}</time></li>
		<li class="post-detail post-authorid"><span class="authorid"></span></li>
		
		@if (isset($op))
		<li class="post-detail post-id">
			@if ($post->reply_to)
			<a href="{!! url("{$board->board_uri}/thread/{$op->board_id}#{$post->board_id}") !!}" class="post-no">@lang('board.post_number')</a>
			<a href="{!! url("{$board->board_uri}/thread/{$op->board_id}#reply-{$post->board_id}") !!}" class="post-reply">{!! $post->board_id !!}</a>
			@else
			<a href="{!! url("{$board->board_uri}/thread/{$op->board_id}") !!}" class="post-no">@lang('board.post_number')</a>
			<a href="{!! url("{$board->board_uri}/thread/{$op->board_id}#reply-{$post->board_id}") !!}" class="post-reply">{!! $post->board_id !!}</a>
			@endif
		</li>
		@endif
		
		@if ($post->stickied_at)
		<li class="post-detail post-sticky"><i class="fa fa-thumb-tack"></i></li>
		@endif
	</ul>
	
	<ul class="post-attachments">
		@foreach ($post->attachments as $attachment)
		<li class="post-attachment">
			<figure class="attachment">
				<a class="attachment-link" target="_new" href="{!! $attachment->getDownloadURL($board) !!}">
					<img class="attachment-img" src="{!! $attachment->getDownloadURL($board) !!}" />
				</a>
			</figure>
		</li>
		@endforeach
	</ul>
	
	<blockquote class="post ugc">
		{!! $post->getBodyFormatted() !!}
	</blockquote>
</div>