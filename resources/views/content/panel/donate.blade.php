@extends('layouts.main.panel')

@section('body')
<main>
	@if (Request::secure() || env('APP_DEBUG', false))
		@section('required-js')
			<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
		@stop
		
		@include( $c->template('errors.parts.js') )
		@include( $c->template('panel.donate.form') )
	@else
		@include( $c->template('errors.parts.ssl') )
	@endif
</main>
@stop