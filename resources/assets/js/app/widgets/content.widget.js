// ===========================================================================
// Purpose          : Content
// Contributors     : jaw-sh
// Widget Version   : 2
// ===========================================================================

(function(window, $, undefined) {
    // Widget blueprint
    var blueprint = ib.getBlueprint();

    // Content events
    var events = {
        doContentUpdate : function(event) {
            blueprint.prototype.adjustDisplay(event.data.setting.get());
        }
    };

    // Configuration options
    var options = {
        sfw : {
            type : "bool",
            initial : false,
            onChange : events.doContentUpdate,
            onUpdate : events.doContentUpdate
        }
    };

    blueprint.prototype.adjustDisplay = function(sfw) {
        var widget  = this;

        var sfw = widget.is('sfw');
        $("body").toggleClass('nsfw-filtered', sfw);
        $("body").toggleClass('nsfw-allowed', !sfw);

        // if (sfw) {
        //     var $ob = $(this.options.selector['overboard-nav']);
        //     $ob.attr('href', $ob.attr('href') + '/sfw');
        // }

        // var $pageStylesheet = $(widget.defaults.selector['page-stylesheet']);
        // $pageStylesheet.attr('href', sfw
        //     ? $pageStylesheet.data('empty')
        //     : widget.defaults.nsfw_skin
        // );
    };

    // Event bindings
    blueprint.prototype.bind = function() {
        var widget  = this;
        var $widget = this.$widget;
        var data    = {
            widget  : widget,
            $widget : $widget
        };

        widget.adjustDisplay(widget.is('sfw'));
    };

    blueprint.prototype.defaults = {
        nsfw_skin : "/static/css/skins/next-yotsuba.css",

        selector : {
            'page-stylesheet' : "#page-stylesheet",
            'overboard-nav'   : ".item-recent_posts .gnav-link"
        }
    };

    blueprint.prototype.events = {
    };

    ib.widget("content", blueprint, options);
})(window, window.jQuery);
