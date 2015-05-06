// Enter into a closed namespace where we can freely
// define variables without messing up the window.
(function(window, $, undefined) {
	var lc = window.lc = function() {};
	
	lc.widgets = {};
	
	lc.widget = function(name, widget) {
		
		if (lc[name] !== undefined) {
			console.log("Trying to re-declare Larachan Widget \""+name+"\".");
			return false;
		}
		
		lc[name] = widget;
		return true;
	};
	
	lc.widgetArguments  = function(args) {
		var widget  = this;
		var target  = args[0];
		var options = args[1];
		
		if (typeof options !== "object") {
			options = {};
		}
		
		widget.options = $.extend(options, widget.defaults);
		
		console.log(widget.options);
		
		if (typeof target !== "string") {
			target = widget.options.selector.widget;
		}
		
		var $widget = widget.$widget = $(target).first();
		
		if ($widget.length) {
			widget.bind.widget();
			return true;
		}
		
		return false;
	};
	
	$(document).on('ready', function() {
		$("[data-widget]").each(function() {
			var requestedWidget = this.getAttribute('data-widget');
			if (lc[requestedWidget]) {
				lc[requestedWidget].init.call(this);
			}
			else {
				console.log("Requested widget \""+requestedWidget+"\" does not exist.");
			}
		});
	});
	
	return lc;
})(window, jQuery);


(function(window, $, undefined) {
	var widget = {
		// Short-hand for this widget's main object.
		$widget  : $(),
		
		// The default values that are set behind init values.
		defaults : {
			// Selectors for finding and binding elements.
			selector : {
				'widget'        : ".form-messages",
			},
			
			// HTML Templates for dynamic construction
			template : {
				'message'         : "<li class=\"form-message\"></li>",
				'message-info'    : "<li class=\"form-message message-info\"></li>",
				'message-success' : "<li class=\"form-message message-success\"></li>",
				'message-error'   : "<li class=\"form-message message-error\"></li>"
			}
		},
		
		// Compiled settings.
		options  : false,
		
		bind     : {
			widget : function() {
			}
		},
		
		build    : {
			
		},
		
		clear    : function() {
			widget.$widget.children().remove();
		},
		
		push     : function(message, messageType) {
			if (widget.options === false) {
				widget.init();
			}
			
			var $message;
			var className = "message";
			
			if (widget.options.template['message-'+messageType] !== undefined) {
				className = 'message-'+messageType;
			}
			
			$message = $(widget.options.template[className]);
			$message.append(message).appendTo(widget.$widget);
			
			// Scroll our window up to meet the notification if required.
			$('html, body').animate(
				{
					scrollTop : $message.offset().top - $(".board-header").height() - 10
				},
				250
			);
			
			return $message;
		},
		
		init     : function(target, options) {
			window.lc.widgetArguments.call(widget, arguments);
		}
	};
	
	window.lc.widget("notice", widget);
})(window, jQuery);


