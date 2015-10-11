<figure class="error error-ssl">
	<i class="error-icon fa fa-unlock-alt"></i>
	
	<figcaption class="error-caption">
		<h4 class="error-title">@lang('error.ssl.title')</h4>
		<span class="error-desc"> <a href="{!! secure_url(Request::path()) !!}">@lang('error.ssl.desc')</a></span>
	</figcaption>
</figure>