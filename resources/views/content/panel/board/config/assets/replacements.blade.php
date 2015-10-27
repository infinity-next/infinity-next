@foreach ([ 'spoiler', 'deleted', 'icon'] as $boardAsset)
	@include('content.panel.board.config.assets.basic', [
		'asset' => $boardAsset,
	])
@endforeach