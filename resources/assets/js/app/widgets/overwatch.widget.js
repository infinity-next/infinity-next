// ===========================================================================
// Purpose          : Lazy Image Loading
// Contributors     : jaw-sh
// Widget Version   : 2
// ===========================================================================

(function(window, $, undefined) {
    var blueprint = ib.getBlueprint();

    var options = {};

    blueprint.prototype.defaults = {
        refresh  : 3000,//ms
        interval : 1000,//ms

        selector : {
            'toggle' : '.overwatch-toggle',
            'label-pause' : '.label-pause',
            'label-reading' : '.label-reading',

            'logo' : "#logo",

            'thread-list' : '#CatalogMix',
            'thread-item' : '.thread',
            'post-container' : '.post-container',
        }
    };

    /* https://mixitup.kunkalabs.com/docs */
    blueprint.prototype.mixItUp = {
        animation: {
            enabled: false
        },
        load: {
            sort: 'bumped:desc'
        },
        callbacks: {
            onMixEnd: function(state) {
                state.$targets.css({
                    transition: "",
                    transform: "",
                    display: 'inline-block'
                });
            }
        }
    };

    blueprint.prototype.scanning       = false;
    blueprint.prototype.paused         = false;
    blueprint.prototype.hasFocus       = false;
    blueprint.prototype.updating       = false;
    blueprint.prototype.updateLast     = false;
    blueprint.prototype.updateLastSeen = false;
    blueprint.prototype.updateTimer    = false;
    blueprint.prototype.updateTime     = false;
    blueprint.prototype.updateCount    = 0
    blueprint.prototype.viewedRecently = false;
    blueprint.prototype.viewedLast     = false;

    blueprint.prototype.events = {
        toggle : function(event) {
            event.data.widget.scanning = $(this).prop('checked');

            if (event.data.widget.scanning) {
                event.data.$widget.trigger('scanstart');
            }
            else {
                event.data.$widget.trigger('scanstop');
            }
        },

        threadHoverCheck : function(event) {
            // mixItUp tacks on this bullshit CSS3 stuff I can't get rid of
            // any other way. It fucks up how my catalog works.
            $(event.currentTarget).parents(".thread-item:first").css({
                'transition' : "",
                'transform' : ""
            });

            var widget = event.data.widget;
            var $widget = event.data.$widget;
            var $catalog = event.data.$catalog;

            if (!widget.scanning) {
                return true;
            }

            setTimeout(function() {
                widget.paused = $(widget.options.selector['post-container']+":hover", $catalog).length > 0;
                $(widget.options.selector['label-reading'], $widget)
                    .css('visibility', widget.paused ? 'visible' : 'hidden');
            }, 100);
        },

        scanStop : function(event) {
            var widget = event.data.widget;
            var $widget = event.data.$widget;

            widget.scanning = false;

            var $logo = $(widget.options.selector['logo'])
            $logo.attr('src', $logo.data('original'));
        },

        // Overscan start
        scanStart : function(event) {
            var widget = event.data.widget;
            var $widget = event.data.$widget;

            widget.scanning = true;
            widget.timer = widget.options.refresh;

            var $logo = $(widget.options.selector['logo'])
            //$logo.data('original', $logo.attr('src'));
            //$logo.attr('src', window.app.media_url+'static/img/logo_overscan.gif');
        },

        updateAlways : function(json, textStatus, jqXHR) {
            jqXHR.widget.updating = false;
        },

        updateDone : function(data, textStatus, jqXHR) {
            var widget = jqXHR.widget;
            var $catalog = widget.$catalog;
            var replacements = 0;
            var $alreadySeen = $();

            data = data.reverse();

            if (widget.viewedRecently && !widget.hasFocus) {
                $alreadySeen = $(widget.options.selector['thread-item'], $catalog);
            }

            $.each(data, function(index, item) {
                var hidePosts = localStorage.getItem("hidePosts."+item.board_uri);
                if (hidePosts !== null && hidePosts.split(",").indexOf(item.post_id.toString()) > -1) {
                    return true; // continue if we're hidden
                }

                var $thread = $(item.html);
                var $existing = $catalog.children("[data-id="+$thread.data('post_id')+"]");

                // DOM prep
                var $li = $("<li class=\"thread-item mix\"></li>");
                var $article = $("<article class=\"thread\"></article>");

                $li.attr({
                    'data-id' : item.post_id,
                    'data-bumped' : item.bumped_last
                }).data({
                    'id' : item.post_id,
                    'bumped' : item.bumped_last
                }).addClass('board-'+item.board_uri);

                $article.append(item.html);
                $li.append($article);

                // Insertion
                if ($existing.length) {
                    $existing.remove();
                }

                ++replacements;

                // Add the item
                $catalog.mixItUp('insert', 0, $li[0]);
                ib.bindAll($li[0]);
                $li.css({
                    'display' : 'inline-block'
                });

                // Track the last time the user saw a post.
                widget.updateLast = item.bumped_last > widget.updateLast ? item.bumped_last : widget.updateLast;

                if (!widget.hasFocus) {
                    ++widget.updateCount;
                }
                else {
                    widget.updateLastSeen = widget.updateLast;
                }
            });

            if (replacements > 0) {
                $catalog.mixItUp('sort', 'bumped:desc');

                if ($alreadySeen.length > 0) {
                    $alreadySeen.addClass('already-seen');
                    widget.viewedRecently = false;
                }
            }

            if (!widget.hasFocus && widget.updateCount > 0)
            {
                $("#favicon").attr('href', window.app.favicon.alert);
                document.title = "(" + widget.updateCount + ") " + $('<div/>').html(window.app.title).text();
            }
            else
            {
                $("#favicon").attr('href', window.app.favicon.normal);
                document.title = $('<div/>').html(window.app.title).text();
            }
        },

        windowFocus : function(event) {
            var widget = event.data.widget;
            var $widget = event.data.$widget;

            widget.hasFocus = true;
            widget.updateCount = 0;
            widget.viewedRecently = true;

            document.title = $('<div/>').html(window.app.title).text();
            $("#favicon").attr('href', window.app.favicon.normal);
        },

        windowUnfocus : function(event) {
            var widget = event.data.widget;
            var $catalog = widget.$catalog;

            widget.hasFocus = false;
        }
    };

    blueprint.prototype.updateInterval = function() {
        var widget  = this;
        var $widget = this.$widget;
        clearInterval(widget.updateTimer);

        if (widget.scanning && !widget.paused  && !widget.updating)
        {
            var time = parseInt(widget.updateTime, 10);

            if (isNaN(time))
            {
                time = widget.options.refresh;
            }

            time -= widget.options.interval;

            if (time <= 0)
            {
                widget.updateQuery.apply(widget);
                time = widget.options.refresh;
            }

            widget.updateTime = time;
        }

        widget.updateTimer = setInterval(function() {
            widget.updateInterval.apply(widget);
        }, 1000);
    };

    blueprint.prototype.updateQuery = function() {
        var widget = this;
        var $widget = this.$widget;
        var $catalog = this.$catalog;

        var data = {
            updatedSince : $(widget.options.selector['thread-item']+":first", $catalog)
        };

        var jqXHR = $.ajax(window.location.pathname+".json", {
            data : {
                'updatedSince' : widget.updateLast,
                'messenger'    : 1
            }
        });

        jqXHR.widget = widget;
        jqXHR.done(widget.events.updateDone)
        jqXHR.always(widget.events.updateAlways);

        widget.updating = true;
    };

    blueprint.prototype.bind = function() {
        var widget = this;
        var $catalog = widget.$catalog = $(widget.options.selector['thread-list']);
        var data = {
            widget   : widget,
            $widget  : widget.$widget,
            $catalog : widget.$catalog
        };

        widget.$widget
            .on('change.ib-overscan', widget.options.selector['toggle'], data, widget.events.toggle)
            .on('scanstart.ib-overscan', data, widget.events.scanStart)
            .on('scanstop.ib-overscan', data, widget.events.scanStop)
        ;

        $(document)
            .on('mouseenter.ib-overwatch', widget.options.selector['post-container'], data, widget.events.threadHoverCheck)
            .on('mouseleave.ib-overwatch', widget.options.selector['post-container'], data, widget.events.threadHoverCheck)
        ;

        $(window)
            .on('focus.ib-overwatch', data, widget.events.windowFocus)
            .on('blur.ib-overwatch', data, widget.events.windowUnfocus)
        ;

        document.addEventListener('DOMContentLoaded', function() {
            // hide collapsed threads
            $catalog.children().children(".post-collapsed").parent().remove();
            $catalog.mixItUp(widget.mixItUp);

            widget.hasFocus = document.hasFocus();
            widget.updateLast = $(widget.options.selector['thread-item']).data('bumped');
            widget.viewedLast = widget.updateLast;

            widget.updateTimer = setInterval(function() {
                widget.updateInterval.apply(widget);
            }, 100);

            widget.$widget.trigger('scanstart');
        }, false);

        return true;
    };

    ib.widget("overwatch", blueprint, options);
})(window, window.jQuery);
