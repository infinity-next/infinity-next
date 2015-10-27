<!-- Basic asset: {{ $asset }} -->
{!! Form::open([
	'url'    => Request::url(),
	'method' => "PUT",
	'files'  => true,
	'id'     => "icon-upload",
	'class'  => "form-asset grid-33",
]) !!}
	<fieldset class="form-fields group-new_board_{{$asset}}">
		<legend class="form-legend">{{ trans("config.legend.board_{$asset}") }}</legend>
		
		<figure class="form-asset">
			<img class="form-asset-img" src="{{ $board->getAssetURL($asset) }}" />
			
			<figcaption class="form-asset-replace">
				<input class="field-control" id="new_board_icon" name="new_board_{{$asset}" type="file" />
			</figcaption>
		</figure>
	</fieldset>
	
	{!! Form::hidden('asset_type', 'board_' . $asset) !!}
	
	<div class="field row-submit">
		{!! Form::button(
			trans("config.upload"),
			[
				'type'      => "submit",
				'class'     => "field-submit",
		]) !!}
		{!! Form::button(
			trans("config.delete"),
			[
				'type'      => "delete",
				'class'     => "field-delete",
		]) !!}
	</div>
{!! Form::close() !!}