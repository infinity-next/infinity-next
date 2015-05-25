@extends('layouts.main')

@section('content')
<main class="cp">
	<div class="cp-container grid-container">
		<div class="cp-box smooth-box">
			@section('nav-primary')
				@include( $c::$navPrimary )
			@show
			
			<div class="cp-frame grid-15">
				@section('nav-secondary')
					@include( $c::$navSecondary)
				@show
			</div>
			
			<div class="cp-frame grid-85">
				@include('widgets.messages')
				@yield('body')
			</div>
		</div>
	</div>
</main>
@endsection