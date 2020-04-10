@foreach($post->getAllowedBacklinks($board) as $cite)
{!! $cite->getBacklinkHtml($board, $post) !!}
@endforeach
