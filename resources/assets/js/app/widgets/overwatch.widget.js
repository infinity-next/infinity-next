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
            'thread-item' : '.thread-item',
            'post-container' : '.post-container',
        }
    };

    /* https://mixitup.kunkalabs.com/docs */
    blueprint.prototype.mixItUp = {
        animation: {
            enabled: true,
            duration: 100,
            effects: 'fade stagger(18ms)',
            easing: 'ease'
        },
        load: {
            sort: 'bumped:desc'
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
    blueprint.prototype.updateCount    = 0;
    blueprint.prototype.viewedLast     = false;

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
            .on('ready.ib-overwatch', data, widget.events.documentReady)
            .on('mouseenter.ib-overwatch', widget.options.selector['post-container'], data, widget.events.threadHoverCheck)
            .on('mouseleave.ib-overwatch', widget.options.selector['post-container'], data, widget.events.threadHoverCheck)
        ;

        $(window)
            .on('focus.ib-overwatch', data, widget.events.windowFocus)
            .on('blur.ib-overwatch', data, widget.events.windowUnfocus)
        ;

        return true;
    };

    blueprint.prototype.events = {
        documentReady : function(event) {
            var widget = event.data.widget;

            widget.$catalog.mixItUp(widget.mixItUp);

            widget.hasFocus = document.hasFocus();
            widget.updateLast = $(widget.options.selector['thread-item']).data('bumped');
            widget.viewedLast = widget.updateLast;

            widget.updateTimer = setInterval(function() {
                widget.updateInterval.apply(widget);
            }, 100);
        },

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
            $logo.data('original', $logo.attr('src'));
            $logo.attr('src', window.app.media_url+'static/img/logo_overscan.gif');
        },

        updateAlways : function(json, textStatus, jqXHR) {
            jqXHR.widget.updating = false;
        },

        updateDone : function(data, textStatus, jqXHR) {
            var widget = jqXHR.widget;
            var $catalog = widget.$catalog;
            var replacements = 0;
            data = data.reverse();

            $.each(data, function(index, item) {
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
                    ++replacements;
                    $existing.remove();
                }

                // Add the item
                $catalog.mixItUp('prepend', $li);
                ib.bindAll($li[0]);

                // Track the last time the user saw a post.
                widget.updateLast = item.bumped_last > widget.updateLast ? item.bumped_last : widget.updateLast;

                if (!widget.hasFocus) {
                    ++widget.updateCount;
                }
                else {
                    widget.updateLastSeen = widget.updateLast;
                }
            });

            if (replacements) {
                $catalog.mixItUp('sort', 'bumped:desc');
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

            widget.hasFocus    = true;
            widget.updateCount = 0;

            document.title = $('<div/>').html(window.app.title).text();
            $("#favicon").attr('href', window.app.favicon.normal);
        },

        windowUnfocus : function(event) {
            var widget = event.data.widget;

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
            updatedSince : $(widget.options.selector['thread-item'], $catalog)
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

    ib.widget("overwatch", blueprint, options);
})(window, window.jQuery);
