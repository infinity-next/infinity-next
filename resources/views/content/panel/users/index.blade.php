@extends('layouts.main.panel')

@section('body')
<div class="filterlist">
    <h4 class="filterlist-heading">@lang('panel.list.head.users')</h4>

    <ol class="filterlist-list">
        @foreach ($users as $item)
        <li class="filterlist-item">
            <a class="filterlist-primary"
                href="{{ $item->getUrl() }}">
                <em>{{ $item->getDisplayName() }}</em>
            </a>
        </li>
        @endforeach
    </ol>
</div>
@endsection
