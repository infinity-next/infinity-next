@extends('layouts.main.panel')

@section('body')
<main id="banned">
	@if (count($bans))
		@section('title', trans('panel.title.you_are_banned'))
		
		<figure class="error-figure">
			<img src="{{ asset("static/img/errors/banned.gif") }}" class="error-image" />
			<figcaption class="error-caption"><a class="error-credit" href="https://twitter.com/kr0npr1nz">Ilya Kuvshinov @kr0npr1nz</a></figcaption>
		</figure>
		
		@lang('panel.bans.ban_list_desc')
		
		<table>
			<thead>
				<tr>
					<th>@lang('panel.bans.table.board')</th>
					<th>@lang('panel.bans.table.ban_ip')</th>
					<th>@lang('panel.bans.table.ban_appeal')</th>
					<th>@lang('panel.bans.table.ban_user')</th>
					<th>@lang('panel.bans.table.ban_placed')</th>
					<th>@lang('panel.bans.table.ban_expire')</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($bans as $ban)
				<tr>
					<td>@if (is_null($ban->board_uri))<strong>@lang('panel.bans.ban_global')</strong>@else/{{$ban->board_uri}}/@endif</td>
					<td>{{ $ban->ban_ip }}</td>
					<td><a href="{!! $ban->getAppealUrl() !!}">@lang('panel.bans.appeal_open')</a></td>
					<td>{!! $ban->mod->getUsernameHTML() !!}</td>
					<td>{{ $ban->created_at }}</td>
					<td>{{ $ban->expires_at }}</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	@else
		@section('title', trans('panel.title.you_are_not_banned'))
		
		@lang('panel.bans.ban_list_empty')
	@endif
</main>
@endsection