@if ($preview)
<blockquote class="post post-preview ugc">
	{!! $post->getBodyPreview() !!}
</blockquote>
@elseif ($post->hasBody())
<blockquote class="post ugc">
	{!! $post->getBodyFormatted() !!}
</blockquote>
@endif