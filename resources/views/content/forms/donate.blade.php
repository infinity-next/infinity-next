@if (!Request::secure())
<form action="{!! url('/cp/donate/') !!}" method="POST" id="payment-form" class="form-donate">
	<input type="hidden" name="_token" value="{{ csrf_token() }}" />
	
	<span class="payment-errors"></span>
	
	<fieldset class="form-fields grid-50">
		<legend class="form-legend">Card Details</legend>
		
		<div class="field-row">
			<div class="field row-ccn">
				<label class="field-label" for="ccn">Card Number</label>
				<input class="field-control" id="ccn" type="text" maxlength="20" size="20" data-stripe="number" />
			</div>
			
			<div class="field row-cvc">
				<label class="field-label" for="cvc">CVC</label>
				<input class="field-control" id="cvc" type="text" maxlength="4" size="4" data-stripe="cvc" />
			</div>
		</div>
		
		<div class="field-row">
			<div class="field-title">Expiration (MM/YYYY)</div>
			
			<div class="field row-month">
				<input class="field-control" id="month" type="text" maxlength="2" size="2" data-stripe="exp-month" />
			</div>
			
			<div class="field row-year">
				<input class="field-control" id="year" type="text" maxlength="4" size="4" data-stripe="exp-year"/>
			</div>
		</div>
		
		<div class="field row-submit">
			<button type="submit" class="field-submit">Submit Payment</button>
		</div>
	</fieldset>
	
	<fieldset class="form-fields grid-50" id="donate-details">
		<legend class="form-legend">Donation Details</legend>
		
		<div class="field-row">
			<div class="field-title">Contribution Type</div>
			
			<div class="field row-payment">
				<label class="field-label"><input class="field-control-inline" type="radio" name="payment" value="once" checked /> One-time donation</label>
				<label class="field-label"><input class="field-control-inline" type="radio" name="payment" value="monthly" /> Monthly support</label>
			</div>
		</div>
		
		<div class="field-row" id="payment-once" style="display: none;">
			<div class="field row-amount">
				<label class="field-label" for="amount">Contribution Amount (USD$)</label>
				<span class="field-value"></span>
				<input class="field-control" id="amount" name="amount" type="range" min="2" max="100" value="12" />
			</div>
		</div>
		
		<div class="field-row" id="payment-monthly" style="display: none;">
			<div class="field row-subscription">
				<label class="field-label" for="subscription">Contribution per Month (USD$)</label>
				<select class="field-control" id="subscription" name="subscription">
					<option value="monthly-three" data-amount="3">$3 / month</option>
					<option value="monthly-six" data-amount="6">$6 / month</option>
					<option value="monthly-twelve" data-amount="12" selected>$12 / month</option>
					<option value="monthly-eighteen" data-amount="18">$18 / month</option>
				</select>
			</div>
		</div>
		
		<div class="field-row" id="payment-time">
			
		</div>
	</fieldset>
</form>
@else
	@include('errors.parts.ssl')
@endif