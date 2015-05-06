@extends('layouts.cp')

@section('body')
<main>
	@if (Request::secure() || env('APP_DEBUG'))
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