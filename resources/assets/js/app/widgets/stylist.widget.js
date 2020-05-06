// ===========================================================================
// Purpose          : Custom Styling
// Contributors     : jaw-sh
// Widget Version   : 2
// ===========================================================================

(function(window, $, undefined) {
    // Widget blueprint
    var blueprint = ib.getBlueprint();

    var events = {
        onCssChange : function (event) {
            var setting = event.data.setting.get();
            var domObj  = document.getElementById('user-css');
            domObj.innerHTML = setting;
        },

        onThemeChange : function (event) {
            var setting = event.data.setting.get();
            var domObj  = document.getElementById('theme-stylesheet');

            if (setting) {
                domObj.href = window.app.url + "/static/css/skins/" + setting;
            }
            else {
                domObj.href = "";
            }
        },

        onTheme3rdPartyChange : function (event) {
            var setting = event.data.setting.get();
            $(document.body).toggleClass('light', !setting).toggleClass('night', setting);
        }
    };

    // Configuration options
    var options = {
        theme : {
            default : "",
            type    : "select",
            values  : [
                "",
                "next-yotsuba.css",
                "next-dark.css",
                "next-tomorrow.css",
                "kappa-burichan.css",
            ],
            onChange : events.onThemeChange,
            onUpdate : events.onThemeChange
        },

        theme_3rd_party : {
            type : "bool",
            initial : false,
            onChange : events.onTheme3rdPartyChange,
            onUpdate : events.onTheme3rdPartyChange
        },

        css : {
            default : "",
            type    : "textarea",
            onChange : events.onCssChange,
            onUpdate : events.onCssChange
        },
    };

    blueprint.prototype.bind = function() {
        var widget  = this;
        var $widget = this.$widget;
        var data    = {
            widget  : widget,
            $widget : $widget
        };

        var night = this.is('theme_3rd_party');
        $(document.body).toggleClass('light', !night).toggleClass('night', night);
    };

    blueprint.prototype.defaults = {
        //
    };

    blueprint.prototype.events = {
        //
    };

    ib.widget("stylist", blueprint, options);
})(window, window.jQuery);
