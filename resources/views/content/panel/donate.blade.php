@extends('layouts.main.panel')

@section('body')
<main>
	@if (Request::secure() || env('APP_DEBUG', false))
		@if (env('CASHIER_SERVICE', 'braintree') === "stripe")
			@section('required-js')
			<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
			@stop
		@elseif (env('CASHIER_SERVICE', 'braintree') === "braintree")
			@section('footer-inner')
				@parent
				
				<script type="text/javascript" src="https://js.braintreegateway.com/v2/braintree.js"></script>
				<script type="text/javascript">
					$("#payment-form").attr('data-widget', "donate");
					window.ib.bindAll($("#payment-form"));
				</script>
			@stop
		@endif
		
		@include( $c->template('errors.parts.js') )
		@include( $c->template('panel.donate.checkout') )
	@else
		@include( $c->template('errors.parts.ssl') )
	@endif
</main>
@stop
