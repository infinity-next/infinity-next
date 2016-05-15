@extends('layouts.main')

@section('area-css')
	<link rel="stylesheet" href="{{ elixir('static/builds/panel.css') }}" />
@endsection

@section('content')
<main class="simplebox">
	<section class="auth-form grid-container smooth-box">
		@include('widgets.messages')

		<div class="grid-100">
			@yield('body')
		</div>
	</section>
</main>
@endsection
