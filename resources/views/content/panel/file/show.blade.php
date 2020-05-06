@extends('layouts.main.panel')

@section('body')
<form method="POST" action="{{ route('panel.site.files.delete', ['hash' => $file->hash]) }}">
    @method('DELETE')
    @csrf
    <button name="action" value="delete">@lang('board.action.delete')</button>
    <button name="action" value="ban">@lang('board.action.ban_delete_global')</button>
    <button name="action" value="fuzzyban">@lang('board.action.fuzzyban')</button>
</form>

<div class="attachment-showcase checkered-background">
    @if ($file->thumbnails->count() > 1)
    <div class="thumbnails">
        @foreach ($file->thumbnails as $thumbnail)
        <div class="thumbnail">{!! $thumbnail->toHtml(100) !!}</div>
        @endforeach
    </div>
    @endif
    <div class="attachment">
        {!! $file->toHtml() !!}
    </div>
</div>

<table>
    <thead>
        <tr>
            <th style="max-width: 14em;"></th>
            <th style="max-width: 8em;"></th>
            <th style="max-width: 8em;"></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($file->posts as $post)
        <tr class="@if ($post->isDeleted()) row-inactive @endif">
            <td>/{{ $post->board_uri }}/</td>
            <td><a href="{{ $post->getUrl() }}">No.{{ $post->board_id }}</a></td>
            <td>{{ $post->created_at->diffForHumans() }}</td>
            <td class="attachments checkered-background">
                @foreach ($post->attachments as $attachment)
                <a class="attachment" href="{{ route('panel.site.files.show', $attachment->file->hash) }}" style="height: 100px; width: 100px;">
                    {!! $attachment->toHtml(100) !!}
                </a>
                @endforeach
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
