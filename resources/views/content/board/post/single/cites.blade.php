@foreach($post->getAllowedBacklinks() as $cite)
{!! $cite->getBacklinkHTML($board) !!}
@endforeach