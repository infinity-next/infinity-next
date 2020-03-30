// ============================================================
// Purpose                      : Global navigation
// Contributors                 : jaw-sh
// ============================================================

ib.widget("gnav", function(window, $, undefined) {
    var widget = {

        defaults : {
            storage   : {
                // Mirrors board-favorite widget's.
                'favorites-data' : "ib.favoritedata"
            },

            selector : {
                'class-open'  : "flyout-open",

                'nav-link'    : ".gnav-link[data-item]",

                'flyout'      : ".flyout",
                'flyout-list' : ".flyout-list",
                'flyout-link' : ".flyout-link",

                'favorites'   : "#favorite-boards"
            },

            templates : {
                'flyout-item'  : "<li class=\"flyout-item\"></li>",
                'flyout-link'  : "<a href=\"\" class=\"flyout-link\"></a>",
                'flyout-uri'   : "<span class=\"flyout-uri\"></span>",
                'flyout-title' : "<span class=\"flyout-title\"></span>"
            }
        },

        // Events
        events   : {
            anyClick  : function(event) {
                // Close ally open flyouts.
                var $flyouts = $("."+widget.options.selector['class-open']);

                $flyouts.each(function() {
                    if (!$(event.target).closest(this).length)
                    {
                        $(this).removeClass(widget.options.selector['class-open']);
                    }
                });
            },

            itemClick : function(event) {
                event.stopPropagation();

                var $link    = $(this);
                var item     = $link.attr('data-item');
                var $flyout  = $("#flyout-"+item);

                if ($flyout.length)
                {
                    $flyout.toggleClass(widget.options.selector['class-open']);
                    event.preventDefault();
                    return false;
                }
            },

            favoritesBuild : function(event) {
                widget.build.favorites();
            },

            flyoutClick : function(event) {
                $(this).parents("."+widget.options.selector['class-open'])
                    .removeClass(widget.options.selector['class-open']);
            },

            // This is an HTML localStorage event.
            // it only fires if ANOTHER WINDOW trips the change.
            storage : function(event) {
                if (event.originalEvent.key == widget.options.storage['favorites-data'])
                {
                    widget.build.favorites();
                }
            }
        },

        // Event bindings
        bind     : {
            widget : function() {
                $(window)
                    .on( 'click.ib-gnav',   widget.events.anyClick )
                    .on( 'storage.ib-gnav', widget.events.storage )
                ;

                widget.$widget
                    .on( 'click.ib-gnav', widget.options.selector['flyout-link'], widget.events.flyoutClick )
                    .on( 'click.ib-gnav', widget.options.selector['nav-link'],    widget.events.itemClick )
                    .on( 'build.ib-gnav', widget.options.selector['favorites'],   widget.events.favoritesBuild )
                ;

                $(widget.options.selector['nav-link'], widget.$widget).each(function() {
                    var $link = $(this);

                    if ($("#flyout-" + $link.attr('data-item')).length > 0)
                    {
                        $link.attr('data-no-instant', "true");
                    }
                });

                widget.build.favorites();
            }
        },

        build    : {
            favorites : function() {
                if (typeof localStorage === "object")
                {
                    var $favorites = $(widget.options.selector['favorites'], widget.$widget);
                    var $list      = $(widget.options.selector['flyout-list'], $favorites);
                    var favorites  = localStorage.getItem(widget.options.storage['favorites-data']);

                    if (typeof favorites === "string")
                    {
                        favorites = JSON.parse(favorites);

                        $favorites.css('display', favorites.length > 0 ? "block" : "none");
                        $list.children().remove();

                        if (favorites.length)
                        {
                            for (var i = 0; i < favorites.length; ++i)
                            {
                                var favorite = favorites[i];

                                var $item  = $(widget.options.templates['flyout-item']);
                                var $link  = $(widget.options.templates['flyout-link']);
                                var $uri   = $(widget.options.templates['flyout-uri']);
                                var $title = $(widget.options.templates['flyout-title']);

                                $item
                                    .appendTo($list);

                                $link
                                    .attr('href', window.app.url+"/"+favorite.board_uri+"/")
                                    .appendTo($item);

                                $uri
                                    .text("/"+favorite.board_uri+"/")
                                    .appendTo($link);

                                $title
                                    .text(favorite.title)
                                    .appendTo($link);
                            }
                        }
                    }
                }
            }
        },
    };

    return widget;
});
