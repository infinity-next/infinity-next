<ul class="post-details">
	@set('catalog', isset($catalog) && $catalog)

	<li class="post-detail post-actions">@include('content.board.post.single.actions')</li>

	@if (isset($details['subject']) && $details['subject'])
	<li class="post-detail post-subject">
		<h3 class="post-detail-item subject ugc">
		@if (!$catalog)
		{{ @$details['subject'] }}
		@else
		<a href="{{ $post->getURL() }}" class="subject-link">{{ $post->subject }}</a>
		@endif
		</h3>
	</li>
	@endif

	<li class="post-detail post-author">
		<strong class="post-detail-item author ugc">
		@if ($details['email'] && !$catalog)<a href="mailto:{{ $details['email'] }}" class="post-detail-item email">@endif
			{{ $post->author ?: $board->getConfig('postAnonymousName', trans('board.anonymous')) }}
		@if ($details['email'] && !$catalog)</a>@endif
		</strong>
		@if (isset($details['insecure_tripcode']) && $details['insecure_tripcode'])
		<span class="insecure-tripcode tripcode">{{ $details['insecure_tripcode'] }}</span>
		@endif

		@if (isset($details['capcode_id']) && $details['capcode_id'] > 0)
		<strong class="post-detail-item capcode">{{ $post->getCapcodeName() }}</strong>
		@endif
	</li>

	@if (isset($details['flag_id']) && !is_null($details['flag_id']))
		<li class="post-detail post-custom-flag" title="{{ $post->flag->getDisplayName() }}">{!! $post->flag->asHTML() !!}</li>
	@endif

	@if ($board->getConfig('postsAuthorCountry', false) && $post->getCountryCode() && (!isset($catalog) || !$catalog))
		<li class="post-detail post-country" title="{{ trans('country.' . $post->getCountryCode()) }}"><span class="flag flag-{{ $post->getCountryCode() }}"></span></li>
	@endif

	<li class="post-detail post-postedon">
		<span class="post-detail-item postedon">
			@include('widgets.time', [
				'carbon' => $post->created_at
			])
		</span>
	</li>

	@if (!$catalog)
		@if ($board->getConfig('postsThreadId', false))
		<li class="post-detail post-authorid" id="{{ $details['board_id'] }}">
			<span class="post-detail-item authorid authorid-colorized" style="background-color: {{ $post->getAuthorIdBackgroundColor() }}; color: {{ $post->getAuthorIdForegroundColor() }};">{{ $details['author_id'] }}</span>
		</li>
		@endif

		<li class="post-detail post-id" id="reply-{{ $details['board_id'] }}">
			<a href="{!! $post->url() !!}" class="post-no" data-board_id="{!! $details['board_id'] !!}" data-instant>@lang('board.post_number')</a>
			<a href="{!! $post->urlReply() !!}" class="post-reply" data-board_id="{!! $details['board_id'] !!}" {{ !$reply_to ? "data-instant" : "" }}>{!! $details['board_id'] !!}</a>
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

	@if (isset($details['adventure_id']) && !is_null($details['adventure_id']))
	<li class="post-detail detail-icon post-adventurer" title="@lang('board.detail.adventurer')"><i class="fa fa-rocket"></i></li>
	@endif

	<li class="post-detail detail-icon post-deleted" title="@lang('board.detail.deleted')"><i class="fa fa-remove"></i></li>

	@if (!$catalog)
		@if (!$reply_to && $post->isOP())
		<li class="post-detail detail-open">
			{{-- Mobile Last 50 Open --}}
			<a class="thread-replies-open only-mobile" href="{{ $post->getURL('l50')}}">
				{{ Lang::choice(
					'board.omitted.show.open',
					50,
					[
						'count' => 50,
					]
				) }}
			</a>

			{{-- Desktop Last 350 Open --}}
			<a class="thread-replies-open no-mobile" href="{{ $post->getURL('l350')}}">
				{{ Lang::choice(
					'board.omitted.show.open',
					350,
					[
						'count' => 350,
					]
				) }}
			</a>
		</li>
		@endif

		<li class="post-detail detail-cites" data-no-instant>@include('content.board.post.single.cites')</li>
	@endif
</ul>
