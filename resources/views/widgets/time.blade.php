<time data-widget="time" datetime="{{ $carbon->toAtomString() }}" title="{{
	$post->created_at->diffForHumans()
}}">{{
	$carbon->formatLocalized( trans('widget.time.format') )
}}</time>
