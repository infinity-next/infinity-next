@if ($featured && $featured instanceof \App\Post)
<section id="site-featured-post" class="grid-100">
	<div class="smooth-box">
		<h2 class="index-title">@lang('index.title.featured_post')</h2>
		
		@include('content.board.post', [
			'board' => $featured->board,
			'post'  => $featured,
		])
	</div>
</section>
@endif