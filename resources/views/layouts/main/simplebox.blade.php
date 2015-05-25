@extends('layouts.main')

@section('content')
<main>
	<section class="auth-form grid-container smooth-box">
		@include('widgets.messages')
		
		<div class="grid-100">
			@yield('body')
		</div>
	</section>
</main>
@endsection