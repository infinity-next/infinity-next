@extends('layouts.main')

@section('content')
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript">
	Stripe.setPublishableKey('pk_test_2GFaAAaqm95AaMlwveuQlRvN');
	
	$(document).on('submit', "#payment-form", function(event) {
		var $form = $(this);
		
		// Disable the submit button to prevent repeated clicks
		$form.find('button').prop('disabled', true);
		
		Stripe.card.createToken($form, stripeResponseHandler);
		
		// Prevent the form from submitting with the default action
		return false;
	});
</script>

<header class="contrib-header">
	@include('widgets.boardlist')
	
	<section class="contrib-howto grid-container">
		<figure class="page-head">
			<img id="logo" src="/img/logo.png" alt="Larachan" />
			
			<figcaption class="page-details">
				<h1 class="page-title">Contribute to Larachan</h1>
				<h2 class="page-desc">Pushing imageboard communities beyond</h2>
			</figcaption>
		</figure>
	</section>
</header>
	
<main>
	<form action="" method="POST" id="payment-form">
		<span class="payment-errors"></span>
		
		<div class="form-row">
		<label>
			<span>Card Number</span>
			<input type="text" size="20" data-stripe="number"/>
		</label>
		</div>
		
		<div class="form-row">
		<label>
			<span>CVC</span>
			<input type="text" size="4" data-stripe="cvc"/>
		</label>
		</div>
		
		<div class="form-row">
		<label>
			<span>Expiration (MM/YYYY)</span>
			<input type="text" size="2" data-stripe="exp-month"/>
		</label>
		<span> / </span>
		<input type="text" size="4" data-stripe="exp-year"/>
		</div>
		
		<button type="submit">Submit Payment</button>
	</form>
</main>
@stop