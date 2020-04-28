{{-- https://ogp.me/ --}}
@section('opengraph')
<meta property="og:title" content="{{ $thread->subject ?: "/{$board->board_uri}/ - {$board->title}" }}" />
<meta property="og:description" content="{{ $thread->getBodyExcerpt('256') }}" />
<meta property="og:url" content="{{ url()->current() }}" />
<meta name="twitter:card" content="summary" />

@set('ogHasImage', false)
@foreach($thread->attachments as $attachment)
    @if (!$attachment->isDeleted() && $attachment->thumbnail)
        @set('ogHasImage', true)
        <meta property="og:image" content="{{ url($attachment->getThumbnailUrl()) }}" />
        @break
    @endif
@endforeach
@if(!$ogHasImage)
    <meta property="og:image" content="{{ asset('static/img/logo.png') }}" />
@endif
@stop
