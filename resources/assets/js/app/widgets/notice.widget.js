/**
 * Message Widget
 */
ib.widget("notice", function(window, $, undefined) {
    var widget = {

        // The default values that are set behind init values.
        defaults : {
            // Selectors for finding and binding elements.
            selector : {
                'widget'        : ".form-messages",
                'message'       : ".form-message",
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
                widget.$widget
                    .on('click.ib-notice', widget.options.selector['message'], widget.events.noticeClick)
                ;
            }
        },

        events   : {

            noticeClick : function(event) {
                var $this = $(this);

                // Make sure we don't hijack and link clicks.
                if ($(this.toElement).is('[href]')) {
                    return true;
                }

                // Fade out and remove the notice very quickly after clicking it.
                $this.fadeOut(250, function() {
                    $this.remove();
                });

                event.preventDefault();
                return false;
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
            if ($message.offsetParent().css('position') !== "fixed") {
                $('html, body').animate(
                    { scrollTop : $message.offset().top - $(".board-header").height() - 10 },
                    250
                );
            }

            return $message;
        }

    };

    return widget;
});
