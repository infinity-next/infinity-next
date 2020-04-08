@foreach ([ 'file_spoiler', 'file_deleted', 'board_icon'] as $boardAsset)
	@include('content.panel.board.config.assets.basic', [
		'asset' => $boardAsset,
	])
@endforeach
