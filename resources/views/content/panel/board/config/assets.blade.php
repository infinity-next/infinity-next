{{-- Banner List --}}
<fieldset class="form-fields group-board_banners">
	<legend class="form-legend">{{ trans("config.legend.board_banners") }}</legend>
	
	<dl class="option option-banner_upload">
		<dt class="option-term">
			{!! Form::label(
				"banner_upload",
				trans("config.option.banner_upload"),
				[
					'class' => "field-label",
			]) !!}
		</dt>
		<dd class="option-definition">
			<input class="field-control" id="banner_upload" name="banner_upload" type="file" multiple />
		</dd>
	</dl>
	
	@if (count($banners))
	{{-- @foreach ($banners as $banner)
	<dl class="option option-banner">
		<dt class="option-term"></dt>
		<dd class="option-definition">
			{!! Form::checkbox(
				,
				1,
				!!$option_value,
				[
					'id'        => $option_name,
					'class'     => "field-control",
			]) !!}
			{!! Form::label(
				$option_name,
				trans("config.option.{$option_name}"),
				[
					'class' => "field-label",
			]) !!}
		</dd>
	</dl>
	@endforeach --}}
	@endif
</fieldset>

<div class="field row-submit">
	{!! Form::button(
		trans("config.submit"),
		[
			'type'      => "submit",
			'class'     => "field-submit",
	]) !!}
</div>