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
        nav : "<span class=\"boardlist-item\"><i class=\"boardlist-link fas fa-eye\"></i></span>",
        gnav : "<li class=\"gnav-item item-config require-js\"><i class=\"gnav-link fas fa-eye\"></i></li>",

        base : "<div id=\"thread-watcher\" class=\"dialog\" data-widget=\"thread-watcher\"></div>",
        handle : "<div class=\"watched-legend\"></div>",
        legend : "<span class=\"watched-name move\">:legend&nbsp;</span>",
        refresh : "<span class=\"watched-refresh\"><i class=\"fas fa-sync\"></i></span></div>",
        move : "<span class=\"watched-space move\"></span>",
        close : "<span class=\"watched-close\"><i class=\"fas fa-times\"></i></span></div>",

        container : "<div class=\"watched-threads\"></div>",
        thread : "<div class=\"watched-thread\"></div>",
        unwatch : "<span class=\"watched-unwatch\"><i class=\"fas fa-minus-circle unwatch\"></i>&nbsp;</span>",
        link : "<a class=\"watched-link\"></a>",
        unseen : "<span class=\"watched-new\">(:unseen)&nbsp;</span>"
    };

    blueprint.prototype.defaults = {
        selector : {
            close : ".watched-close",
            threads : ".watched-threads",
            refresh : ".watched-refresh",
            unwatch : ".watched-unwatch"
        }
    };

    blueprint.prototype.events = {
        closeClick : function (event) {
            event.data.$widget.hide();
        },

        navClick : function (event) {
            event.data.$widget.show();
        },

        refreshClick : function (event) {
            event.data.widget.update();
        },

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

        $widget.append($(widget.templates.handle).append(
            widget.templates.legend.replace(':legend', ib.trans('thread-watcher.legend')),
            widget.templates.refresh,
            widget.templates.move,
            widget.templates.close
        ));
        $widget.append(widget.templates.container);

        widget.buildWatchlist();
        widget.bindDraggable();

        $widget
            .on('click.ib-thread-watcher', widget.options.selector.close, data, widget.events.closeClick)
            .on('click.ib-thread-watcher', widget.options.selector.refresh, data, widget.events.refreshClick)
            .on('click.ib-thread-watcher', widget.options.selector.unwatch, data, widget.events.unwatchClick)
        ;

        $(window).on('storage.ib-thread-watcher', data, widget.events.storage);

        // bar nav
        var $nav = $(widget.templates.nav);
        $nav.insertBefore($(".boardlist-link:last"));

        // global nav
        var $gnav = $(widget.templates.gnav);
        $gnav.insertAfter($(".gnav-item.item-config:last"));

        $nav.on('click.ib-thread-watcher', data, widget.events.navClick);
        $gnav.on('click.ib-thread-watcher', data, widget.events.navClick);

        $widget.hide();

        setInterval(function () {
            widget.update();
        }, 10000);
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
            var hash = "";

            if (thread.last_seen || false) {
                hash = "#post-" + thread.board_uri + "-" + thread.last_seen;
            }

            $link
                .attr('id', "watched-link-" + thread.post_id)
                .attr('href', "/" + thread.board_uri + "/thread/" + thread.board_id + hash)
                .text("/" + thread.board_uri + "/ - " + thread.excerpt)
            ;

            $thread.data({
                post_id : threadId,
                bumped_last : thread.bumped_last
            });

            $thread.append(
                this.templates.unwatch,
                this.templates.unseen.replace(":unseen", thread.unseen || 0),
                $link
            );
            $threads.append($thread);
        }

        return $("#thread-watcher");
    };

    blueprint.prototype.update = function () {
        //var lastUpdate = parseInt(localStorage.getItem('watchThread.lastUpdate')  || 0, 10);
        var now = Date.now();

        //if (lastUpdate + 30000 < now) {
            localStorage.setItem('watchThread.lastUpdate', now);

            var threads = ib.getThreadsWatched();
            var payload = {};

            for (thread in threads) {
                payload[thread] = threads[thread].bumped_last;
            }

            $.post("/watcher.json", { threads : payload }, function (data) {
                var storage = ib.getThreadsWatched(); // fetch again because async
                localStorage.setItem('watchThread.lastUpdate', Date.now());

                for (datum in data) {
                    storage[datum].unseen = data[datum];
                }

                localStorage.setItem("watchThreads", JSON.stringify(storage));
                document.getElementById('thread-watcher')?.widget?.buildWatchlist();
            });
        //}
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
        storage = (storage === null || typeof storage === 'undefined' || typeof storage !== 'object' || storage instanceof Array) ? {} : storage;

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
