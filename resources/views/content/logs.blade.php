@extends('layouts.main')

@section('title', "/{$board->board_uri}/ Staff Logs")
@section('description', $board->descriptios)

	protected $fillable = ['action_name', 'action_details', 'user_id', 'user_ip', 'board_uri'];
	
@section('content')
<main class="board-logs">
	<section class="board-logs-table">
		<table class="logs-table">
			<thead>
				<tr>
					<th>Time</th>
					<th>User</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($logs as $log)
				<tr>
					<td>{{ $log->created_at }}</td>
					<td>{{ $log->user->username }}</td>
					<td>@lang($log->action)</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</section>
</main>
@stop

@section('footer')
	@include('nav.boardpages')
@stop