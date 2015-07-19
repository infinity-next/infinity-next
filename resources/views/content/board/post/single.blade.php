<div class="post-content @if ($post->capcode_capcode > 0) capcode-{{{ $post->capcode_role }}} @endif">
	<a name="{!! $post->board_id !!}"></a>
	
	@if ($catalog)
		<a class="post-open"  href="{{ url("/{$board->board_uri}/thread/{$post->board_id}") }}">Open</a>
		
		@include($c->template('board.post.single.attachments'))
		@include($c->template('board.post.single.details'))
	@else
		@include($c->template('board.post.single.details'))
		@include($c->template('board.post.single.attachments'))
	@endif
	
	@include($c->template('board.post.single.post'))
</div>