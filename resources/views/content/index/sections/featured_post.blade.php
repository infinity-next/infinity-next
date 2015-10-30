@if ($featured && $featured instanceof \App\Post)
<section id="site-featured-post" class="grid-100">
	<div class="smooth-box">
		<h2>@lang('index.title.featured_post')</h2>
		
		@include('content.board.thread', [
			'board'   => $featured->board,
			'thread'  => $featured,
			'op'      => false,
		])
	</div>
</section>
@endif