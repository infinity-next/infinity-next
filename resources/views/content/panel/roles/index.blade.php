@extends('layouts.main.panel')

@section('body')
    <div class="filterlist">
        <h4 class="filterlist-heading">@lang('panel.list.head.roles')</h4>

        <ol class="filterlist-list">
            @foreach ($roles as $role)
            <li class="filterlist-item">
                <a class="filterlist-primary" href="{{ $role->getPanelUrl('permission.index') }}">
                    <em>{{ $role->getDisplayName() }}</em>
                    <dfn>{{ $role->getDisplayWeight() }}</dfn>
                </a>
            </li>
            @endforeach
        </ol>
    </div>
@endsection
