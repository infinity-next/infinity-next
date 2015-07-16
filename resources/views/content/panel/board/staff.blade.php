@extends('layouts.main.panel')

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