@extends('layouts.cp')

@section('nav-secondary')
	@include('nav.cp.home')
@endsection

@section('body')
<main>
	@if (Request::secure() || env('APP_DEBUG', false))
		@section('required-js')
			<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
		@stop
		
		@include('errors.parts.js')
		@include('content.forms.donate')
	@else
		@include('errors.parts.ssl')
	@endif
</main>
@stop