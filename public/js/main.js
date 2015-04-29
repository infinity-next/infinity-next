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
				'message'         : "<li class=\"form-message\"><i class=\"fa fa-circle-thin\"></i></li>",
				'message-info'    : "<li class=\"form-message message-info\"><i class=\"fa fa-info-circle\"></i></li>",
				'message-success' : "<li class=\"form-message message-success\"><i class=\"fa fa-check\"></i></li>",
				'message-error'   : "<li class=\"form-message message-error\"><i class=\"fa fa-exclamation-triangle\"></i></li>"
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
				'widget'            : "#payment-form",
				
				'time'              : "#payment-time",
				
				'input-amount'      : "#amount",
				'input-ccn'         : "#ccn",
				'input-cvc'         : "#cvc",
				'input-pay-monthly' : "#payment-monthly",
				'input-pay-once'    : "#payment-once",
				'input-sub'         : "#subscription",
				'input-amount'      : "#amount",
				
				'inputs-cycle'      : ".row-payment .field-control-inline",
				'inputs-amount'     : "#donate-details input",
				
				'message'           : "#payment-process"
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
				
				widget.$widget
					.on('submit', widget.events.formSubmit)
					.on('change', widget.options.selector['input-ccn'], widget.events.ccnChange)
					.on('change', widget.options.selector['inputs-cycle'], widget.events.cycleChange)
					.on('change', widget.options.selector['inputs-amount'], widget.events.paymentChange);
				
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
				$(this).validateCreditCard(
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
				
				$(widget.options.selector['input-pay-once']).toggle(paymentVal == "once");
				$(widget.options.selector['input-pay-monthly']).toggle(paymentVal == "monthly");
			},
			
			formSubmit     : function(event) {
				window.lc.notice.clear();
				
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
				
				// Prevent the form from submitting with the default action
				return false;
			},
			
			paymentChange  : function(event) {
				var workFactor = 0.1875;
				var timestamp = "";
				
				var paymentVal = $(widget.options.selector['inputs-cycle']).filter(":checked").val();
				var amount = 0;
				
				if (paymentVal == "once")
				{
					amount = parseInt($(widget.options.selector['input-amount']).val(), 10);
				}
				else
				{
					amount = parseInt($(widget.options.selector['input-sub']).children("option:selected").attr('data-amount'), 10);
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
					widget.submit($form.add("<input type=\"hidden\" name=\"ajax\" value=\"1\" />").serialize());
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