<div class="post-content @if ($post->capcode_capcode > 0) capcode-{{{ $post->capcode_role }}} @endif">
	<a name="{!! $post->board_id !!}"></a>
	
	@include($c->template('board.post.single.actions'))
	
	@if (isset($catalog) && $catalog === true)
	<a class="post-open" href="{{ $post->getURL() }}">
		@include($c->template('board.post.single.attachments'))
		@include($c->template('board.post.single.details'))
		@include($c->template('board.post.single.post'))
	</a>
	@else
		@include($c->template('board.post.single.details'))
		@include($c->template('board.post.single.attachments'))
		@include($c->template('board.post.single.post'))
	@endif
</div>