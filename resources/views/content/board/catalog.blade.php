<div class="post-container op-container">
	@include( $c->template('board.post.single'), [
		'board'   => $board,
		'post'    => $thread,
		'catalog' => true,
	])
</div>