@spaceless
@if ($post->hasBody())
<blockquote class="post ugc">
	{!! $post->getBodyFormatted() !!}
</blockquote>
@endif
@endspaceless