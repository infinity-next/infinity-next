<ul class="post-details">
	<li class="post-detail post-subject"><h3 class="subject">{{{ $thread->subject }}}</h3></li>
	<li class="post-detail post-author"><strong class="author">{{{ $thread->author }}}</strong></li>
	<li class="post-detail post-postedon"><time class="postedon">{{{ $thread->created_at }}}</time></li>
	<li class="post-detail post-authorid"><span class="authorid">NEED_TO_ADD</span></li>
	<li class="post-detail post-id"><a href="#" class="postid">No.</a><a href="#" class="postid">{{{ $thread->board_id }}}</a></li>
</ul>

<blockquote class="post ugc">
	<p>{{{ $thread->body }}}</p>
</blockquote>

@if (isset($posts[ $thread->id ]))
<ul class="thread-replies">
	@foreach ($posts[ $thread->id ] as $thread)
	<li class="thread-reply">
		<article class="reply">
			@include('content.post', [ 'thread' => $thread, 'posts' => $posts ])
		</article>
	</li>
	@endforeach
</ul>
@endif