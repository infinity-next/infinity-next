@foreach($post->getAllowedBacklinks($board) as $cite)
{!! $cite->getBacklinkHTML($board, $post) !!}
@endforeach
