@if (count($post->attachments))
<ul class="post-attachments attachment-count-{{ count($post->attachments) }} @if(count($post->attachments) > 1) attachments-multi @else attachments-single @endif">
	@foreach ($post->attachments as $attachment)
	<li class="post-attachment">
		@if (!isset($catalog) || !$catalog)
		<div class="attachment-container">
			<a class="attachment-link"
				target="_blank"
				href="{!! $attachment->getDownloadURL($board) !!}"
				data-download-url="{!! $attachment->getDownloadURL($board) !!}"
				data-thumb-url="{!! $attachment->getThumbnailURL($board) !!}"
			>
				<figure class="attachment attachment-type-{{ $attachment->guessExtension() }}">
					{!! $attachment->getThumbnailHTML($board) !!}
					
					<figcaption class="attachment-details">
						<p class="attachment-detail">
							<span class="detail-item detail-filesize">({{ $attachment->getHumanFilesize() }})</span>
							
							@if ($attachment->pivot->is_spoiler)
							<span class="detail-item detail-filename filename-spoilers">@lang('board.field.spoilers')</span>
							@else
							<span class="detail-item detail-filename filename-cleartext">{{ $attachment->pivot->filename }}</span>
							@endif
						</p>
					</figcaption>
				</figure>
			</a>
		</div>
		@else
		<a href="{!! $post->getURL() !!}" data-instant>
			<figure class="attachment attachment-type-{{ $attachment->guessExtension() }}">
				{!! $attachment->getThumbnailHTML($board) !!}
			</figure>
		</a>
		@endif
	</li>
	@endforeach
</ul>
@endif