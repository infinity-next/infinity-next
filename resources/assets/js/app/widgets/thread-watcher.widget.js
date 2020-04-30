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
        container : "<div class=\"watched-threads\"></div>",
        thread : "<div class=\"watched-thread\"></div>",
        unwatch : "<span class=\"watched-unwatch\"><i class=\"fas fa-minus-circle unwatch\"></i>&nbsp;</span>",
        link : "<a class=\"watched-link\"></a>"
    };

    blueprint.prototype.defaults = {
        selector : {
            threads : ".watched-threads",
            unwatch : ".watched-unwatch"
        }
    };

    blueprint.prototype.events = {
        storage : function (event) {
            if (event.originalEvent.key == "watchThreads") {
                event.data.widget.buildWatchlist();
            }
        },

        unwatchClick : function (event) {
            var widget = event.data.widget;
            var $widget = event.data.$widget;
            var id = $(this).parent().data('post_id');

            ib.threadUnwatch(id);
            widget.buildWatchlist();
        }
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

        widget.buildWatchlist();
        widget.bindDraggable();

        $widget.on('click.ib-thread-watcher', widget.options.selector.unwatch, data, widget.events.unwatchClick);
        $(window).on('storage.ib-thread-watcher', data, widget.events.storage);
    };

    blueprint.prototype.bindDraggable = function () {
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


    blueprint.prototype.buildWatchlist = function () {
        var $widget = this.$widget;
        var $threads = $(this.options.selector.threads, $widget);
        var storage = ib.getThreadsWatched();

        $threads.empty();

        for (var threadId in storage) {
            var thread = storage[threadId];
            var $thread = $(this.templates.thread);
            var $link = $(this.templates.link);

            $link
                .attr('href', "/" + thread.board_uri + "/thread/" + thread.board_id)
                .text("/" + thread.board_uri + "/ - " + thread.excerpt)
            ;

            $thread.data({
                post_id : threadId,
                bumped_last : thread.bumped_last
            });

            $thread.append(this.templates.unwatch, $link);
            $threads.append($thread);
        }
    };

    blueprint.prototype.update = function () {
        var lastUpdate = parseInt(localStorage.getItem('watchThread.lastUpdate')  || 0, 10);
        var now = Date.now();

        if (lastUpdate + 30000 < now) {
            localStorage.setItem('watchThread.lastUpdate', now);

            var threads = ib.getThreadsWatched();
            var payload = {};

            for (thread in threads) {
                payload[thread] = threads[thread].bumped_last;
            }

            $.post("/watcher.json", payload, function (data) {
                var storage = ib.getThreadsWatched(); // fetch again because async
                localStorage.setItem('watchThread.lastUpdate', Date.now());

                for (datum in data) {
                    threads[datum].bumped_last = Math.max(threads[datum].bumped_last, data[datum] || 0);
                }

                console.log(JSON.stringify(storage));
                localStorage.setItem("watchThreads", JSON.stringify(storage));
            });
        }
    };


    // Setup
    ib.widget("thread-watcher", blueprint, options);

    ib.threadUnwatch = function(id) {
        var storage = this.getThreadsWatched();
        var post = null;

        if (typeof storage !== 'undefined') {
            post = document.getElementById('post-' + storage[id].board_uri + "-" + storage[id].board_id)?.widget;
        }

        delete storage[id];

        localStorage.setItem("watchThreads", JSON.stringify(storage));

        document.getElementById('thread-watcher')?.widget?.buildWatchlist();
        post?.updateHeart();
        return storage;
    }
    ib.threadWatch = function(id, data) {
        var storage = this.getThreadsWatched();

        storage[id] = data;

        localStorage.setItem("watchThreads", JSON.stringify(storage));

        document.getElementById('thread-watcher')?.widget?.buildWatchlist();
        return storage;
    }
    ib.getThreadsWatched = function(id) {
        var storage = JSON.parse(localStorage.getItem("watchThreads"));
        storage = (storage === 'undefined' || typeof storage !== 'object' || storage instanceof Array) ? {} : storage;

        if (typeof id === 'undefined') {
            return storage;
        }

        return storage[id];
    }

    // Inject the widget
    document.addEventListener("DOMContentLoaded", function() {
        $(document.body).append(blueprint.prototype.templates.base);
        ib.bindElement(document.getElementById('thread-watcher'));
    });
})(window, window.jQuery);
