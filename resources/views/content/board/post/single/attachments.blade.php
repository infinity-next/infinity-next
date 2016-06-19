@if ($post->attachments->count())
@spaceless
<ul class="post-attachments attachment-count-{{ $post->attachments->count() }} {{ $post->attachments->count() > 1 ? "attachments-multi" : "attachments-single" }}">
	@foreach ($post->attachments as $attachment)
	<li class="post-attachment">
		@if (!isset($catalog) || !$catalog)
		<div class="attachment-container">
			@if ($attachment->isDeleted())
			<figure class="attachment attachment-deleted">
				{!! $attachment->getThumbnailHTML($board) !!}
			</figure>
			@else
			<a class="attachment-link"
				target="_blank"
				href="{!! $attachment->getDownloadURL($board) !!}"
				data-download-url="{!! $attachment->getDownloadURL($board) !!}"
				data-thumb-url="{!! $attachment->getThumbnailURL($board) !!}"
			>
				<figure class="attachment attachment-type-{{ $attachment->guessExtension() }} {{ $attachment->getThumbnailClasses() }}" data-widget="lazyimg">
					<figcaption class="attachment-details">
						<p class="attachment-detail">
							@if ($attachment->pivot->is_spoiler)
							<span class="detail-item detail-filename filename-spoilers">@lang('board.field.spoilers')</span>
							@else
							<span class="detail-item detail-filename filename-cleartext" title="{{ $attachment->pivot->filename }}">{{ $attachment->getShortFilename() }}</span>
							<span class="detail-item detail-close">&nbsp;[@lang('board.action.close')]</span>
							@endif
						</p>
					</figcaption>

					{!! $attachment->getThumbnailHTML($board) !!}
				</figure>
			</a>
			@endif
		</div>

		<div class="attachment-action-group">
			<div class="post-action-bar action-bar-attachments">
				@section('attachment-actions')
				@set('attachmentActions', false)
				<div class="post-action-tab action-tab-actions" data-no-instant>
					<span class="post-action-label post-action-open"><span class="post-action-text">@lang('board.action.open')</span></span>
					<ul class="post-action-groups">
						<li class="post-action-group">
							<ul class="post-actions">
								@set('attachmentActions', true)
								@if ($attachment->isSpoiler())
								<li class="post-action">
									<a href="{{ $attachment->getUnspoilerURL($board) }}" target="_blank" class="post-action-link attachment-unspoiler" title="@lang('board.field.unspoiler')" data-no-instant>
										<i class="fa fa-question"></i>&nbsp;@lang('board.field.unspoiler')
									</a>
								</li>
								@else
								<li class="post-action">
									<a href="{{ $attachment->getSpoilerURL($board) }}" target="_blank" class="post-action-link attachment-spoiler" title="@lang('board.field.spoiler')" data-no-instant>
										<i class="fa fa-question"></i>&nbsp;@lang('board.field.spoiler')
									</a>
								</li>
								@endif

								<li class="post-action">
									<a href="{{ $attachment->getRemoveURL($board) }}" target="_blank" class="post-action-link attachment-remove" title="@lang('board.field.remove')" data-no-instant>
										<i class="fa fa-remove"></i>&nbsp;@lang('board.field.remove')
									</a>
								</li>
							</ul>
						</li>
					</ul>
				</div>
				@show
			</div>

			{{-- Note: Strict LTR direct here because thsi is technical info. --}}
			<a class="attachment-action attachment-download" dir="ltr" target="_blank"
				href="{!! $attachment->getDownloadURL($board) . "?disposition=attachment" !!}"
				download="{!! $attachment->getDownloadName() !!}"
			>
				<span class="detail-item detail-download">@lang('board.field.download')</span>
				<span class="detail-item detail-filesize">{{ $attachment->getHumanFilesize() }}</span>
				<span class="detail-item detail-filedim" title="{{ $attachment->getFileDimensions() }}">{{ $attachment->getFileDimensions() }}</span>
			</a>
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
