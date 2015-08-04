@if (count($post->attachments))
<ul class="post-attachments attachment-count-{{ count($post->attachments) }} @if(count($post->attachments) > 1) attachments-multi @else attachments-single @endif">
	@foreach ($post->attachments as $attachment)
	<li class="post-attachment">
		<figure class="attachment attachment-type-{{ $attachment->guessExtension() }}">
			@if (!isset($catalog) || !$catalog)
			<a class="attachment-link"
				target="_blank"
				href="{!! $attachment->getDownloadURL($board) !!}"
				data-download-url="{!! $attachment->getDownloadURL($board) !!}"
				data-thumb-url="{!! $attachment->getThumbnailURL($board) !!}"
			>
				<img class="attachment-img" src="{!! $attachment->getThumbnailURL($board) !!}" />
				
				<figcaption class="attachment-details">
					<p class="attachment-detail">
						<span class="detail-item detail-filesize">({{ $attachment->getHumanFilesize() }})</span>
						<span class="detail-item detail-filename">{{ $attachment->pivot->filename }}</span>
					</p>
					<p class="attachment-detail">
						<span class="detail-item detail-filetime">{{ $attachment->first_uploaded_at }}</span>
					</p>
				</figcaption>
			</a>
			@else
			<a href="{!! $post->getURL() !!}" data-instant>
				<img class="attachment-img" src="{!! $attachment->getThumbnailURL($board) !!}" />
			</a>
			@endif
		</figure>
	</li>
	@endforeach
</ul>
@endif