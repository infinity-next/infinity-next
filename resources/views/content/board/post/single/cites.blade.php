@foreach($post->backlinks as $cite)
{!! $cite->getBacklinkHTML($board) !!}
@endforeach