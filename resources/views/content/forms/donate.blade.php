@if (!Request::secure())
<form action="{!! url('/cp/donate/') !!}" method="POST" id="payment-form" class="form-donate">
	<input type="hidden" name="_token" value="{{ csrf_token() }}" />
	
	<span class="payment-errors"></span>
	
	<fieldset class="form-fields" id="card-details">
		<legend class="form-legend">Donate to Larachan Development</legend>
		
		<div id="card-form">
			<div class="field-row">
				<div class="field row-ccn">
					<input class="field-control" id="ccn" type="text" maxlength="20" size="20" pattern="[0-9]{13,12}" data-stripe="number" placeholder="4242 4242 4242 4242"/>
				</div>
			</div>
			
			<div class="field-row">
				<div class="field row-cvc">
					<label class="field-label" for="cvc">CVC</label>
					<input class="field-control" id="cvc" type="text" maxlength="4" size="4" data-stripe="cvc" />
				</div>
				
				<div class="field row-month">
					<label class="field-label" for="month">Expiration</label>
					<select class="field-control" id="month" data-stripe="exp-month">
						<option value="1">1 - Jan</option>
						<option value="2">2 - Feb</option>
						<option value="3">3 - Mar</option>
						<option value="4">4 - Apr</option>
						<option value="5">5 - May</option>
						<option value="6">6 - Jun</option>
						<option value="7">7 - Jul</option>
						<option value="8">8 - Aug</option>
						<option value="9">9 - Sep</option>
						<option value="10">10 - Oct</option>
						<option value="11">11 - Nov</option>
						<option value="12">12 - Dec</option>
					</select>
				</div>
				
				<div class="field row-year">
					<label class="field-label" for="year">&nbsp;</label>
					<select class="field-control" id="year" data-stripe="exp-year">
						<option value="2015">2015</option>
						<option value="2016">2016</option>
						<option value="2017">2017</option>
						<option value="2018">2018</option>
						<option value="2019">2019</option>
						<option value="2020">2020</option>
						<option value="2021">2021</option>
						<option value="2022">2022</option>
						<option value="2023">2023</option>
						<option value="2024">2024</option>
						<option value="2025">2025</option>
						<option value="2026">2026</option>
					</select>
				</div>
			</div>
			
			<div id="donate-details">
				<div class="field-row">
					<div class="field row-payment">
						<label class="field-label"><input class="field-control-inline" type="radio" name="payment" value="once" checked /> One-time donation</label>
						<label class="field-label"><input class="field-control-inline" type="radio" name="payment" value="monthly" /> Monthly support</label>
					</div>
				</div>
				
				<div class="field-row" id="payment-once" style="display: none;">
					<div class="field row-amount">
						<label class="field-label" for="amount">Contribution Amount (USD$)</label>
						<span class="field-value"></span>
						<input class="field-control" id="amount" name="amount" type="number" min="2.5" value="12.00" />
					</div>
				</div>
				
				<div class="field-row donate-details" id="payment-monthly" style="display: none;">
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
			</div>
			
			<div class="field-row" id="payment-time"></div>
			<div class="field-row" id="payment-tax"></div>
			
			<div id="payment-submit">
				<button type="submit" class="field-submit">Submit Payment</button>
			</div>
			
		</div>
	</fieldset>
	
	<div id="payment-security" class="grid-50">
		<span class="security-footer"><strong>At no point</strong> is your personal information stored on this webserver, even temporarily. We interact strictly through Stripe.</span>
		
		<ul class="security-steps">
			<li class="security-step">
				<div class="security-icons"><i class="fa fa-user"></i><i class="fa fa-angle-right"></i><i class="fa fa-cc-stripe"></i></div>
				<span class="security-item">When submitted, your details are encrypted and sent directly to Stripe from your browser.</span>
			</li>
			<li class="security-step">
				<div class="security-icons"><i class="fa fa-cc-stripe"></i><i class="fa fa-angle-right"></i><i class="fa fa-user"></i></div>
				<span class="security-item">If valid, Stripe responds with an encrypted, non-identifying token.</span>
			</li>
			<li class="security-step">
				<div class="security-icons"><i class="fa fa-user"></i><i class="fa fa-angle-right"></i><i class="fa fa-server"></i></div>
				<span class="security-item">Your client sends the token to us instead of credit card details.</span>
			</li>
			<li class="security-step">
				<div class="security-icons"><i class="fa fa-server"></i><i class="fa fa-angle-right"></i><i class="fa fa-cc-stripe"></i></div>
				<span class="security-item">We check this token against Stripe's records. If it is valid, we charge it and store only a receipt.</span>
			</li>
		</ul>
	</div>
	
	<div id="payment-btc" class="grid-50">
		<h5 class="btc-title"><i class="fa fa-btc"></i>itcoin Contributions</h5>
		<blockquote class="btc-desc">
			<img class="btc-qr" src="/img/assets/btc.png" />
			The best and most direct way to contribute to the development process is with Bitcoins. I live in a city with a Bitcoin ATM and am freely able to access anything sent to me. A whole bitcoin is worth up to <strong>28 hours</strong> of development time.
			<code class="btc-code">1Ah4wk9WRfhK5gtbgUyGrJriqiXHgyyoJZ</code>
		</blockquote>
	</div>
</form>
@else
	@include('errors.parts.ssl')
@endif