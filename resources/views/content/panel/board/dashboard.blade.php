@extends('layouts.main.panel')

@section('body')
	@if (count($boards))
	<table>
		<thead>
			<tr>
				<th>Board</th>
				<th>Staff</th>
				<th>Owned By</th>
				<th>Created By</th>
				<th>Total Posts</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($boards as $board)
			{{ dd($board->staffAssignments) }}
			<tr>
				<td><a href="{{{ url("/cp/board/{$board->board_uri}") }}}">/{{{ $board->board_uri }}}/</a></td>
				<td><a href="{{{ url("/cp/board/{$board->board_uri}/staff") }}}">{{{
					Lang::choice(
						'panel.field.staff_count',
						count($board->staffAssignments),
						[
							"staff_count" => count($board->staffAssignments),
						]
					)
				}}}</a></td>
				<td><a href="{{{ url("/cp/user/{$board->operated_by_username}.{$board->operated_by}/") }}}">{{{ $board->operated_by_username }}}</a></td>
				<td><a href="{{{ url("/cp/user/{$board->created_by_username}.{$board->created_by}/") }}}">{{{ $board->created_by_username }}}</a></td>
				<td>{{{ $board->posts_total }}}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	@endif
@endsection