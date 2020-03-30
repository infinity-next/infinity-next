@spaceless
@if ($preview)
<blockquote class="post post-preview ugc" {!! $post->getBodyDirectionAttr() !!}>
    {!! $post->getBodyPreview() !!}
</blockquote>
@elseif ($post->hasBody())
<blockquote class="post ugc" {!! $post->getBodyDirectionAttr() !!}>
    {!! $post->getBodyFormatted() !!}
</blockquote>
@endif
@endspaceless
