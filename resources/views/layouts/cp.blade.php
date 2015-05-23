@extends('layouts.main')

@section('content')
<main class="cp">
	<div class="cp-container grid-container">
		<div class="cp-box smooth-box">
			@section('nav-primary')
				@include('nav.cp')
			@show
			
			<div class="cp-frame grid-15">
				@yield('nav-secondary')
			</div>
			
			<div class="cp-frame grid-85">
				@include('widgets.messages')
				@yield('body')
			</div>
		</div>
	</div>
</main>
@endsection