@if ($preview)
	<blockquote class="post post-preview ugc" dir="{{ $post->getBodyDirection() }}">
	{!! $post->getBodyPreview() !!}
</blockquote>
@elseif ($post->hasBody())
<blockquote class="post ugc" dir="{{ $post->getBodyDirection() }}">
	{!! $post->getBodyFormatted() !!}
</blockquote>
@endif
