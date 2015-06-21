@extends('layouts.main.panel')

@section('body')
	@if (count($roles))
	<table>
		<tbody>
			@foreach ($roles as $role)
			<tr>
				<td><a href="{{{ url("/cp/roles/permissions/{$role->role_id}") }}}">{{{ $role->name }}}</a></td>
			</tr>
			@endforeach
		</tbody>
	</table>
	@endif
@endsection