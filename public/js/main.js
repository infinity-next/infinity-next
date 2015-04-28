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
		
		console.log(widget);
		
		if (typeof options !== "object") {
			options = {};
		}
		
		widget.options = $.extend(options, widget.defaults);
		
		console.log(widget.options, options, widget.defaults);
		
		if (typeof target !== "string") {
			target = widget.options.selector.widget;
		}
		
		var $widget = widget.$widget = $(target);
		
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
		options  : {},
		
		bind     : {
			widget : function() {
				alert(1);
			}
		},
		
		build    : {
			
		},
		
		events   : {
			
		},
		
		submit   : function(parameters) {
			
		},
		
		init     : function(target, options) {
			window.lc.widgetArguments.call(widget, arguments);
		}
	};
	
	window.lc.widget("notify", widget);
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
				'widget'        : "#payment-form",
				
				'input-ccn'     : "#ccn",
				
				'inputs-cycle'  : ".row-payment .field-control-inline",
				'inputs-amount' : "#donate-details input"
			},
			
			// HTML Templates for dynamic construction
			template : {
				
			}
		},
		
		// Compiled settings.
		options  : {},
		
		// Event binding.
		bind     : {
			widget : function() {
				alert(1);
			}
		},
		
		// HTML building.
		build    : {
			
		},
		
		// Event trigger handlers.
		events   : {
			
		},
		
		// Form submission.
		submit   : function(parameters) {
			
		},
		
		// Widget building.
		init     : function(target, options) {
			window.lc.widgetArguments.call(widget, arguments);
		}
	};
	
	window.lc.widget("donate", widget);
})(window, jQuery);




Stripe.setPublishableKey('pk_test_2GFaAAaqm95AaMlwveuQlRvN');

$(document)
	.on('change', "#ccn", function(event) {
		$(this)
			.validateCreditCard(function(result) {
				$(this)[0].className = "field-control";
				
				$(this).addClass(result.card_type.name);
				
				if (result.valid) {
					return $(this).addClass('control-valid');
				}
				else {
					return $(this).removeClass('control-invalid');
				}
			},
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
	})
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
		
		
		$("#payment-time").html( "<strong>$" + amount + " USD</strong> will afford up to <wbr> <strong>" + timestamp + "</strong> of development time" + (paymentVal == "monthly" ? " per month" : ""));
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