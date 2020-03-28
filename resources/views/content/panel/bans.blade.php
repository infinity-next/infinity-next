@extends('layouts.main.panel')

@section('body')
<main id="banned">
    @if (count($bans) || !$clientOnly)
        @if ($clientOnly)
            @section('title', trans('panel.title.you_are_banned'))

            <figure class="error-figure">
                <img src="{{ asset("static/img/errors/banned.gif") }}" class="error-image" />
                <figcaption class="error-caption"><a class="error-credit" href="https://twitter.com/Kuvshinov_Ilya">Ilya Kuvshinov</a></figcaption>
            </figure>

            @lang('panel.bans.ban_list_desc')
        @else
            @section('title', trans('panel.title.bans_public'))
        @endif

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
                <tr class="@if ($ban->isExpired()) row-inactive @endif">
                    <td>@if (is_null($ban->board_uri))<strong>@lang('panel.bans.ban_global')</strong>@else/{{$ban->board_uri}}/@endif</td>
                    <td>{{ $ban->ban_ip->toTextForUser() }}</td>
                    <td>
                        @if (!$ban->isExpired())
                            <a href="{!! $ban->getAppealUrl() !!}">@lang( $ban->canAppeal() ? 'panel.bans.appeal_open' : 'panel.bans.appeal_closed')</a>
                        @else
                            @lang( 'panel.bans.appeal_expired' )
                        @endif
                    </td>
                    <td>{!! isset($ban->mod) ? $ban->mod->getUsernameHTML() : "" !!}</td>
                    <td>{{ $ban->created_at }}</td>
                    <td>{{ $ban->expires_at }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @include('nav.paginator', [
            'paginator' => $bans,
        ])
    @else
        @section('title', trans('panel.title.you_are_not_banned'))

        @lang('panel.bans.ban_list_empty')
    @endif
</main>
@endsection
