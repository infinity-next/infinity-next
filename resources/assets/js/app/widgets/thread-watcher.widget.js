// ===========================================================================
// Purpose          : Thread Watcher
// Contributors     : Joshua Moon <josh@9chan.org>
// Widget Version   : 2
// ===========================================================================

(function(window, $, undefined) {
    // Widget blueprint
    var blueprint = ib.getBlueprint();

    // Configuration options
    var options = {
        //sfw : {
        //    type : "bool",
        //    initial : false,
        //    onChange : events.doContentUpdate,
        //    onUpdate : events.doContentUpdate
        //}
    };

    blueprint.prototype.templates = {
        base : "<div id=\"thread-watcher\" class=\"dialog\" data-widget=\"thread-watcher\"></div>",
        handle : "<div class=\"move\">Thread Watcher</div>",
        container : "<div class=\"watched-threads\"></div>"
    };

    blueprint.prototype.defaults = {
    };

    blueprint.prototype.events = {
    };

    // Event bindings
    blueprint.prototype.bind = function() {
        var widget  = this;
        var $widget = this.$widget;
        var data    = {
            widget  : widget,
            $widget : $widget
        };

        $widget.append(widget.templates.handle);
        $widget.append(widget.templates.container);
        widget.bindDraggable();

        $(window).on('storage.ib-thread-watcher', data, widget.events.storage);
    };


    blueprint.prototype.bindDraggable = function() {
        var widget   = this;
        var $widget  = this.$widget;

        if (!ib.isMobile()) {
            $widget.draggable({
                containment : "window",
                handle      : ".move"
            });

            widget.draggable = true;
        }
    };

    // Setup
    ib.widget("thread-watcher", blueprint, options);

    ib.threadUnwatch = function(id) {
        if (typeof window.localStorage !== "object") {
            return [];
        }

        try {
            var storage = localStorage.getItem("watchThreads").split(",");
        }
        catch (e) {
            var storage = [];
        }

        id = id.toString();
        storage = storage.filter(i => i !== id);

        localStorage.setItem("watchThreads", storage.join(","));
        return storage;
    }
    ib.threadWatch = function(id) {
        if (typeof window.localStorage !== "object") {
            return [];
        }

        try {
            var storage = localStorage.getItem("watchThreads").split(",");
        }
        catch (e) {
            var storage = [];
        }

        id = id.toString();
        if (storage.indexOf(id) === -1) {
            storage.push(id);
            storage = storage.filter(e => !!e);
        }
        else {
            storage = storage.filter((v, i, a) => a.indexOf(v) === i)
        }

        localStorage.setItem("watchThreads", storage.join(","));
        return storage;
    }

    // Inject the widget
    document.addEventListener("DOMContentLoaded", function() {
        $(document.body).append(blueprint.prototype.templates.base);
        ib.bindElement(document.getElementById('thread-watcher'));
    });
})(window, window.jQuery);
