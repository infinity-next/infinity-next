{!! Form::open([
	'url'    => Request::url(),
	'method' => "PUT",
	'files'  => true,
	'id'     => "mod-form",
	'class'  => "form-mod smooth-box",
]) !!}
	@include('widgets.messages')
	
	<fieldset class="form-fields">
		<legend class="form-legend">{{ trans("board.legend." . implode($actions,"+"), [ 'board' => "/{$post->board_uri}/" ]) }}</legend>
		
		<div class="field row-ip label-inline">
			{!! Form::text(
				'ban_ip',
				$post->author_ip,
				[
					'id'        => "ban_ip",
					'class'     => "field-control",
					'maxlength' => 255,
			]) !!}
			{!! Form::label(
				"ban_ip",
				trans('board.field.ip'),
				[
					'class' => "field-label",
			]) !!}
		</div>
		
		<div class="field row-ip label-inline">
			{!! Form::text(
				'justification',
				"",
				[
					'id'        => "justification",
					'class'     => "field-control",
					'maxlength' => 255,
			]) !!}
			{!! Form::label(
				"justification",
				trans('board.field.justification'),
				[
					'class' => "field-label",
			]) !!}
		</div>
		
		
		@if ($banMaxLength > 0)
		<div class="field row-inline row-expires row-expires-days">
			<span class="field-label">@lang('board.field.expires')</span>
			{!! Form::selectRange(
				'expires_days',
				0,
				$banMaxLength,
				0,
				[
					'class' => "field-control",
			]) !!}
			@lang('board.field.expires-days')
		</div>
		@else
			{!! Form::hidden('expires_days', 0) !!}
		@endif
		
		<div class="field row-inline row-expires row-expires-hours">
			<span class="field-label"></span>
			{!! Form::selectRange(
				'expires_hours',
				0,
				23,
				0,
				[
					'class' => "field-control",
			]) !!}
			@lang('board.field.expires-hours')
		</div>
		
		<div class="field row-inline row-expires row-expires-minutes">
			<span class="field-label"></span>
			{!! Form::selectRange(
				'expires_minutes',
				0,
				59,
				0,
				[
					'class' => "field-control",
			]) !!}
			@lang('board.field.expires-minutes')
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