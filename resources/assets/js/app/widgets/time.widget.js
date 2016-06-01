// ===========================================================================
// Purpose          : Timestamp Configuration
// Contributors     : jaw-sh
// Widget Version   : 2
// ===========================================================================

(function(window, $, undefined) {
    // Widget blueprint
    var blueprint = ib.getBlueprint();

    // Configuration options
    var options = {
        // Date Format
        format : {
            type : "select",
            initial : "YYYY-MMM-DD HH:MM:SS",
            values : [
                'YYYY-MMM-DD HH:MM:SS',
                'MM/DD/YY(DDD)HH:MM:SS'
            ],
            onChange : function(event) {
                // On setting update, trigger reformating..
                var setting = event.data.setting;
                var widget  = setting.widget;
                ib.getInstances(widget).trigger('reformat.ib-time');
            },
            onUpdate : function(event) {
                // On storage update, trigger reformating.
                var setting = event.data.setting;
                var widget  = setting.widget;

                for (var i = 0; i < ib.widgets[widget].instances.length; ++i)
                {
                    var instance = ib.widgets[widget].instances[i];
                    instance.$widget.trigger('reformat.ib-time');
                }
            }
        }
    };

    // Main Widget Initialization Binding
    blueprint.prototype.bind = function() {
        var data = {
            widget  : this,
            $widget : this.$widget
        };

        this.$widget
            .on('reformat.ib-time', data, this.events.timeReformat)
            .trigger('reformat.ib-time');
    };

    // Event hooks
    blueprint.prototype.events = {
        // Reformat timestamps on command.
        timeReformat : function(event) {
            // Fetch our existing information.
            var widget = event.data.widget;
            var text   = event.data.$widget.text();
            var time   = new Date(event.data.$widget.attr('datetime'));

            var y = ib.lpad(time.getFullYear(), 2, "0");
            var m = ib.lpad(time.getMonth() + 1, 2, "0");
            var d = ib.lpad(time.getDate(), 2, "0");
            var hour = ib.lpad(time.getHours(), 2, "0");
            var min  = ib.lpad(time.getMinutes(), 2, "0");
            var sec  = ib.lpad(time.getSeconds(), 2, "0");

            switch (widget.get('format'))
            {
                case 'YYYY-MMM-DD HH:MM:SS' :
                    // Translate 0 into Dec
                    m = ib.trans("time.calendar.abbrevmonths."+time.getMonth());
                    text = y+"-"+m+"-"+d+" "+hour+":"+min+":"+sec;
                    break;

                case 'MM/DD/YY(DDD)HH:MM:SS' :
                    var dow = time.getDay();
                    dow = ib.trans("time.calendar.abbrevdays." + dow);

                    text = m+"/"+d+"/"+y+"("+dow+")"+hour+":"+min+":"+sec;
                    break;

                default :
                    console.log("Invalid format \""+this.get('format')+"\"");
                    break;
            }

            event.data.$widget.text(text);
        }
    };

    ib.widget("time", blueprint, options);
})(window, window.jQuery);
