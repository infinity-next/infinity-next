<!-- Basic asset: {{ $asset }} -->
{!! Form::open([
	'url'    => Request::url(),
	'method' => "PUT",
	'files'  => true,
	'id'     => "icon-upload",
	'class'  => "form-asset grid-33",
]) !!}
	<fieldset class="form-fields group-new_{{$asset}}">
		<legend class="form-legend">{{ trans("config.legend.asset.{$asset}") }}</legend>
		
		<figure class="form-asset">
			<img class="form-asset-img" src="{{ $board->getAssetURL($asset) }}" />
		</figure>
		
		<div class="form-asset-replace">
			<input class="field-control" id="new_{{$asset}}" name="new_{{$asset}}" type="file" />
		</div>
	</fieldset>
	
	{!! Form::hidden('asset_type', $asset) !!}
	
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
				'type'      => "submit",
				'name'      => "delete",
				'value'     => 1,
				'class'     => "field-delete",
		]) !!}
	</div>
{!! Form::close() !!}