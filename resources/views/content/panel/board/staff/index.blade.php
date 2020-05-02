@extends('layouts.main.panel')

@section('title', trans("panel.title.board_staff_list", [
    'board_uri' => $board->board_uri,
]))

@section('actions')
    <a class="panel-action" href="{{ $board->getPanelUrl('staff.create') }}">+ @lang('panel.action.add_staff')</a>
@endsection

@section('body')
    <div class="filterlist">
        <h4 class="filterlist-heading">@lang('panel.list.head.staff')</h4>

        <ol class="filterlist-list">
            @foreach ($staff as $member)
            <li class="filterlist-item">
                @can('editStaff', [$board, $member])<a class="filterlist-secondary" href="{{ $member->getBoardStaffUrl($board, 'delete') }}"><i class="fas fa-trash"></i></a>@endcan
                <a class="filterlist-secondary" href="{{ $member->getUrl() }}">@lang('panel.list.field.userinfo')</a>
                <a class="filterlist-primary" href="{{ $member->getBoardStaffUrl($board, 'update') }}">
                    <em>{{ $member->getDisplayName() }}</em>
                    <dfn>{{ $member->roles->map(function($item) { return $item->getDisplayName(); })->implode(", ") }}</dfn>
                </a>
            </li>
            @endforeach
        </ol>
    </div>
@endsection
