@extends('layouts.main')

@section('body-class', "error error-{$status_code}")
@section('title', "{$status_code} " . trans("error.{$status_code}.title"))
@section('boardlist', "<!-- -->")
@section('footer-inner', "<!-- -->")

@section('content')
<main id="error" class="{$status_code}">
	<section class="error-flair smooth-box">
		<figure class="error-figure">
			<img src="/img/errors/{{$status_code}}.jpg" class="error-image" />
			<figcaption class="error-caption"><a class="error-credit" href="https://twitter.com/kr0npr1nz">Ilya Kuvshinov @kr0npr1nz</a></figcaption>
		</figure>
	</section>
</main>
@endsection