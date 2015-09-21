<ul class="post-details">
	<li class="post-detail post-subject">@if ($post->subject)<h3 class="post-detail-item subject ugc">{{ $post->subject }}</h3>@endif</li>
	
	<li class="post-detail post-author">
		<strong class="post-detail-item author ugc">
		@if ($post->email && !$catalog)<a href="mailto:{{ $post->email }}" class="post-detail-item email">@endif
			{{ $post->author ?: $board->getConfig('defaultName', trans('board.anonymous')) }}
		@if ($post->email && !$catalog)</a>@endif
		</strong>
		
		@if ($post->capcode_id > 0)
		<strong class="post-detail-item capcode">{{ $post->capcode_name }}</strong>
		@endif
	</li>
	
	@if ($board->getConfig('postsAuthorCountry', false) && $post->getCountryCode() && (!isset($catalog) || !$catalog))
		<li class="post-detail post-country" title="{{ trans('country.' . $post->getCountryCode()) }}"><span class="flag flag-{{ $post->getCountryCode() }}"></span></li>
	@endif
	
	<li class="post-detail post-postedon"><time class="post-detail-item postedon">{{ $post->created_at }}</time></li>
	
	@if (!isset($catalog) || !$catalog)
		@if ($board->getConfig('postsThreadId', false))
		<li class="post-detail post-authorid"><span class="post-detail-item authorid authorid-colorized"
			style="background-color: {{ $post->getAuthorIdBackgroundColor() }}; color: {{ $post->getAuthorIdForegroundColor() }};">{{ $post->author_id }}</span>
		</li>
		@endif
		
		<li class="post-detail post-id">
			<a href="{!! $post->url() !!}" class="post-no" data-board_id="{!! $post->board_id !!}" data-instant>@lang('board.post_number')</a>
			<a href="{!! $post->urlReply() !!}" class="post-reply" data-board_id="{!! $post->board_id !!}" {{(!isset($reply_to) || !$reply_to) ? "data-instant" : ""}}>{!! $post->board_id !!}</a>
		</li>
	@endif
	
	@if ($post->isStickied())
	<li class="post-detail detail-icon post-sticky" title="@lang('board.detail.sticky')"><i class="fa fa-thumb-tack"></i></li>
	@elseif ($post->isBumplocked())
	<li class="post-detail detail-icon post-bumplocked" title="@lang('board.detail.bumplocked')"><i class="fa fa-hand-o-down"></i></li>
	@endif
	
	@if ($post->isLocked())
	<li class="post-detail detail-icon post-locked" title="@lang('board.detail.locked')"><i class="fa fa-lock"></i></li>
	@endif
	
	@if (!is_null($post->adventure_id))
	<li class="post-detail detail-icon post-adventurer" title="@lang('board.detail.adventurer')"><i class="fa fa-rocket"></i></li>
	@endif
	
	@if (!is_null($post->author_ip))
	<li class="post-detail detail-icon post-logged" title="@lang('board.detail.logged')"><i class="fa fa-server"></i></li>
	@endif
	
	<li class="post-detail detail-icon post-deleted" title="@lang('board.detail.deleted')"><i class="fa fa-remove"></i></li>
</ul>