
/**
 * Widget Master
 */
(function(window, $, undefined) {
	var ib = window.ib = function() {};
	
	ib.widgets = {};
	
	ib.bindAll = function() {
		$("[data-widget]").each(function() {
			ib.bindElement(this);
		});
	},
	
	ib.bindElement = function(element) {
		var requestedWidget = element.getAttribute('data-widget');
		
		if (ib[requestedWidget]) {
			ib.bindWidget(element, ib[requestedWidget]);
		}
		else {
			console.log("Requested widget \""+requestedWidget+"\" does not exist.");
		}
	};
	
	ib.bindWidget = function(dom, widget)
	{
		if (typeof dom.widget === "undefined")
		{
			var newWidget = new widget(window, jQuery);
			newWidget.init(dom);
			dom.widget = newWidget;
		}
	};
	
	ib.config = function(name, configDefault) {
		if (typeof window.app !== "undefined" && typeof window.app[name] !== "undefined")
		{
			return window.app[name];
		}
		
		return configDefault;
	};
	
	ib.widget = function(name, widget) {
		
		if (ib[name] !== undefined) {
			console.log("Trying to re-declare widget \""+name+"\".");
			return false;
		}
		
		ib[name] = widget;
		return true;
	};
	
	ib.widgetArguments  = function(args) {
		var widget  = this;
		var target  = args[0];
		var options = args[1];
		
		if (typeof options !== "object") {
			options = {};
		}
		
		widget.options = $.extend(true, options, widget.defaults);
		
		if (typeof target !== "string" && $(target).length === 0) {
			target = widget.options.selector.widget;
		}
		var $widget = widget.$widget = $(target).first();
		
		if ($widget.length) {
			widget.bind.widget();
			return true;
		}
		
		return false;
	};
	
	$(document).on('ready', ib.bindAll);
	
	if (typeof InstantClick === "object")
	{
		InstantClick.on('change', ib.bindAll);
	}
	
	return ib;
})(window, jQuery);

/**
 * Message Widget
 */
ib.widget("notice", function(window, $, undefined) {
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
			$('html, body').animate( {
					scrollTop : $message.offset().top - $(".board-header").height() - 10
				},
				250
			);
			
			return $message;
		},
		
		init     : function(target, options) {
			window.ib.widgetArguments.call(widget, arguments);
		}
	};
	
	return widget;
});

/**
 * Stripe Cashier Form
 */
