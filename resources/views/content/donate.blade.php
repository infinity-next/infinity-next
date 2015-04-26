@extends('layouts.cp')

@section('js')
@parent
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script type="text/javascript">
	Stripe.setPublishableKey('pk_test_2GFaAAaqm95AaMlwveuQlRvN');
	
	$(document)
		.on('change', ".row-payment .field-control-inline", function(event) {
			var paymentVal = $(".row-payment .field-control-inline:checked").val();
			
			$("#payment-once").toggle(paymentVal == "once");
			$("#payment-monthly").toggle(paymentVal == "monthly");
		})
		.on('change', "#donate-details input", function(event) {
			var workFactor = 0.1875;
			var timestamp = "";
			
			var paymentVal = $(".row-payment .field-control-inline:checked").val();
			var amount = 0;
			
			if (paymentVal == "once")
			{
				amount = parseInt($("#amount").val(), 10);
			}
			else
			{
				amount = parseInt($("#subscription option:selected").attr('data-amount'), 10);
			}
			
			var hours = parseFloat(amount * workFactor);
			
			if (hours < 1)
			{
				timestamp = (hours*60).toFixed(0) + " minutes";
			}
			else
			{
				timestamp = hours.toFixed(2) + " hours";
			}
			
			
			$("#payment-time").text( timestamp + " of development time" + (paymentVal == "monthly" ? " per month" : ""));
		})
		.on('submit', "#payment-form", function(event) {
			var $form = $(this);
			
			// Disable the submit button to prevent repeated clicks
			$form.find('button').prop('disabled', true);
			
			Stripe.card.createToken($form, stripeResponseHandler);
			
			// Prevent the form from submitting with the default action
			return false;
		})
		.on('ready', function(event) {
			$(".row-payment .field-control-inline").first().trigger('change');
		});
		
	function stripeResponseHandler(status, response) {
		var $form = $('#payment-form');
		
		if (response.error) {
			// Show the errors on the form
			$form.find('.payment-errors').text(response.error.message);
			$form.find('button').prop('disabled', false);
		}
		else {
			// response contains id and card, which contains additional card details
			var token = response.id;
			// Insert the token into the form so it gets submitted to the server
			$form.append($('<input type="hidden" name="stripeToken" />').val(token));
			// and submit
			$form.get(0).submit();
		}
	};
</script>
@stop

@section('body')
<main>
	@include('content.forms.donate')
</main>
@stop