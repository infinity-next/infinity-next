<div id="card-form">
	<div class="field-row">
		<div class="field row-ccn">
			<input class="field-control numeric" id="ccn" name="ccn" type="text" maxlength="20" size="20" autofocus required data-stripe="number" data-braintree-name="number" />
		</div>
	</div>
	
	<div class="field-row">
		<div class="field row-cvc">
			<label class="field-label" for="cvc">CVC</label>
			<input class="field-control" id="cvc" name="cvc" type="text" maxlength="3" size="3" pattern="[0-9]{3}" required data-stripe="cvc" data-braintree-name="cvv" />
		</div>
		
		<div class="field row-month">
			<label class="field-label" for="month">Expiration</label>
			<select class="field-control" id="month" name="exp-month" data-stripe="exp-month" data-braintree-name="expiration_month">
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
			<select class="field-control" id="year" name="exp-year" data-stripe="exp-year" data-braintree-name="expiration_year">
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
	
	<div class="field-row">
		<div class="field row-email">
			<label class="field-label" for="email">Email Address (<em>For invoices</em>)</label>
			<input class="field-control" type="email" id="email" name="email" value="{{{ $user ? $user->email : "" }}}" maxlength="254" required data-stripe="email"  />
		</div>
	</div>
	
	<div class="field-row">
		<div class="field row-attribution">
			<label class="field-label" for="attribution">Attribution (<em>Optional, Displayed publicly</em>)</label>
			<input class="field-control" type="text" id="attribution" name="attribution" value="{{{ $user ? $user->username : "" }}}" maxlength="32" placeholder="Anonymous" />
		</div>
	</div>
	
	
	<div id="donate-details">
		<div class="field-row">
			@foreach ($cycles as $cycleName => $cycle)
			<label class="donate-cycle-label">
				<input type="radio" name="payment" id="payment-{!! $cycle !!}" value="{!! $cycle !!}" class="donate-cycle-input" {{ $cycle == "once" ? "checked" : "" }} /> {{{ $cycleName }}}
			</label>
			@endforeach
		</div>
		
		<div class="field-row">
			<ul class="donate-options">
				@foreach ($amounts as $amount)
				<li class="donate-option">
					<input type="radio" name="amount" id="input_amount_{!! $amount !!}" value="{!! $amount !!}" class="donate-option-input" {{ $amount == 12 ? "checked" : "" }}  />
					<label for="input_amount_{!! $amount !!}" class="donate-option-label">${!! $amount !!}</label>
				</li>
				@endforeach
				
				<li class="donate-option">
					<input type="radio" name="amount" id="input_amount_other" value="Other" class="donate-option-input" >
					<label for="input_amount_other" id="input_amount_other_label" class="donate-option-label">
						<span>Other</span>
						<input type="text" id="input_amount_other_box" size="3" autocomplete="off" name="other" value="" />
					</label>
				</li>
			</ul>
		</div>
	</div>
	
	<div class="field-row" id="payment-time"><strong>$12 USD</strong> will afford up to <strong>1.25 hours</strong> of development time</div>
	
	<div id="payment-submit">
		<button type="submit" class="field-submit">Submit Contribution</button>
	</div>
</div>

<div id="payment-email">
	* Monthly support will be debited on the anniversary of the first payment, until such time as you notify us to discontinue them. Contributions initiated on the 29, 30, or 31 of the month will recur on the last day of the month for shorter months, as close to the original date as possible. Please ensure you enter a valid email so that invoices are delivered correctly.
</div>