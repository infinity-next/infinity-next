<ul class="post-details">
	@set('catalog', isset($catalog) && $catalog)
	
	<li class="post-detail post-actions">@include('content.board.post.single.actions')</li>
	
	@if ($post->subject)
	<li class="post-detail post-subject">
		@if ($catalog)
		<h3 class="post-detail-item subject ugc">{{ $post->subject }}</h3>
		@else
		<a href="{{ $post->getURL() }}" class="post-detail-item subject ugc">{{ $post->subject }}</a>
		@endif
	</li>
	@endif
	
	<li class="post-detail post-author">
		<strong class="post-detail-item author ugc">
		@if ($post->email && !$catalog)<a href="mailto:{{ $post->email }}" class="post-detail-item email">@endif
			{{ $post->author ?: $board->getConfig('postAnonymousName', trans('board.anonymous')) }}
		@if ($post->email && !$catalog)</a>@endif
		</strong>
		@if ($post->insecure_tripcode)
		<span class="insecure-tripcode tripcode">{{ $post->insecure_tripcode }}</span>
		@endif
		
		@if ($post->capcode_id > 0)
		<strong class="post-detail-item capcode">{{ $post->getCapcodeName() }}</strong>
		@endif
	</li>
	
	@if ($post->flag_id)
		<li class="post-detail post-custom-flag" title="{{ $post->flag->getDisplayName() }}">{!! $post->flag->asHTML() !!}</li>
	@endif
	
	@if ($board->getConfig('postsAuthorCountry', false) && $post->getCountryCode() && (!isset($catalog) || !$catalog))
		<li class="post-detail post-country" title="{{ trans('country.' . $post->getCountryCode()) }}"><span class="flag flag-{{ $post->getCountryCode() }}"></span></li>
	@endif
	
	<li class="post-detail post-postedon"><time class="post-detail-item postedon" title="{{ $post->created_at->diffForHumans() }}">{{ $post->created_at }}</time></li>
	
	@if (!$catalog)
		@if ($board->getConfig('postsThreadId', false))
		<li class="post-detail post-authorid" id="{{ $post->board_id}}">
			<span class="post-detail-item authorid authorid-colorized" style="background-color: {{ $post->getAuthorIdBackgroundColor() }}; color: {{ $post->getAuthorIdForegroundColor() }};">{{ $post->author_id }}</span>
		</li>
		@endif
		
		<li class="post-detail post-id" id="reply-{{ $post->board_id}}">
			<a href="{!! $post->url() !!}" class="post-no" data-board_id="{!! $post->board_id !!}" data-instant>@lang('board.post_number')</a>
			<a href="{!! $post->urlReply() !!}" class="post-reply" data-board_id="{!! $post->board_id !!}" {{ (!isset($reply_to) || !$reply_to) ? "data-instant" : "" }}>{!! $post->board_id !!}</a>
		</li>
	@endif
	
	@if ($post->isStickied())
	<li class="post-detail detail-icon post-sticky" title="@lang('board.detail.sticky')"><i class="fa fa-thumb-tack"></i></li>
	@elseif ($post->isBumplocked())
	<li class="post-detail detail-icon post-bumplocked" title="@lang('board.detail.bumplocked')"><i class="fa fa-anchor"></i></li>
	@endif
	
	@if ($post->isLocked())
	<li class="post-detail detail-icon post-locked" title="@lang('board.detail.locked')"><i class="fa fa-lock"></i></li>
	@endif
	
	@if (!is_null($post->adventure_id))
	<li class="post-detail detail-icon post-adventurer" title="@lang('board.detail.adventurer')"><i class="fa fa-rocket"></i></li>
	@endif
	
	@if (!is_null($post->author_ip) && ($user->canViewGlobalHistory() || $user->canViewHistory($post)))
		<li class="post-detail detail-icon post-logged" title="@lang('board.detail.history')">
			<a href="{{ $user->canViewGlobalHistory() ?  url('cp/history/' . $post->author_ip->toText()) : $post->getURL("history\{$post->board_id}") }}"><i class="fa fa-server"></i></a>
		</li>
	@endif
	
	<li class="post-detail detail-icon post-deleted" title="@lang('board.detail.deleted')"><i class="fa fa-remove"></i></li>
	
	@if (!$catalog)
		<li class="post-detail detail-cites" data-no-instant>@include('content.board.post.single.cites')</li>
	@endif
</ul>
