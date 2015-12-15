{{-- Don't include this directly. Call `content.board.post`. --}}
<div class="post-content">
	<a name="{!! $details['board_id'] !!}"></a>
	<a name="reply-{!! $details['board_id'] !!}"></a>
	
	@if (isset($catalog) && $catalog === true)
		@if ($post->attachments->count() === 0)
			<a class="catalog-open" href="{!! $post->getURL() !!}" data-instant>@lang('board.action.view')</a>
		@else
			@include('content.board.post.single.attachments')
		@endif
		@include('content.board.post.single.details')
		@include('content.board.post.single.post')
	@else
		@include('content.board.post.single.details')
		<div class="post-content-wrapper">
			@include('content.board.post.single.attachments')
			@include('content.board.post.single.post')
		</div>
	@endif
</div>
