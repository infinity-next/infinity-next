@extends('layouts.main')

@section('content')
<main class="cp">
	<div class="cp-container grid-container">
		<div class="cp-box smooth-box">
			@include('nav.cp')
			
			<div class="cp-frame grid-15">
				@include('nav.cp.home')
			</div>
			
			<div class="cp-frame grid-85">
				@include('widgets.messages')
				@yield('body')
			</div>
		</div>
	</div>
</main>
@endsection