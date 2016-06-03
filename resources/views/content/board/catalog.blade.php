@set('details',     $post->getAttributes())
@set('catalog',     isset($catalog) && $catalog ? true : false)
@set('multiboard',  isset($multiboard) ? $multiboard : false)
@set('preview',     isset($preview)    ? $preview    : (!isset($updater) || !$updater) && $post->body_too_long )
@set('reply_to',    isset($reply_to) && $reply_to ? $reply_to : false)
<div class="post-container op-container">
    @include('content.board.post.single', [
        'board'   => $board,
        'post'    => $post,
        'catalog' => true,
    ])
</div>
