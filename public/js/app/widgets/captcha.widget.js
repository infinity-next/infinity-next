/**
 * Captcha widget
 */
ib.widget("captcha", function(window, $, undefined) {
	var widget = {
		
		// The default values that are set behind init values.
		defaults : {
			
			captchaUrl    : "/cp/captcha",
			
			// Selectors for finding and binding elements.
			selector : {
				'captcha'         : ".captcha",
			}
		},
		
		// Events
		events   : {
			captchaLoad : function(event) {
				var $captcha = $(this),
					$parent  = $captcha.parent();
				
				$parent.removeClass("captcha-loading");
			},
			
			captchaReload : function() {
				var $captcha = $(widget.options.selector['captcha'], widget.$widget),
					$parent  = $captcha.parent(),
					$hidden  = $captcha.next(),
					$field   = $captcha.parents(widget.options.selector['captcha-row']).children(widget.options.selector['captcha-field']);
				
				$parent.addClass("captcha-loading");
				$field.val("").focus();
				
				jQuery.getJSON(widget.options.captchaUrl + ".json", function(data) {
					$captcha.attr('src', widget.options.captchaUrl + "/" + data['hash_string'] + ".png");
					$hidden.val(data['hash_string']);
				});
			}
		},
		
		// Event bindings
		bind     : {
			widget : function() {
				
				$(widget.options.selector['captcha'])
					// Load events cannot be tied on parents.
					// Watch for source changes on the captcha.
					.on('load.ip-captcha', widget.events.captchaLoad);
				
				widget.$widget
					// Watch for captcha clicks.
					.on('reload.ib-captcha',                                     widget.events.captchaReload)
					.on('reload.ib-captcha', widget.options.selector['captcha'], widget.events.captchaReload)
					.on('click.ib-captcha',  widget.options.selector['captcha'], widget.events.captchaReload);
				
			}
		}
		
	};
	
	return widget;
});