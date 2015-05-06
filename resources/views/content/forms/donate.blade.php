@if (Request::secure() || env('APP_DEBUG'))

@include('errors.parts.js')

<form action="{!! url('/cp/donate/') !!}" method="POST" id="payment-form" class="form-donate" data-widget="donate">
	<input type="hidden" name="_token" value="{{ csrf_token() }}" />
	
	@if (env('APP_DEBUG'))
		@include('widgets.messages', [ 'messages' => [ "<tt>APP_DEBUG</tt> is set to <tt>TRUE</tt>. This form is only a test and will not charge." ] ])
	@else
		@include('widgets.messages')
	@endif
	
	<fieldset id="card-details" class="form-fields require-js">
		<legend class="form-legend">Donate to Larachan Development</legend>
		
		<div id="card-form">
			<div class="field-row">
				<div class="field row-ccn">
					<input class="field-control numeric" id="ccn" name="ccn" type="text" maxlength="20" size="20" autofocus required data-stripe="number" />
				</div>
			</div>
			
			<div class="field-row">
				<div class="field row-cvc">
					<label class="field-label" for="cvc">CVC</label>
					<input class="field-control" id="cvc" name="cvc" type="text" maxlength="3" size="3" pattern="[0-9]{3}" required data-stripe="cvc" />
				</div>
				
				<div class="field row-month">
					<label class="field-label" for="month">Expiration</label>
					<select class="field-control" id="month" name="exp-month" data-stripe="exp-month">
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
					<select class="field-control" id="year" name="exp-year" data-stripe="exp-year">
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
						<input type="radio" name="payment" id="payment-{{!! $cycle !!}" value="{!! $cycle !!}" class="donate-cycle-input" {{ $cycle == "once" ? "checked" : "" }} /> {{{ $cycleName }}}
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
				<button type="submit" class="field-submit">Submit Donation</button>
			</div>
		</div>
		
		<div id="payment-email">
			* Monthly support will be debited on the anniversary of the first donation, until such time as you notify us to discontinue them. Donations initiated on the 29, 30, or 31 of the month will recur on the last day of the month for shorter months, as close to the original date as possible. Please ensure you enter a valid email so that invoices are delivered correctly.
		</div>
	</fieldset>
	
	<div id="payment-security" class="grid-50 require-js">
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
			The most direct way to contribute to the development process is with Bitcoins. I am freely able to access anything sent to me. A whole bitcoin is worth up to <strong>28 hours</strong> of development time.
			<p class="btc-nojs no-js">For users most concerned about privacy (you!), this is the best method available.</p>
			<code class="btc-code">1Ah4wk9WRfhK5gtbgUyGrJriqiXHgyyoJZ</code>
		</blockquote>
	</div>
</form>
@else
	@include('errors.parts.ssl')
@endif