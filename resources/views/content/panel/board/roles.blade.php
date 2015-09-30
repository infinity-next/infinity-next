@extends('layouts.main.panel')

@section('title', trans("panel.title.board_role_list", [
	'board_uri' => $board->board_uri,
]))

@section('actions')
	<a class="panel-action" href="{{ $board->getURLForRole('add') }}">+ @lang('panel.action.add_role')</a>
@endsection

@section('body')
	<div class="filterlist">
		<h4 class="filterlist-heading">@lang('panel.list.head.roles')</h4>
		
		<ol class="filterlist-list">
			@foreach ($roles as $role)
			<li class="filterlist-item">
				<a class="filterlist-primary" href="{{ $role->getPermissionsURLForBoard() }}">
					<em>{{ $role->getDisplayName() }}</em>
					<dfn>{{ $role->getDisplayWeight() }}</dfn>
				</a>
			</li>
			@endforeach
		</ol>
	</div>
@endsection