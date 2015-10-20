{!! Form::open([
	'url'    => Request::url(),
	'method' => "PUT",
	'files'  => true,
	'id'     => "tags-board",
	'class'  => "form-config",
	'data-widget' => "config",
]) !!}
	{{-- Basic Details --}}
	<fieldset class="form-fields group-board_basic">
		<legend class="form-legend">{{ trans("config.legend.board_tags") }}</legend>
		
		@include("widgets.config.option.list", [
			'option_name'  => "boardTags",
			'option_value' => $tags,
		])
	</fieldset>
	
	<div class="field row-submit">
		{!! Form::button(
			trans("config.submit"),
			[
				'type'      => "submit",
				'class'     => "field-submit",
		]) !!}
	</div>
{!! Form::close() !!}