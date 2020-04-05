@extends('layouts.main.panel')

@section('body')
<div class="attachments">
    @foreach ($files as $file)
    <a class="attachment" href="{{ route('panel.site.files.show', $file) }}" style="height: 100px; width: 100px;">
        {!! $file->getThumbnailHtml(null, 100) !!}
    </a>
    @endforeach
</div>
@endsection