ib.widget("donate", function(window, $, undefined) {
	var widget = {
		// Short-hand for this widget's main object.
		$widget  : $(),
		
		notices  : null,
		
		// The default values that are set behind init values.
		defaults : {
			// Config options for this widget.
			config   : {
				'merchant'      : window.ib.config('merchant', false),
				'stripe-key'    : window.ib.config('stripe_key', false),
				'braintree-key' : window.ib.config('stripe_key', false)
			},
			
			// Selectors for finding and binding elements.
			selector : {
				'widget'             : "#payment-form",
				'notices'            : "[data-widget=notice]:first",
				
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
				'message-sent'      : "<div id=\"payment-process\">Securely Contacting Merchant</div>",
				'message-server'    : "<div id=\"payment-process\">Processing Transaction</div>",
				'thank-you'         : "<div id=\"payment-received\">Thank you!</div>"
			}
		},
		
		// Compiled settings.
		options  : false,
		
		// Event binding.
		bind     : {
			merchant : function() {
				
				switch (widget.options.config['merchant'])
				{
					case "braintree" :
						window.braintree.setup(window.ib.config('braintree_key'), "custom", {
							container: widget.options.selector['widget'],
						});
						break;
					
					case "stripe" :
						window.Stripe.setPublishableKey(widget.options.config['stripe-key']);
						break;
				}
				
			},
			
			widget : function() {
				
				widget.bind.merchant();
				
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
				
				widget.notices = $(widget.options.selector['notices'])[0].widget;
				console.log(widget.notices);
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
				if (data.amount !== false)
				{
					var $ty = $(widget.options.template['thank-you']);
					
					$(widget.options.selector['message']).replaceWith($ty);
					$ty.hide().fadeIn(500);
					setTimeout(function() { widget.$widget.unblock(); }, 1500);
					
					widget.notices.push("You were successfully charged for <strong>" + data.amount + "</strong>. Thank you for your support!", "success");
				}
				else
				{
					widget.$widget.unblock();
				}
				
				$.each(data.errors, function(index, error) {
					widget.notices.push(error, "error");
				});
			},
			
			ajaxFail     : function(data, textStatus, errorThrown) {
				console.log(data);
				
				widget.$widget.unblock();
				widget.notices.push("The server responded with an unknown error. You were not charged. Please report this issue.", "error");
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
					
					var cvcMax = result.card_type.name === "amex" ? 4 : 3;
					
					$(widget.options.selector['input-cvc'], widget.$widget).attr({
						'maxlength' : cvcMax,
						'size'      : cvcMax,
						'pattern'   : "[0-9]{"+cvcMax+"}"
					});
					
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
				widget.notices.clear();
				
				var valid = true;
				var sel   = widget.options.selector;
				
				// Make sure the CCN has been validated by the jQuery tool.
				var $ccn  = $(sel['input-ccn']);
				if (!$ccn.is(".control-valid"))
				{
					widget.notices.push("Please enter a valid credit card number.", 'error');
					$ccn.focus().trigger('focus');
					valid = false;
				}
				
				// Check to see if CVC is valid.
				var $cvc = $(sel['input-cvc']);
				if ((new RegExp("^"+$cvc.attr('pattern')+"$")).test($cvc.val()) === false)
				{
					widget.notices.push("Please enter a valid security code. It is three or four digits and found on the back of the card.", 'error');
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
					widget.notices.push("Double-check your expiration date. This card is invalid.", 'error');
					valid = false;
				}
				
				// See what amount we've entered.
				var $amountSel = $(sel['input-amount']).filter(":checked");
				var $amountInp = $(sel['input-amount-other']);
				var amount     = 0;
				if (!$amountSel.length)
				{
					widget.notices.push("Please enter an amount.", 'error');
					valid = false;
				}
				else if ($amountSel.val() == "Other")
				{
					amount = parseInt($amountInp.val(), 10);
					
					if (isNaN(amount) || amount <= 3)
					{
						widget.notices.push("Please enter a real amount that is greater than $3.", 'error');
						$amountInp.focus();
						valid = false;
					}
					else if (amount.toString() !== $amountInp.val())
					{
						widget.notices.push("Please enter a real, whole number as a donation amount.", 'error');
						$amountInp.focus();
						valid = false;
					}
				}
				
				if (valid)
				{
					var $form = $(this);
					
					$form.block({
						message : widget.options.template['message-sent'],
						theme   : true
					});
					
					// Disable the submit button to prevent repeated clicks
					$form.find('button').prop('disabled', true);
					
					// Send the information to our merchant.
					switch (widget.options.config['merchant'])
					{
						case "braintree" :
							var client = new braintree.api.Client({clientToken: window.ib.config('braintree_key')});
							
							client.tokenizeCard({
									number:          $ccn.val(),
									expirationMonth: $month.val(),
									expirationYear:  $year.val(),
									cvv:             $cvc.val()
								}, widget.events.braintreeResponse);
							
							break;
						
						case "stripe" :
							Stripe.card.createToken($form, widget.events.stripeResponse);
							break;
					}
					
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
			
			
			braintreeResponse : function(err, nonce) {
				var $form = widget.$widget;
				
				if (err) {
					// Show the errors on the form
					widget.notices.push(err, "error");
					
					$form.unblock();
					$form.find('button').prop('disabled', false);
				}
				else {
					// Response contains id and card, which contains additional card details
					var token = nonce;
					
					// Insert the token into the form so it gets submitted to the server
					$form.append($('<input type="hidden" name="nonce" />').val(token));
					
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
			},
			
			stripeResponse : function(status, response) {
				var $form = widget.$widget;
				
				if (response.error) {
					// Show the errors on the form
					widget.notices.push(response.error.message, "error");
					
					$form.unblock();
					$form.find('button').prop('disabled', false);
				}
				else {
					// Response contains id and card, which contains additional card details
					var token = response.id;
					
					// Insert the token into the form so it gets submitted to the server
					$form.append($('<input type="hidden" name="nonce" />').val(token));
					
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
			return window.ib.widgetArguments.call(widget, arguments);
		}
	};
	
	return widget;
});

/**
 * Single post widget
 */
ib.widget("post", function(window, $, undefined) {
	var widget = {
		// Short-hand for this widget's main object.
		$widget  : $(),
		
		// The default values that are set behind init values.
		defaults : {
			// Selectors for finding and binding elements.
			selector : {
				'widget'         : ".post-container",
				
				'mode-reply'     : "main.mode-reply",
				'mode-index'     : "main.mode-index",
				
				'post-reply'     : ".post-reply",
				
				'elementCode'    : "pre code",
				'elementQuote'   : "blockquote",
				
				'post-form'      : "#post-form",
				'post-form-body' : "#body",
				
				'attacment-expand'   : "li.post-attachment:not(.attachment-expanded) a.attachment-link",
				'attacment-collapse' : "li.post-attachment.attachment-expanded a.attachment-link",
				'attachment-image'   : "img.attachment-img",
				'attachment-image-download'   : "img.attachment-type-file",
				'attachment-image-expandable' : "img.attachment-type-img",
				'attachment-image-audio'      : "img.attachment-type-audio",
				'attachment-image-video'      : "img.attachment-type-video",
				'attachment-inline'  : "audio.attachment-inline, video.attachment-inline"
			},
		},
		
		// Compiled settings.
		options  : false,
		
		// Events
		events   : {
			attachmentCollapseClick : function(event) {
				if(event.altKey || event.shiftKey || event.ctrlKey)
				{
					return true;
				}
				
				var $link   = $(this);
				var $item   = $link.parents("li.post-attachment");
				var $img    = $(widget.options.selector['attachment-image'], $link);
				var $inline = $(widget.options.selector['attachment-inline'], $item);
				
				$item.removeClass('attachment-expanded');
				$img.attr('src', $link.attr('data-thumb-url'));
				$inline.remove();
				$img.toggle(true);
				
				event.preventDefault();
				return false;
			},
			
			attachmentExpandClick : function(event) {
				// We don't do anything if the user is CTRL+Clicking.
				if (event.ctrlKey)
				{
					return true;
				}
				
				var $link = $(this);
				var $item = $link.parents("li.post-attachment");
				var $img  = $(widget.options.selector['attachment-image'], $link);
				
				// If the attachment type is not an image, we can't expand inline.
				if ($img.is(widget.options.selector['attachment-image-expandable']))
				{
					$item.addClass('attachment-expanded');
					
					$img
						// Blur the image while it loads so the user understands there is a loading action.
						.css('opacity', 0.5)
						// Bind an event to handle the image loading.
						.one("load", function() {
							// Remove our opacity change.
							$(this).css('opacity', "");
						})
						// Finally change the source of our thumb to the full image.
						.attr('src', $link.attr('data-download-url'));
					
				}
				else if ($img.is(widget.options.selector['attachment-image-audio']))
				{
					var $audio  = $("<audio controls autoplay class=\"attachment-inline attachment-audio\"></audio>");
					var $source = $("<source />");
					
					if ($audio[0].canPlayType($img.attr('data-mime')))
					{
						$item.addClass('attachment-expanded');
						
						$source
							.attr('src',  $link.attr('href'))
							.attr('type', $img.attr('data-mime'))
							.one('error', function(event) {
								// Our source has failed to load!
								// Trigger a download.
								$img
									.trigger('click')
									.removeClass('attachment-type-audio')
									.addClass('attachment-type-download');
							})
							.appendTo($audio);
						
						$audio.insertBefore($link);
					}
					else
					{
						return true;
					}
				}
				else if ($img.is(widget.options.selector['attachment-image-video']))
				{
					var $video  = $("<video controls autoplay class=\"attachment-inline attachment-video\"></video>");
					var $source = $("<source />");
					
					if ($video[0].canPlayType($img.attr('data-mime')))
					{
						$item.addClass('attachment-expanded');
						
						$source
							.attr('src',  $link.attr('href'))
							.attr('type', $img.attr('data-mime'))
							.one('error', function(event) {
								console.log(1);
								// Our source has failed to load!
								// Trigger a download.
								$img
									.trigger('click')
									.removeClass('attachment-type-video')
									.addClass('attachment-type-download');
							})
							.appendTo($video);
						
						$img.toggle(false);
						
						$video
							.insertBefore($link);
					}
					else
					{
						return true;
					}
				}
				else
				{
					return true;
				}
				
				event.preventDefault();
				return false;
			},
			
			codeHighlight : function() {
				// Activate code highlighting if the JS module is enabled.
				if (typeof hljs === "object") {
					$(widget.defaults.selector.elementCode, widget.$widget).each(function(index, element) {
						hljs.highlightBlock(element);
					});
				}
			},
			
			postClick : function(event) {
				if ($(widget.options.selector['mode-reply']).length !== 0)
				{
					event.preventDefault();
					
					var $this = $(this);
					var $body = $(widget.options.selector['post-form-body']);
					
					$body
						.val($body.val() + ">>" + $this.data('board_id') + "\n")
						.focus();
					
					return false;
				}
				
				return true;
			}
		},
		
		// Event bindings
		bind     : {
			widget : function() {
				
				widget.events.codeHighlight();
				
				widget.$widget
					.on('click.ib-post', widget.options.selector['post-reply'],         widget.events.postClick)
					.on('click.ib-post', widget.options.selector['attacment-expand'],   widget.events.attachmentExpandClick)
					.on('click.ib-post', widget.options.selector['attacment-collapse'], widget.events.attachmentCollapseClick)
				;
				
			}
		},
		
		build    : {
			
		},
		
		init     : function(target, options) {
			return window.ib.widgetArguments.call(widget, arguments);
		}
	};
	
	return widget;
});

/**
 * Post form widget
 */
ib.widget("postbox", function(window, $, undefined) {
	var widget = {
		// Short-hand for this widget's main object.
		$widget  : $(),
		
		// The default values that are set behind init values.
		defaults : {
			
			captchaUrl : "/cp/captcha",
			
			// Selectors for finding and binding elements.
			selector : {
				'widget'          : "#post-form",
				
				'captcha'         : ".captcha",
				'captcha-row'     : ".row-captcha",
				'captcha-field'   : ".field-control"
			},
		},
		
		// Compiled settings.
		options  : false,
		
		// Events
		events   : {
			
			captchaClick : function(event) {
				var $captcha = $(this),
					$parent  = $captcha.parent(),
					$hidden  = $captcha.next(),
					$field   = $captcha.parents(widget.options.selector['captcha-row']).children(widget.options.selector['captcha-field']);
				
				$parent.addClass("captcha-loading");
				$field.val("").focus();
				
				jQuery.getJSON(widget.options.captchaUrl + ".json", function(data) {
					$captcha.attr('src', widget.options.captchaUrl + "/" + data['hash_string'] + ".png");
					$hidden.val(data['hash_string']);
				});
				
				event.preventDefault();
				return false;
			},
			
			captchaLoad : function(event) {
				var $captcha = $(this),
					$parent  = $captcha.parent();
				
				$parent.removeClass("captcha-loading");
			}
			
		},
		
		// Event bindings
		bind     : {
			widget : function() {
				
				$(widget.options.selector['captcha'])
					// Load events cannot be tied on parents.
					// Watch for source changes on the captcha.
					.on('load.ip-postbox', widget.events.captchaLoad);
				
				
				widget.$widget
					// Watch for captcha clicks.
					.on('click.ib-postbox', widget.options.selector['captcha'], widget.events.captchaClick)
				;
				
			}
		},
		
		build    : {
			
		},
		
		init     : function(target, options) {
			return window.ib.widgetArguments.call(widget, arguments);
		}
	};
	
	return widget;
});

/**
 * Autoupdater widget
 */
ib.widget("autoupdater", function(window, $, undefined) {
	
	var widget = {
		// Short-hand for this widget's main object.
		$widget  : $(),
		
		// The default values that are set behind init values.
		defaults : {
			// Selectors for finding and binding elements.
			selector : {
				'widget'         : "#autoupdater",
				
				'enabled'        : "#autoupdater-enabled",
				'timer'          : "#autoupdater-timer",
				'force-update'   : "#autoupdater-update",
				'updating'       : "#autoupdater-updating",
			},
		},
		
		// Compiled settings.
		options  : false,
		
		updating    : false,
		updateTimer : false,
		updateURL   : false,
		updateAsked : false,
		updateLast  : parseInt(parseInt(Date.now(), 10) / 1000, 10),
		
		// Events
		events   : {
			
			update : function() {
				if (!widget.updating)
				{
					$(widget.options.selector['force-update'])
						.hide();
					$(widget.options.selector['updating'])
						.show();
					
					clearInterval(widget.updateTimer);
					
					$.ajax(widget.updateURL, {
						data : {
							'updatesOnly'  : 1,
							'updateHtml'   : 1,
							'updatedSince' : widget.updateLast,
						}
					})
						.done(widget.events.updateSuccess)
						.always(widget.events.updateComplete);
					
					widget.updating    = true;
					widget.updateTimer = false;
					widget.updateAsked = parseInt(parseInt(Date.now(), 10) / 1000, 10);
				}
			},
			
			updateSuccess : function(data, textStatus, jqXHR) {
				if (data instanceof Array)
				{
					widget.updateLast = widget.updateAsked;
					
					$.each(data, function(index, reply)
					{
						var $existingPost = $(".post-" + reply.post_id);
						
						if ($existingPost.length > 0)
						{
							if (reply.html !== null)
							{
								$newPost      = $(reply.html);
								$existingPost.replaceWith($newPost);
								ib.bindElement($newPost[0]);
							}
							else
							{
								$existingPost.addClass('post-deleted');
							}
						}
						else if(reply.html !== null)
						{
							$("<li class=\"thread-reply\"><article class=\"reply\">"+reply.html+"</article></li>")
								.insertBefore(widget.$widget);
						}
					});
				}
			},
			
			updateComplete : function() {
				widget.updating = false;
				
				$(widget.options.selector['force-update'])
					.show();
				$(widget.options.selector['updating'])
					.hide();
				
				clearInterval(widget.updateTimer);
				widget.updateTimer = setInterval(widget.events.updateInterval, 1000);
			},
			
			updateInterval : function() {
				if ($(widget.options.selector['enabled']).is(":checked"))
				{
					var $timer = $(widget.options.selector['timer'], widget.$widget);
					var time   = parseInt($timer.attr('data-time'), 10);
					
					if (isNaN(time))
					{
						time = 10;
					}
					else
					{
						--time;
						
						if (time <= 0)
						{
							time = 10;
							
							widget.$widget.trigger('au-update');
						}
					}
					
					$timer
						.text(time+'s')
						.attr('data-time', time);
				}
				
				clearInterval(widget.updateTimer);
				widget.updateTimer = setInterval(widget.events.updateInterval, 1000);
			},
			
			updaterUpdateClick : function() {
				var $timer = $(widget.options.selector['timer'], widget.$widget);
				
				$timer.attr('data-time', 10);
				widget.events.update();
			}
			
		},
		
		// Event bindings
		bind     : {
			timer  : function() {
				var url   = widget.$widget.data('url');
				
				if (url)
				{
					widget.updateURL = url;
					widget.$widget.show();
					
					clearInterval(widget.updateTimer);
					widget.updateTimer = setInterval(widget.events.updateInterval, 1000);
				}
			},
			
			widget : function() {
				
				$(widget.options.selector['force-update'])
					.show();
				$(widget.options.selector['updating'])
					.hide();
				
				widget.$widget
					.on('au-update',   widget.events.update)
					.on('click.ib-au', widget.options.selector['force-update'], widget.events.updaterUpdateClick)
				;
				
				widget.bind.timer();
			}
		},
		
		build    : {
			
		},
		
		init     : function(target, options) {
			return window.ib.widgetArguments.call(widget, arguments);
		}
	};
	
	return widget;
})