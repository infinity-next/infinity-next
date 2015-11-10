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
				<figure class="attachment attachment-type-{{ $attachment->guessExtension() }} {{ $attachment->getThumbnailClasses() }}" data-widget="lazyimg">
					{!! $attachment->getThumbnailHTML($board) !!}
					
					<figcaption class="attachment-details">
						<p class="attachment-detail">
							@if ($attachment->pivot->is_spoiler)
							<span class="detail-item detail-filename filename-spoilers">@lang('board.field.spoilers')</span>
							@else
							<span class="detail-item detail-filename filename-cleartext" title="{{ $attachment->pivot->filename }}">{{ $attachment->getShortFilename() }}</span>
							@endif
						</p>
					</figcaption>
				</figure>
			</a>
			
			<a class="attachment-download" target="_blank" href="{!! $attachment->getDownloadURL($board) . "?disposition=attachment" !!}">
				<i class="fa fa-download"></i>&nbsp;@lang('board.field.download')&nbsp;<span class="detail-item detail-filesize">({{ $attachment->getHumanFilesize() }})</span>
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