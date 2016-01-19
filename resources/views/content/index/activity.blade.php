<div class="grid-container">
	@include('widgets.messages')
	
	@include('content.index.sections.featured_post')
	
	@if (env('APP_ESI', false))
		<esi:include src="{{ url('.internal/site/recent-images', [], false) }}" />
		<esi:include src="{{ url('.internal/site/recent-posts', [], false) }}" />
	@else
		@include('content.index.sections.recent_images')
		@include('content.index.sections.recent_posts')
	@endif
</div>
