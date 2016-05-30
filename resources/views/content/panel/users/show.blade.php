@extends('layouts.main.panel')

@section('body')
<section id="user-profile">
    <figure class="avatar-figure">
        <img id="avatar" src="{{ asset('static/img/assets/anonymous.png') }}" />
    </figure>

    <h2 class="username">{{ $profile->getDisplayName() }}</h2>
</section>

<div class="filterlist">
    <h4 class="filterlist-heading">@lang('panel.list.head.global_roles')</h4>

    <ol class="filterlist-list">
        @foreach ($globalRoles as $role)
        <li class="filterlist-item">
            <a class="filterlist-primary"
                href="{{ $role->getPanelUrl() }}">
                <em>{{ $role->getDisplayName() }}</em>
            </a>
        </li>
        @endforeach
    </ol>
</div>

<div class="filterlist">
    <h4 class="filterlist-heading">@lang('panel.list.head.board_roles')</h4>

    <ol class="filterlist-list">
        @foreach ($localRoles as $role)
        <li class="filterlist-item">
            <a class="filterlist-primary"
                href="{{ $role->getPanelUrl() }}">
                <em>{{ $role->getDisplayName() }}</em>
            </a>
        </li>
        @endforeach
    </ol>
</div>
@endsection