(function(window, $, undefined) {
	var widget = {
		// Short-hand for this widget's main object.
		$widget  : $(),
		
		// The default values that are set behind init values.
		defaults : {
			// Config options for this widget.
			config   : {
				'stripe-key'    : "pk_test_2GFaAAaqm95AaMlwveuQlRvN"
			},
			
			// Selectors for finding and binding elements.
			selector : {
				'widget'             : "#payment-form",
				
				'time'               : "#payment-time",
				
				'input-ccn'          : "#ccn",
				'input-cvc'          : "#cvc",
				'input-exp-month'    : "#month",
				'input-exp-year'     : "#year",
				'input-pay-monthly'  : "#payment-monthly",
				'input-pay-once'     : "#payment-once",
				'input-sub'          : "#subscription",
				'input-amount'       : ".donate-option-input:checked",
				'input-select-other' : "#input_amount_other",
				'input-amount-other' : "#input_amount_other_box",
				
				'inputs-cycle'       : ".donate-cycle-input:checked",
				'inputs-amount'      : ".donate-option-input, #input_amount_other_box",
				
				'message'            : "#payment-process"
			},
			
			// HTML Templates for dynamic construction
			template : {
				'message-stripe'    : "<div id=\"payment-process\">Contacting Stripe</div>",
				'message-server'    : "<div id=\"payment-process\">Processing</div>",
				'thank-you'         : "<div id=\"payment-received\">Thank you!</div>"
			}
		},
		
		// Compiled settings.
		options  : false,
		
		// Event binding.
		bind     : {
			widget : function() {
				Stripe.setPublishableKey(widget.options.config['stripe-key']);
				
				// $(widget.options.selector['input-pay-once']).insertBefore(widget.options.selector['input-pay-monthly']);
				
				widget.$widget
					.on('submit', widget.events.formSubmit)
					.on('change', widget.options.selector['input-ccn'], widget.events.ccnChange)
					.on('change', widget.options.selector['inputs-cycle'], widget.events.cycleChange)
					.on('change', widget.options.selector['inputs-amount'], widget.events.paymentChange)
					.on('change', widget.options.selector['input-amount-other'], widget.events.otherChange)
					.on('focus', widget.options.selector['input-amount-other'], widget.events.otherFocus);
				
				widget.events.cycleChange();
				widget.events.paymentChange();
			}
		},
		
		// HTML building.
		build    : {
			
		},
		
		// Event trigger handlers.
		events   : {
			ajaxAlways     : function(data, textStatus, errorThrown) {
				widget.$widget.find('button').prop('disabled', false);
			},
			
			ajaxDone     : function(data, textStatus, errorThrown) {
				var $ty = $(widget.options.template['thank-you']);
				
				$(widget.options.selector['message']).replaceWith($ty);
				$ty.hide().fadeIn(500);
				setTimeout(function() { widget.$widget.unblock(); }, 1500);
				
				if (data.amount !== false) {
					window.lc.notice.push("You were successfully charged for <strong>" + data.amount + "</strong>. Thank you for your support!", "success");
				}
				
				$.each(data.errors, function(index, error) {
					window.lc.notice.push(error, "error");
				});
			},
			
			ajaxFail     : function(data, textStatus, errorThrown) {
				console.log(data);
				
				widget.$widget.unblock();
				window.lc.notice.push("The server responded with an unknown error. You were not charged. Please report this issue.", "error");
			},
			
			ccnChange      : function(event) {
				var $ccn = $(this);
				
				$ccn.val( $ccn.val().trim() );
				$ccn.validateCreditCard(
						widget.events.ccnValidate,
						{
							accept: [
								'visa',
								'mastercard',
								'amex',
								'jcb',
								'discover',
								'diners_club_international',
								'diners_club_carte_blanche'
							]
						}
					);
			},
			
			ccnValidate    : function(result) {
				$(this)[0].className = "field-control";
				
				if (result.card_type)
				{
					$(this).addClass(result.card_type.name);
					
					if (result.valid) {
						return $(this).addClass('control-valid');
					}
					else {
						return $(this).removeClass('control-invalid');
					}
				}
			},
			
			cycleChange    : function(event) {
				var paymentVal = $(widget.options.selector['inputs-cycle']).filter(":checked").val();
				
				if (paymentVal != "once")
				{
					$(widget.options.selector['input-amount-other'])
						.prop('checked', false)
						.parent()
							.toggle(false);
					
					$(widget.options.selector['inputs-amount'])
						.filter("[value=12]")
							.prop('checked', true);
				}
				else
				{
					$(widget.options.selector['input-amount-other'])
						.parent()
							.toggle(true);
				}
				
				widget.events.paymentChange();
			},
			
			otherFocus     : function(event) {
				$(this).val("");
				$(widget.options.selector['input-select-other']).prop('checked', true);
				widget.events.paymentChange();
			},
			
			otherChange    : function(event) {
				widget.events.paymentChange();
			},
			
			formSubmit     : function(event) {
				window.lc.notice.clear();
				
				var valid = true;
				var sel   = widget.options.selector;
				
				// Make sure the CCN has been validated by the jQuery tool.
				var $ccn  = $(sel['input-ccn']);
				if (!$ccn.is(".control-valid"))
				{
					window.lc.notice.push("Please enter a valid credit card number.", 'error');
					$ccn.focus().trigger('focus');
					valid = false;
				}
				
				// Check to see if CVC is valid.
				var $cvc = $(sel['input-cvc']);
				if (parseInt($cvc.val(), 10).toString().length !== 3)
				{
					window.lc.notice.push("Please enter a valid three-digit security code. It is usually found on the back of the card.", 'error');
					$ccn.focus().trigger('focus');
					valid = false;
				}
				
				// Check if expiration date is older than this month.
				var $month     = $(sel['input-exp-month']);
				var $year      = $(sel['input-exp-year']);
				var expiration = parseInt($month.val(), 10) + (parseInt($year.val(), 10) * 12);
				var expiredBy  = new Date().getMonth() + (new Date().getFullYear() * 12);
				if (expiration < expiredBy)
				{
					window.lc.notice.push("Double-check your expiration date. This card is invalid.", 'error');
					valid = false;
				}
				
				// See what amount we've entered.
				var $amountSel = $(sel['input-amount']).filter(":checked");
				var $amountInp = $(sel['input-amount-other']);
				var amount     = 0;
				if (!$amountSel.length)
				{
					window.lc.notice.push("Please enter an amount.", 'error');
					valid = false;
				}
				else if ($amountSel.val() == "Other")
				{
					amount = parseInt($amountInp.val(), 10);
					
					if (isNaN(amount) || amount <= 3)
					{
						window.lc.notice.push("Please enter a real amount that is greater than $3.", 'error');
						$amountInp.focus();
						valid = false;
					}
					else if (amount.toString() !== $amountInp.val())
					{
						window.lc.notice.push("Please enter a real, whole number as a donation amount.", 'error');
						$amountInp.focus();
						valid = false;
					}
				}
				
				if (valid)
				{
					var $form = $(this);
					
					$form.block({
						message : widget.options.template['message-stripe'],
						theme   : true
					});
					
					// Disable the submit button to prevent repeated clicks
					$form.find('button').prop('disabled', true);
					
					// Send the information to Stripe.
					Stripe.card.createToken($form, widget.events.stripeResponse);
					
					// Clear personal information.
					$(widget.options.selector['input-ccn'])
						.add(widget.options.selector['input-cvc'])
							.val("")
							.trigger('change');
				}
				
				// Prevent the form from submitting with the default action
				return false;
			},
			
			paymentChange  : function(event) {
				var workFactor = 0.1;
				var timestamp = "";
				
				var paymentVal = $(widget.options.selector['inputs-cycle']).filter(":checked").val();
				var amount = $(widget.options.selector['input-amount']).filter(":checked").val();
				
				if( amount == "Other")
				{
					amount = parseInt($(widget.options.selector['input-amount-other']).val(), 10);
				}
				else
				{
					amount = parseInt(amount, 10);
				}
				
				if (isNaN(amount))
				{
					amount = 0;
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
				
				var text = "<strong>$" + amount + " USD</strong> will afford up to <wbr> <strong>" + timestamp + "</strong> of development time" + (paymentVal == "monthly" ? " per month" : "");
				
				$(widget.options.selector['time']).html(text);
			},
			
			stripeResponse : function(status, response) {
				var $form = widget.$widget;
				
				if (response.error) {
					// Show the errors on the form
					window.lc.notice.push(response.error.message, "error");
					
					$form.unblock();
					$form.find('button').prop('disabled', false);
				}
				else {
					// Response contains id and card, which contains additional card details
					var token = response.id;
					
					// Insert the token into the form so it gets submitted to the server
					$form.append($('<input type="hidden" name="stripeToken" />').val(token));
					
					// Submit to server
					var parameters = $form
						.add("<input type=\"hidden\" name=\"ajax\" value=\"1\" />")
						.serialize();
					
					if ($(widget.options.selector['input-amount']).val() == "Other")
					{
						parameters += "&amount=" + $(widget.options.selector['input-amount-other']).val();
					}
					
					widget.submit(parameters);
				}
			}
		},
		
		// Form submission.
		submit   : function(parameters) {
			var $form = widget.$widget;
			
			// Change our server message.
			$(widget.options.selector['message']).replaceWith(widget.options.template['message-server']);
			
			$.post(
				$form.attr('action'),
				parameters
			)
				.done(widget.events.ajaxDone)
				.fail(widget.events.ajaxFail)
				.always(widget.events.ajaxAlways);
		},
		
		// Widget building.
		init     : function(target, options) {
			window.lc.widgetArguments.call(widget, arguments);
		}
	};
	
	window.lc.widget("donate", widget);
})(window, jQuery);



/*

$(document)
	.on('change', "#ccn", function(event) {
	.on('change', ".row-payment .field-control-inline")
	.on('change', "#donate-details input", function(event) {
	})
	.on('submit', "#payment-form", function(event) {
	})
	.on('ready', function(event) {
		$(".row-payment .field-control-inline").first().trigger('change');
	});
	

*/