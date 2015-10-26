<section id="site-recent-images" class="grid-50">
	<div class="smooth-box">
		<h2>Recent Uploads</h2>
		<ul class="recent-images selfclear">
			@foreach (App\FileAttachment::getRecentImages(20, true) as $file)
				@if ($file->storage->hasThumb())
				<li class="recent-image">
					<a class="recent-image-link" href="{{ $file->post->getURL() }}">
						{!! $file->storage->getThumbnailHTML($file->post->board) !!}
					</a>
				</li>
				@endif
			@endforeach
		</ul>
	</div>
</section>