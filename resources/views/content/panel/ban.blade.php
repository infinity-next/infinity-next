@extends('layouts.main.panel')

@section('body')
<main id="banned">
	@section('title', trans('panel.title.you_are_banned'))
	
	@if (!$board || !$board->getBannedImages())
	<figure class="error-figure">
		<img src="{{ asset("static/img/errors/banned.gif") }}" class="error-image" />
		<figcaption class="error-caption"><a class="error-credit" href="https://twitter.com/kr0npr1nz">Ilya Kuvshinov @kr0npr1nz</a></figcaption>
	</figure>
	@else
	<figure class="error-figure">
		{!! $board->getBannedImages()->random()->asHTML() !!}
	</figure>
	@endif
	
	<p>{!! $board ? trans('panel.bans.ban_review.banned_from', [ 'board_uri' => $board->board_uri ]) : trans('panel.bans.ban_review.banned_all') !!}</p>
	
	<blockquote><p><strong>{!! $ban->justification ?: "<em>" . trans('panel.bans.ban_review.no_reason') . "</em>" !!}</strong><p></blockquote>
	
	@if (!$ban->isExpired())
		<p>{!! is_null($ban->expires_at) ? trans('panel.bans.ban_review.expires_no', [
			'start' => $ban->created_at,
		]) : trans('panel.bans.ban_review.expires_at', [
			'start' => $ban->created_at,
			'end'   => $ban->expires_at,
			'diff'  => $ban->expires_at->diffForHumans()
		]) !!}</p>
		
		<p>@lang('panel.bans.ban_review.identity', [ 'ip' => \Request::ip() ])</p>
	@else
	
	@endif
</main>
@endsection