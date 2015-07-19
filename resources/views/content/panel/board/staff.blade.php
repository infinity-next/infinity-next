@extends('layouts.main.panel')

@section('title', trans("panel.title.board_staff_list", [
	'board_uri' => $board->board_uri,
]))

@section('body')
	@if (count($staff))
	<table>
		<tbody>
			@foreach ($staff as $user)
			<tr>
				<td><a href="{{{ url("/cp/board/{$board->board_uri}/staff/{$user->username}.{$user->user_id}") }}}">{{{ $user->username }}}</a></td>
				<td><a href="{{{ url("/cp/user/{$user->username}.{$user->user_id}") }}}">User Details</a></td>
			</tr>
			@endforeach
		</tbody>
	</table>
	@endif
@endsection