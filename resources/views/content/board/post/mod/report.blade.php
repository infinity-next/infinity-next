{!! Form::open([
	'url'    => Request::url(),
	'method' => "POST",
	'id'     => "mod-form",
	'class'  => "form-report smooth-box",
]) !!}
	@include('widgets.messages')
	
	<fieldset class="form-fields">
		<legend class="form-legend">{{ trans("board.legend." . implode($actions,"+"), [ 'board' => "/{$post->board_uri}/" ]) }}</legend>
		
		<blockquote>@lang("board.report." . (in_array('global', $actions) ? "global" : "local"))</blockquote>
		
		@if ($reportText)
		<blockquote class="report-rules">
			<p>@lang("board.report.desc-" . (in_array('global', $actions) ? "global" : "local"))</p>
			
			{!! $reportText !!}
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
				old('reason'),
				[
					'id'          => "reason",
					'class'       => "field-control",
					'maxlength'   => 1024,
			]) !!}
		</div>
		
		<div class="field row-captcha">
			<label class="field-label" for="captcha">
				{!! Captcha::img() !!}
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
		
		<div class="field row-submit">
			{!! Form::button(
				trans("board.submit." . implode($actions,"+"), [ 'board' => "/{$post->board_uri}/" ]),
				[
					'type'      => "submit",
					'class'     => "field-submit",
			]) !!}
		</div>
	</fieldset>
	
{!! Form::close() !!}