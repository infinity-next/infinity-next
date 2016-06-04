<div class="post-container {{
        is_null($details['reply_to']) ? 'op-container' : 'reply-container'
    }} {{
        $post->hasBody() ? 'has-body' : 'has-no-body'
    }} {{
        $post->attachments->count() > 1 ? 'has-files' : ($post->attachments->count() > 0 ? 'has-file' : 'has-no-file')
    }}"
    id="post-{{ $details['board_uri'] }}-{{ $details['board_id'] }}"
    data-widget="post"
    data-post_id="{{ $details['post_id'] }}"
    data-board_uri="{{ $details['board_uri'] }}"
    data-board_id="{{ $details['board_id'] }}"
    data-created-at="{{ $post->created_at->timestamp }}"
    data-updated-at="{{ $post->updated_at->timestamp }}"
    data-bumped-last="{{ $post->bumped_last->timestamp }}"
    data-capcode="{{ isset($details['capcode_capcode']) ? $details['capcode_capcode'] : '' }}"
>
