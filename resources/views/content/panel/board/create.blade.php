@extends('layouts.main.panel')

@section('body')
{!! Form::open([
	'url'    => Request::url(),
	'method' => "PUT",
	'files'  => true,
	'id'     => "board-create",
	'class'  => "form-config",
]) !!}
	<h3 class="config-title">@lang("config.title.board_create")</h3>
	
	<fieldset class="form-fields group-board_basic">
		<legend class="form-legend">@lang("config.legend.board_basic")</legend>
		
		<dl class="option option-field-board_uri">
			<dt class="option-term">
				{!! Form::label(
					"board_uri",
					trans("config.option.board_uri"),
					[
						'class' => "field-label",
				]) !!}
				<span class="option-desc">@lang("config.option.desc.board_uri").</span>
			</dt>
			
			<dd class="option-definition">
				{!! Form::text(
					"board_uri",
					"",
					[
						'id'        => "board_uri",
						'class'     => "field-control",
				]) !!}
			</dd>
		</dl>
		
		<dl class="option option-field-title">
			<dt class="option-term">
				{!! Form::label(
					"title",
					trans("config.option.title"),
					[
						'class' => "field-label",
				]) !!}
			</dt>
			
			<dd class="option-definition">
				{!! Form::text(
					"title",
					"",
					[
						'id'        => "title",
						'class'     => "field-control",
				]) !!}
			</dd>
		</dl>
		
		<dl class="option option-field-description">
			<dt class="option-term">
				{!! Form::label(
					"description",
					trans("config.option.description"),
					[
						'class' => "field-label",
				]) !!}
			</dt>
			
			<dd class="option-definition">
				{!! Form::text(
					"description",
					"",
					[
						'id'        => "description",
						'class'     => "field-control",
				]) !!}
			</dd>
		</dl>
		
	</fieldset>
	
	<div class="field row-submit">
		{!! Form::button(
			trans("config.create"),
			[
				'type'      => "submit",
				'class'     => "field-submit",
		]) !!}
	</div>
{!! Form::close() !!}
@endsection