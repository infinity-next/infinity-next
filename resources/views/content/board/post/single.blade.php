{{-- Don't include this directly. Call `content.board.post`. --}}
@include('content.board.post.single.open')

<div class="post-content">
	<a name="{!! $details['board_id'] !!}"></a>
	<a name="reply-{!! $details['board_id'] !!}"></a>
	
	@if (isset($catalog) && $catalog === true)
		@include('content.board.post.single.attachments')
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
