@foreach($post->getAllowedBacklinks($board) as $cite)
{!! $cite->getBacklinkHTML($board) !!}
@endforeach