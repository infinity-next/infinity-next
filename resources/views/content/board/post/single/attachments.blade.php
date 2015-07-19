<ul class="post-attachments">
	@foreach ($post->attachments as $attachment)
	<li class="post-attachment">
		<figure class="attachment">
			<a class="attachment-link" target="_new" href="{!! $attachment->getDownloadURL($board) !!}">
				<img class="attachment-img" src="{!! $attachment->getDownloadURL($board) !!}" />
			</a>
		</figure>
	</li>
	@endforeach
</ul>