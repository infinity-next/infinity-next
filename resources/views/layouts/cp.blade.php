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
				@if (count($errors) > 0)
					<ul class="alerts">
					@foreach ($errors->all() as $error)
						<li class="alert">{{ $error }}</li>
					@endforeach
					</ul>
				@endif
				
				@yield('body')
			</div>
		</div>
	</div>
</main>
@endsection