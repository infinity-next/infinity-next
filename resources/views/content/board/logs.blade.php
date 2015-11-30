@extends('layouts.main')

@section('title', trans('board.logs.title', [ 'board_uri' => $board->board_uri ]))

@section('content')
<main class="board-logs">
	<section class="board-logs-table grid-container">
		<div class="smooth-box">
			<table class="logs-table">
				<colgroup>
					<col style="width: 12.5em;" />
					<col style="min-width: 5em;" />
					<col style="width: auto;" />
				</colgrop>
				<thead>
					<tr>
						<th>@lang('board.logs.table.time')</th>
						<th>@lang('board.logs.table.user')</th>
						<th>@lang('board.logs.table.action')</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($logs as $log)
					<tr>
						<td><time datetime="{{ $log->created_at }}">{{ $log->created_at }}</time></td>
						<td>{{ $log->user->getDisplayName() }}</td>
						
						@if ($log->action_details)
						<td>@lang($log->action_name, $log->getDetails($c->user))</td>
						@else
						<td>@lang($log->action_name)</td>
						@endif
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</section>
</main>
@stop