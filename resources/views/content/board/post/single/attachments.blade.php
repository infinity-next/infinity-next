@if (count($post->attachments))
<ul class="post-attachments attachment-count-{{ count($post->attachments) }} @if(count($post->attachments) > 1) attachments-multi @else attachments-single @endif">
	@foreach ($post->attachments as $attachment)
	<li class="post-attachment">
		<figure class="attachment">
			@if (!isset($catalog) || !$catalog)
			<a class="attachment-link" target="_blank" href="{!! $attachment->getDownloadURL($board) !!}">
				<figcaption class="attachment-details">
					<p class="attachment-detail detail-filename">{{ $attachment->pivot->filename }}</p>
					<p class="attachment-detail detail-filetime">{{ $attachment->first_uploaded_at }}</p>
				</figcaption>
				
				<img class="attachment-img" src="{!! $attachment->getThumbnailURL($board) !!}" />
			</a>
			@else
				<img class="attachment-img" src="{!! $attachment->getThumbnailURL($board) !!}" />
			@endif
		</figure>
	</li>
	@endforeach
</ul>
@endif