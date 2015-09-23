@extends('layouts.main.panel')

@section('body')
	@if (count($boards))
	<table>
		<thead>
			<tr>
				<th>Board</th>
				<th>Assets</th>
				<th>Staff</th>
				<th>Owned By</th>
				<th>Created By</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($boards as $board)
			<tr>
				<td><a href="{{{ url("/cp/board/{$board->board_uri}/{$suffix}") }}}">/{{{ $board->board_uri }}}/</a></td>
				<td>{{{
					Lang::choice(
						"panel.field.assets_count",
						count($board->assets),
						[
							"count" => count($board->assets),
						]
					)
				}}}</td>
				<td>{{{
					Lang::choice(
						"panel.field.staff_count",
						count($board->staffAssignments),
						[
							"count" => count($board->staffAssignments),
						]
					)
				}}}</td>
				<td><a href="{{{ url("/cp/user/{$board->operated_by_username}.{$board->operated_by}/") }}}">{{{ $board->operated_by_username }}}</a></td>
				<td><a href="{{{ url("/cp/user/{$board->created_by_username}.{$board->created_by}/") }}}">{{{ $board->created_by_username }}}</a></td>
			</tr>
			@endforeach
		</tbody>
	</table>
	@endif
@endsection