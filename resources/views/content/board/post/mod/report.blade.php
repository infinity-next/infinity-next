{!! Form::open([
	'url'    => Request::url(),
	'method' => "POST",
	'id'     => "mod-form",
	'class'  => "form-report smooth-box",
]) !!}
	@include('widgets.messages')
	
	<fieldset class="form-fields">
		<legend class="form-legend">{{ trans("board.legend." . implode($actions,"+"), [ 'board' => "/{$post->board_uri}/" ]) }}</legend>
		
		@if (!$report)
			<blockquote>@lang("board.report." . ($reportGlobal ? "global" : "local"))</blockquote>
			
			@if ($reportText)
			<blockquote class="report-rules ugc">
				<p>@lang("board.report.desc-" . ($reportGlobal ? "global" : "local"))</p>
				
				{!! $reportText !!}
			</blockquote>
			@endif
		@else
		<blockquote>
			@if ($report->is_dismissed)
				@lang('board.report.dismissed')
			@elseif ($report->is_successful)
				@lang('board.report.successful')
			@else
				@lang('board.report.pending')
			@endif
		</blockquote>
		@endif
		
		<div class="field row-reason">
			{!! Form::label(
				"reason",
				trans('board.report.reason'),
				[
					'class' => "field-label",
			]) !!}
			{!! Form::textarea(
				'reason',
				$report ? $report->reason : old('reason'),
				[
					'id'          => "reason",
					'class'       => "field-control",
					'maxlength'   => 1024,
					(!$report ? 'data-enabled' : 'disabled'),
			]) !!}
		</div>
		
		@if (!$report)
		<div class="field row-captcha">
			<label class="field-label" for="captcha">
				{!! captcha() !!}
				<span class="field-validation"></span>
			</label>
			
			{!! Form::text(
				'captcha',
				"",
				[
					'id'          => "captcha",
					'class'       => "field-control",
					'placeholder' => "Security Code",
			]) !!}
		</div>
		@endif
		
		@if (!$user->isAnonymous())
		<div class="field row-associate field-inline">
			{!! Form::checkbox(
				'associate',
				1,
				$report && $report->user_id,
				[
					'id'        => "associate",
					'class'     => "field-control",
					(!$report && !$user->isAnonymous() ? 'data-enabled' : 'disabled'),
			]) !!}
			{!! Form::label(
				'associate',
				trans('board.report.associate'),
				[
					'class' => "field-label",
			]) !!}
			
			<p class="row-associate-desc">@lang('board.report.associate-disclaimer')</p>
		</div>
		@endif
		
		@if (!$report)
		<div class="field row-submit">
			{!! Form::button(
				trans("board.submit." . implode($actions,"+"), [ 'board' => "/{$post->board_uri}/" ]),
				[
					'type'      => "submit",
					'class'     => "field-submit",
			]) !!}
		</div>
		@endif
	</fieldset>
	
{!! Form::close() !!}