<section id="site-recent-posts">
	<div class="smooth-box">
		<h2>Recent Posts</h2>
		<div class="grid-container">
			<ul class="recent-posts">
				@foreach (App\Post::getRecentPosts() as $post)
					<li class="recent-post grid-25">
						<a class="recent-post-link" href="{{ $post->getURL() }}"></a>
						<blockquote class="post ugc">
							{!! $post->getBodyFormatted() !!}
						</blockquote>
					</li>
				@endforeach
			</ul>
		</div>
	</div>
</section>