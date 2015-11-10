@if (count($post->attachments))
@spaceless
<ul class="post-attachments attachment-count-{{ count($post->attachments) }} {{ count($post->attachments) > 1 ? "attachments-multi" : "attachments-single" }}">
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
				<figure class="attachment attachment-type-{{ $attachment->guessExtension() }}" data-widget="lazyimg">
					{!! $attachment->getThumbnailHTML($board) !!}
					
					<figcaption class="attachment-details">
						<p class="attachment-detail">
							<span class="detail-item detail-filesize">({{ $attachment->getHumanFilesize() }})</span>
						</p>
						<p class="attachment-detail">
							@if ($attachment->pivot->is_spoiler)
							<span class="detail-item detail-filename filename-spoilers">@lang('board.field.spoilers')</span>
							@else
							<span class="detail-item detail-filename filename-cleartext" title="{{ $attachment->pivot->filename }}">{{ $attachment->pivot->filename }}</span>
							@endif
						</p>
					</figcaption>
				</figure>
			</a>
		</div>
		@else
		<a href="{!! $post->getURL() !!}" data-instant>
			<figure class="attachment attachment-type-{{ $attachment->guessExtension() }}" data-widget="lazyimg">
				{!! $attachment->getThumbnailHTML($board) !!}
			</figure>
		</a>
		@endif
	</li>
	@endforeach
</ul>
@endspaceless
@endif