@extends('layouts.main.panel')

@section('body')
<main id="banned">
	@if (!$ban->isExpired() || $seeing)
		@section('title', trans('panel.title.you_are_banned'))
		
		@if (!$board || $board->getBannedImages()->count() == 0)
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
		
		<blockquote><p><strong>{!! e($ban->justification) ?: "<em>" . trans('panel.bans.ban_review.no_reason') . "</em>" !!}</strong><p></blockquote>
		
		<p>{!! is_null($ban->expires_at) ? trans('panel.bans.ban_review.expires_no', [
			'start' => $ban->created_at,
		]) : trans('panel.bans.ban_review.expires_at', [
			'start' => $ban->created_at,
			'end'   => $ban->expires_at,
			'diff'  => $ban->expires_at->diffForHumans()
		]) !!}</p>
		
		<p>@lang('panel.bans.ban_review.identity', [ 'ip' => \Request::ip() ])</p>
		
		@if (!$ban->isExpired())
			@if ($ban->willExpire() && $ban->expires_at->diffInDays() < 7)
				<p>@lang('panel.bans.ban_review.appeal_at')</p>
			@elseif (!$ban->canAppeal())
				@if ($appeal = $ban->getAppeal())
					@if (is_null($appeal->approved))
					<p>@lang('panel.bans.ban_review.appeal_pending', [
						'date' => $appeal->created_at,
						'diff' => $appeal->created_at->diffForHumans(),
					])</p>
					@elseif ($appeal->approved)
					<p>@lang('panel.bans.ban_review.appeal_yes')</p>
					@else
					<p>@lang('panel.bans.ban_review.appeal_no')</p>
					@endif
				@else
				<p>@lang('panel.bans.ban_review.appeal_never')</p>
				@endif
			@else
				<p>@lang('panel.bans.ban_review.appeal_now')</p>
				
				{!! Form::open([
					'url'    => Request::url(),
					'method' => "PUT",
					'files'  => true,
					'id'     => "submit-ban-appeal",
				]) !!}
					@include('widgets.messages')
					
					<div class="ban-appeal-text">
						{!! Form::textarea('appeal_text', "", [
							'maxlength' => 2048,
							'rows'      => 5,
						]) !!}
					</div>
					
					<div class="ban-appeal-submit">
						<button type="submit">@lang('panel.bans.ban_review.appeal_submit')</button>
					</div>
				{!! Form::close() !!}
			@endif
		@else
			<p>@lang('panel.bans.ban_review.seeing')</p>
		@endif
		
		{{-- Decided against this. Maybe later.
		@if ($ban->appeals->count())
		<ul class="ban-appeals">
			@foreach ($ban->appeals as $appeal)
			<li class="ban-appeal">
				@include('content.panel.board.appeal'. [ 'appeal' => $appeal ])
			</li>
			@endforeach
		</ul>
		@endif
		--}}
	@else
		@lang('panel.bans.ban_review.expired')
	@endif
</main>
@endsection