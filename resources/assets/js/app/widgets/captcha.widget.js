/**
 * Captcha widget
 */
ib.widget("captcha", function(window, $, undefined) {
    var widget = {

        // The default values that are set behind init values.
        defaults : {

            captchaUrl    : window.app.panel_url+"captcha",
            reloadUrl     : window.app.panel_url+"captcha/replace.json",

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

            /**
             * Captcha IMAGE Load
             *
             * @param  object  event
             */
            captchaLoad : function(event) {
                var $captcha = $(this),
                    $parent  = $captcha.parent();

                $parent.removeClass("captcha-loading");
            },

            captchaLoadIn : function(event, captcha) {
                var $captcha = $(widget.options.selector['captcha'], widget.$widget),
                    $hidden  = $captcha.next(),
                    $field   = $captcha.parent().children("input"),
                    url      = widget.options.captchaUrl + "/" + captcha['hash_string'] + ".jpg";

                widget.$widget.data('replacing', false);
                $captcha
                    .attr('data-expires-at', captcha.expires_at)
                    .data('expires-at', captcha.expires_at);

                if ($captcha.attr('src') != url) {
                    $field.val("");
                    $captcha.attr('src', url);
                    $hidden.val(captcha['hash_string']);
                }

            },

            captchaReload : function() {
                var $captcha = $(widget.options.selector['captcha'], widget.$widget),
                    $parent  = $captcha.parent(),
                    $field   = $captcha.parent().children("input");

                widget.$widget.data('replacing', true);
                $parent.addClass("captcha-loading");
                $field.val("").focus();

                jQuery.getJSON(widget.options.reloadUrl, function(data) {
                    widget.$widget.trigger('load', data);
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
                    .data('replacing', false)

                    // Watch for captcha clicks.
                    .on('load.ib-captcha',                                       widget.events.captchaLoadIn)
                    .on('reload.ib-captcha',                                     widget.events.captchaReload)
                    .on('click.ib-captcha',  widget.options.selector['captcha'], widget.events.captchaReload);

            }
        }

    };

    return widget;
});
