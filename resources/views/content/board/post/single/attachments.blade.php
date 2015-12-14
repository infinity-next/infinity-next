@if ($post->attachments->count())
@spaceless
<ul class="post-attachments attachment-count-{{ $post->attachments->count() }} {{ $post->attachments->count() > 1 ? "attachments-multi" : "attachments-single" }}">
	
	@if ($post->attachments->count() > 1)
	<li class="attachment-actions">
		<span class="attachment-action attachment-spoiler-all" title="@lang('board.field.spoiler-all')">
			<i class="fa fa-question"></i>&nbsp;@lang('board.field.spoiler-all')
		</span>
		<span class="attachment-action attachment-remove-all" title="@lang('board.field.remove-all')">
			<i class="fa fa-remove"></i>&nbsp;@lang('board.field.remove-all')
		</span>
		<span class="attachment-action attachment-download-all" title="@lang('board.field.download-all')">
			<i class="fa fa-download"></i>&nbsp;@lang('board.field.download-all')
		</span>
		<span class="attachment-action attachment-expand-all" title="@lang('board.field.expand-all')">
			<i class="fa fa-search-plus"></i>&nbsp;@lang('board.field.expand-all')
		</span>
		<span class="attachment-action attachment-collapse-all" title="@lang('board.field.collapse-all')">
			<i class="fa fa-search-minus"></i>&nbsp;@lang('board.field.collapse-all')
		</span>
	</li>
	@endif
	
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
			
			<div class="attachment-action-group">
				<a class="attachment-action attachment-download" target="_blank" href="{!! $attachment->getDownloadURL($board) . "?disposition=attachment" !!}" download="{!! $attachment->getDownloadName() !!}">
					<i class="fa fa-download"></i>
					<span class="detail-item detail-download">@lang('board.field.download')</span>
					<span class="detail-item detail-filesize">{{ $attachment->getHumanFilesize() }}</span>
					<span class="detail-item detail-filedim" title="{{ $attachment->getFileDimensions() }}">{{ $attachment->getFileDimensions() }}</span>
				</a>
			</div>
			
			<div class="attachment-action-group">
				<span class="attachment-action attachment-spoiler" title="@lang('board.field.spoiler')">
					<i class="fa fa-question"></i>&nbsp;@lang('board.field.spoiler')
				</span>
				
				<span class="attachment-action attachment-remove" title="@lang('board.field.remove')">
					<i class="fa fa-remove"></i>&nbsp;@lang('board.field.remove')
				</span>
			</div>
		</div>
		@else
		<a href="{!! $post->getURL() !!}" data-instant>
			<figure class="attachment attachment-type-{{ $attachment->guessExtension() }}" data-widget="lazyimg">
				{!! $attachment->getThumbnailHTML($board, 150) !!}
			</figure>
		</a>
		@endif
	</li>
	@endforeach
</ul>
@endspaceless
@endif
