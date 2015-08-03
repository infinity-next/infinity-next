<ul class="post-details">
	<li class="post-detail post-subject">@if ($post->subject)<h3 class="post-detail-item subject ugc">{{ $post->subject }}</h3>@endif</li>
	
	<li class="post-detail post-author">
		<strong class="post-detail-item author ugc">
		@if ($post->email && !$catalog)<a href="mailto:{{ $post->email }}" class="post-detail-item email">@endif
			{{ $post->author ?: $board->getSetting('defaultName', trans('board.anonymous')) }}
		@if ($post->email && !$catalog)</a>@endif
		</strong>
		
		@if ($post->capcode_id > 0)
		<strong class="post-detail-item capcode">{{ $post->capcode_name }}</strong>
		@endif
	</li>
	
	<li class="post-detail post-postedon"><time class="post-detail-item postedon">{{ $post->created_at }}</time></li>
	
	<li class="post-detail post-authorid"><span class="post-detail-item authorid"></span></li>
	
	@if (isset($op))
	<li class="post-detail post-id">
		@if ($post->reply_to)
		<a href="{!! url("{$board->board_uri}/thread/{$op->board_id}#{$post->board_id}") !!}" class="post-no">@lang('board.post_number')</a>
		<a href="{!! url("{$board->board_uri}/thread/{$op->board_id}#reply-{$post->board_id}") !!}" class="post-reply">{!! $post->board_id !!}</a>
		@else
		<a href="{!! url("{$board->board_uri}/thread/{$op->board_id}") !!}" class="post-no" data-instant>@lang('board.post_number')</a>
		<a href="{!! url("{$board->board_uri}/thread/{$op->board_id}#reply-{$post->board_id}") !!}" class="post-reply">{!! $post->board_id !!}</a>
		@endif
	</li>
	@endif
	
	@if ($post->isStickied())
	<li class="post-detail post-sticky" title="@lang('board.detail.sticky')"><i class="fa fa-thumb-tack"></i></li>
	@elseif ($post->isBumplocked())
	<li class="post-detail post-bumplocked" title="@lang('board.detail.bumplocked')"><i class="fa fa-hand-o-down"></i></li>
	@endif
	
	@if ($post->isLocked())
	<li class="post-detail post-locked" title="@lang('board.detail.locked')"><i class="fa fa-lock"></i></li>
	@endif
	
	@if ($post->isDeleted())
	<li class="post-detail post-deleted" title="@lang('board.detail.deleted')"><i class="fa fa-remove"></i></li>
	@endif
</ul>