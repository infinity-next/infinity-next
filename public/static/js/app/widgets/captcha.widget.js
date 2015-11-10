/**
 * Captcha widget
 */
ib.widget("captcha", function(window, $, undefined) {
	var widget = {
		
		// The default values that are set behind init values.
		defaults : {
			
			captchaUrl    : "/cp/captcha",
			reloadUrl     : "/cp/captcha/replace.json",
			
			// Selectors for finding and binding elements.
			selector : {
				'captcha'         : ".captcha",
			}
		},
		
		// Events
		events   : {
			captchaAjaxFail : function(jqXHR, textStatus, errorThrown) {
				if (jqXHR.status == 429) {
					// Retry again in a second.
					setTimeout(function() {
						widget.events.captchaReload();
					}, 1000);
				}
			},
			
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
				
				jQuery.getJSON(widget.options.reloadUrl, function(data) {
					$captcha.attr('src', widget.options.captchaUrl + "/" + data['hash_string'] + ".png");
					$hidden.val(data['hash_string']);
				})
				.fail(widget.events.captchaAjaxFail);
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