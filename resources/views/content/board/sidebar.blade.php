<aside class="index-sidebar">
	@if (isset($board))
	<div class="sidebar-content">
		{!! $board->getSidebarContent() !!}
	</div>
	@endif
	
	@include('widgets.ads.board_bottom_right')
</aside>