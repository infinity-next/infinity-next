{{--
	BE CAREFUL
	This is one of the most important templates in the entire application.
	Changes made here can break every view if not done correctly.
	Many, many things depend on this template and its dependencies.
	
	ABOUT "$details"
	We use $details (derived from $post->getAttributes() method) instead of
	$post->attribute_name most of the time now because it is faster. Laravel's
	__get magic method calls getAttributes which calls getAttribute which calls
	a bunch of other stuff.
--}}
@set('details',     $post->getAttributes())
@set('catalog',     isset($catalog) && $catalog ? true : false)
@set('multiboard',  isset($multiboard) ? $multiboard : false)
@set('preview',     isset($preview)    ? $preview    : (!isset($updater) || !$updater) && $post->body_too_long )
@set('reply_to',    isset($reply_to) && $reply_to ? $reply_to : false)

<div class="post-container {{ is_null($details['reply_to']) ? 'op-container' : 'reply-container' }} {{ $post->hasBody() ? 'has-body' : 'has-no-body' }} {{ $post->attachments->count() > 1 ? 'has-files' : $post->attachments->count() > 0 ? 'has-file' : 'has-no-file' }}"
	id="post-{{ $details['board_uri'] }}-{{ $details['board_id'] }}"
	data-widget="post"
	data-post_id="{{ $details['post_id'] }}"
	data-board_uri="{{ $details['board_uri'] }}"
	data-board_id="{{ $details['board_id'] }}"
	data-created-at="{{ $post->created_at->timestamp }}"
	data-updated-at="{{ $post->updated_at->timestamp }}"
	data-capcode="{{ $details['capcode_capcode'] ?: '' }}"
>
	{{-- The intraboard crown applied to posts in Overboard. --}}
	@if ($multiboard && !$reply_to)
	@include('content.board.crown', [
		'board'  => $post->board,
	])
	@endif
	
	<div class="post-interior">
		@if ($post->getRelation('reports'))
		@include('content.board.post.single', [
			'board'   => $board,
			'post'    => $post,
			'catalog' => isset($catalog) ? !!$catalog : false,
		])
		
		{{-- Each condition for an item must also be supplied as a condition so the <ul> doesn't appear inappropriately. --}}
		@if ($preview || $post->bans->count() || !is_null($post->updated_by))
		<ul class="post-metas">
			@if ($preview)
			<li class="post-meta meta-see_more">@lang('board.preview_see_more', [
				'url' => $post->getURL(),
			])</li>
			@endif
			
			@if ($post->bans->count())
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
			
			@if (!is_null($post->updated_by))
			<li class="post-meta meta-updated_by">
				<i class="fa fa-pencil"></i> @lang('board.meta.updated_by', [
					'name' => $details['updated_by_username'],
					'time' => $post->updated_at
				])
			</li>
			@endif
		</ul>
		@endif
		@endif
	</div>
</div>
