@spaceless
@if ($post->hasBody())
<blockquote class="post ugc attachment-count-{{ count($post->attachments) }} {{ count($post->attachments) > 1 ? "attachments-multi" : count($post->attachments) > 0 ? "attachments-single" : "attachments-none" }}">
	{!! $post->getBodyFormatted() !!}
</blockquote>
@endif
@endspaceless