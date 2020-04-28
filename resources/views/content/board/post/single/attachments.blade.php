@if ($post->attachments->count())
@spaceless
<div class="post-attachments attachment-count-{{ $post->attachments->count() }} {{ $post->attachments->count() > 1 ? "attachments-multi" : "attachments-single" }}">
    @foreach ($post->attachments as $attachment)
    @if (!isset($catalog) || !$catalog)
    @if ($attachment->isDeleted())
    <div class="attachment attachment-deleted">
        {!! $attachment->toHtml() !!}
    </div>
    @else
    <div class="attachment attachment-type-{{ $attachment->file->guessExtension() }} {{ $attachment->file->getHtmlClasses() }}" data-widget="attachment" data-file="{{ $attachment->file_id }}" data-thumbnail="{{ $attachment->thumbnail_id }}">
        <a class="attachment-link" target="_new" href="{!! $attachment->getUrl() !!}" data-download-url="{!! $attachment->getUrl() !!}" data-thumb-url="{!! $attachment->getThumbnailUrl() !!}">
            {!! $attachment->toHtml() !!}
        </a>

        <div class="attachment-details attachment-actions">
            <div class="actions-anchor actions-attachment" data-no-instant>
                <span class="actions-label"><i class="fas fa-angle-down"></i></span>
                <div class="actions">
                    <div class="action">
                        <a href="{{ $attachment->getUnspoilerUrl($board) }}" target="_blank" class="action-link attachment-unspoiler" title="@lang('board.field.unspoiler')">
                            <i class="fas fa-question"></i>&nbsp;@lang('board.field.unspoiler')
                        </a>
                    </div>
                    <div class="action">
                        <a href="{{ $attachment->getSpoilerUrl($board) }}" target="_blank" class="action-link attachment-spoiler" title="@lang('board.field.spoiler')">
                            <i class="fas fa-question"></i>&nbsp;@lang('board.field.spoiler')
                        </a>
                    </div>
                    <div class="action">
                        <a href="{{ $attachment->getRemoveUrl($board) }}" target="_blank" class="action-link attachment-remove" title="@lang('board.field.remove')">
                            <i class="fas fa-remove"></i>&nbsp;@lang('board.field.remove')
                        </a>
                    </div>
                    <div class="action">
                        <a href="{{ route('panel.site.files.show', $attachment->file->hash) }}" target="_blank" class="action-link attachment-moderate" title="@lang('board.action.moderate')">
                            <i class="fas fa-exclamation-triangle"></i>&nbsp;@lang('board.action.moderate')
                        </a>
                    </div>
                </div>
            </div>

            {{-- Note: Strict LTR direct here because this is technical info. --}}
            <a class="attachment-action attachment-download" dir="ltr" target="_blank" href="{!! $attachment->getUrl($board) . "?disposition=attachment" !!}" download="{!! $attachment->getDownloadName() !!}">
                <span class="detail-item detail-download">
                    @if ($attachment->is_spoiler)
                    <span class="detail-item detail-filename filename-spoilers">@lang('board.field.spoilers')</span>
                    @else
                    <span class="detail-item detail-filename filename-cleartext" title="{{ $attachment->filename }}">{{ $attachment->getShortFilename() }}</span>
                    @endif
                </span>
                <br />
                <span class="detail-item detail-filesize">{{ $attachment->file->getHumanFilesize() }}</span>
                <span class="detail-item detail-filedim" title="{{ $attachment->file->getFileDimensions() }}">{{ $attachment->file->getFileDimensions() }}</span>
            </a>
        </div>
    </div>
    @endif
    @else
    <div class="attachment attachment-type-{{ $attachment->file->guessExtension() }}" data-widget="attachment" data-file="{{ $attachment->file_id }}" data-thumbnail="{{ $attachment->thumbnail_id }}">
        <a class="attachment-link" target="_new" href="{!! $post->getUrl() !!}" data-instant data-download-url="{!! $attachment->getUrl() !!}" data-thumb-url="{!! $attachment->getThumbnailUrl() !!}">
            {!! $attachment->toHtml('auto') !!}
        </a>
    </div>
    @endif
    @endforeach
</div>
@endspaceless
@endif
