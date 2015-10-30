@extends('layouts.main.panel')

@section('title', trans('panel.title.appeals'))

@section('body')
<section class="appeals">
	@if (count($appeals))
		<table>
			<colgroup>
				<col />
				<col />
				<col />
				<col />
				<col />
				<col />
				<col style="width: 200px;" />
			</colgroup>
			<thead>
				<tr>
					<th>@lang('panel.bans.table.board')</th>
					<th>@lang('panel.bans.table.ban_ip')</th>
					<th>@lang('panel.bans.table.appeal_text')</th>
					<th>@lang('panel.bans.table.ban_user')</th>
					<th>@lang('panel.bans.table.ban_placed_ago')</th>
					<th>@lang('panel.bans.table.ban_expire_in')</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($appeals as $appeal)
				<tr>
					<td>@if (is_null($appeal->ban->board_uri))<strong>@lang('panel.bans.ban_global')</strong>@else/{{$appeal->ban->board_uri}}/@endif</td>
					<td>{{ $appeal->ban->ban_ip }}</td>
					<td><em>{{ $appeal->appeal_text }}</em></td>
					<td>{!! $appeal->ban->mod->getUsernameHTML() !!}</td>
					<td><time title="{{ $appeal->ban->created_at }}" datetime="{{ $appeal->ban->created_at->timestamp}}">{{ $appeal->ban->created_at->diffForHumans() }}</time></td>
					<td><time title="{{ $appeal->ban->expires_at }}" datetime="{{ $appeal->ban->expires_at->timestamp}}">{{ $appeal->ban->expires_at->diffForHumans() }}</time></td>
					<td>
						<button class="approve" type="submit" name="approve" value="{{ $appeal->appeal_id }}">@lang('panel.approve')</button>&nbsp;
						<button class="reject" type="submit" name="reject" value="{{ $appeal->appeal_id }}">@lang('panel.reject')</button>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	@else
		<p>@lang('panel.appeals.empty')
	@endif
</section>
@stop