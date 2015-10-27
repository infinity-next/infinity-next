<!-- Board Banners -->
{!! Form::open([
	'url'    => Request::url(),
	'method' => "PUT",
	'files'  => true,
	'id'     => "banner-upload",
	'class'  => "form-config",
]) !!}
	<fieldset class="form-fields group-new_board_banner">
		<legend class="form-legend">{{ trans("config.legend.board_banners") }}</legend>
		
		<dl class="option option-new_board_banner">
			<dt class="option-term">
				{!! Form::label(
					"new_board_banner",
					trans("config.option.boardAssetBannerUpload"),
					[
						'class' => "field-label",
				]) !!}
			</dt>
			<dd class="option-definition">
				<input class="field-control" id="new_board_banner" name="new_board_banner" type="file" />
			</dd>
		</dl>
	</fieldset>
	
	{!! Form::hidden('asset_type', 'board_banner') !!}
	
	<div class="field row-submit">
		{!! Form::button(
			trans("config.upload"),
			[
				'type'      => "submit",
				'class'     => "field-submit",
		]) !!}
	</div>
{!! Form::close() !!}

@if (count($banners))
{!! Form::open([
	'url'    => Request::url(),
	'method' => "PATCH",
	'files'  => true,
	'id'     => "banner-board",
	'class'  => "form-config",
]) !!}
	<fieldset class="form-fields group-board_banners">
		@foreach ($banners as $banner)
		<dl class="option option-banner">
			<dt class="option-term"></dt>
			<dd class="option-definition">
				<label for="banner_{{ $banner->board_asset_id }}" class="field-label">
					{!! Form::checkbox(
						"banner[{$banner->board_asset_id}]",
						1,
						true,
						[
							'id'    => "banner_{$banner->board_asset_id}",
							'class' => "field-control",
					]) !!}
					
					{!! $banner->asHTML() !!}
				</label>
			</dd>
		</dl>
		@endforeach
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
@endif