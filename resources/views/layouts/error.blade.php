@extends('layouts.main')

@section('body-class', "error error-{$status_code}")
@section('title', "{$status_code} " . trans("error.{$status_code}.title"))
@section('boardlist', "<!-- -->")
@section('footer-inner', "<!-- -->")

@section('content')
<main id="error" class="error-code-{{ $status_code }}">
	<section class="error-flair smooth-box">
		<figure class="error-figure">
			<img src="{{ asset("static/img/errors/{$status_code}.jpg") }}" class="error-image" />
			<figcaption class="error-caption"><a class="error-credit" href="https://twitter.com/Kuvshinov_Ilya">Ilya Kuvshinov @Kuvshinov_Ilya</a></figcaption>
		</figure>
	</section>
</main>
@endsection