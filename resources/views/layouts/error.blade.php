@extends('layouts.main')

@section('body-class', "error error-{$status_code}")
@section('page-title', "{$status_code} " . trans("error.{$status_code}.title"))
@section('boardlist', "<!-- -->")
@section('footer-inner', "<!-- -->")

@if ($status_code == 500)
	@section('header-logo', asset('static/img/logo_500.png'))
@endif

@if (isset($error_css) && $error_css != "")
	@section('css')
		@parent
		
		<style type="text/css">
			{!! $error_css !!}
		</style>
	@endsection
@endif

@section('content')
<main id="error" class="error-code-{{ $status_code }}">
	<section class="error-flair smooth-box">
		<figure class="error-figure">
			<img src="{{ asset("static/img/errors/{$status_code}.jpg") }}" class="error-image" />
			<figcaption class="error-caption"><a class="error-credit" href="https://twitter.com/Kuvshinov_Ilya">Ilya Kuvshinov @Kuvshinov_Ilya</a></figcaption>
		</figure>
	</section>
	
	@if ((isset($error_html) && $error_html != "") && (env('APP_DEBUG', false) === true || ($user && $user->canAdminConfig())))
	<section class="error-trace">
		{!! $error_html !!}
	</section>
	@endif
</main>
@endsection