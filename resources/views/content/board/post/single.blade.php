@include('content.board.post.single.actions')

<div class="post-content @if ($post->capcode_capcode > 0) capcode-{{{ $post->capcode_role }}} @endif">
	<a name="{!! $post->board_id !!}"></a>
	<a name="reply-{!! $post->board_id !!}"></a>
	
	@if (isset($catalog) && $catalog === true)
		@include('content.board.post.single.attachments')
		@include('content.board.post.single.details')
		@include('content.board.post.single.post')
	@else
		@include('content.board.post.single.details')
		@include('content.board.post.single.attachments')
		@include('content.board.post.single.post')
	@endif
</div>