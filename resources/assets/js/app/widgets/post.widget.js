// ===========================================================================
// Purpose          : Posts
// Contributors     : jaw-sh
// Widget Version   : 2
// ===========================================================================

(function(window, $, undefined) {
    // Widget blueprint
    var blueprint = ib.getBlueprint();

    var events = {
        doContentUpdate : function(event) {
            // On setting update, trigger reformating..
            var setting = event.data.setting;
            var widget  = setting.widget;
            ib.getInstances(widget).trigger('contentUpdate');
        }
    };

    // Configuration options
    var options = {
        author_id : {
            type : "bool",
            initial : true,
            onChange : events.doContentUpdate,
            onUpdate : events.doContentUpdate
        }
    };

    blueprint.prototype.defaults = {
        preview_delay : 100,

        // Important class names.
        classname : {
            'post-collapsed' : "post-collapsed",
            'post-hover'     : "post-hover",
            'cite-you'       : "cite-you",
            'cite-op'        : "cite-op",
            'tabs-open'      : "open",
            'op'             : "op-container"
        },

        // Selectors for finding and binding elements.
        selector : {
            'widget'         : ".post-container",

            'mode-reply'     : "main.mode-reply",
            'mode-index'     : "main.mode-index",

            'post-reply'     : ".post-reply",

            'element-code'   : "pre code",
            'element-quote'  : "blockquote",

            'action-tab'     : ".actions-anchor", // the dropdown button
            'action-tabs'    : ".actions", // the dropdown menu

            'details'        : ".post-details",
            'watch'          : ".post-watch",
            'unwatch'        : ".post-unwatch",
            'author'         : ".author",
            'author_id'      : ".authorid",

            'cite-slot'      : ".detail-cites",
            'cite'           : "a.cite-post",
            'backlinks'      : "a.cite-backlink",
            'forwardlink'    : "blockquote.post a.cite-post",

            'post-form'      : "#post-form",
            'post-form-body' : "#body"
        },

        // HTML Templates
        template : {
            'backlink' : "<a class=\"cite cite-post cite-backlink\"></a>",
            'collapse' : "<span class=\"post-collapse\"><i class=\"fas fa-minus-square\"></i>&nbsp;</span>",
            'uncollapse' : "<span class=\"post-uncollapse\"><i class=\"fas fa-plus-square\"></i>&nbsp;</span>",
            'watch' : "<span class=\"post-watch\"><i class=\"fas fa-heart\"></i></span>"
        }
    };

    // The temporary hover-over item created to show backlink posts.
    blueprint.prototype.$cite    = null;
    blueprint.prototype.citeLoad = null;

    // Takes an element and positions it near a backlink.
    blueprint.prototype.anchorBoxToLink = function($box, $link) {
        var bodyWidth = document.body.scrollWidth;

        var linkRect  = $link[0].getBoundingClientRect();

        $(this.options.classname['post-hover']).remove();

        if (!$box.parents().length) {
            $box.appendTo("body")
                .addClass(this.options.classname['post-hover'])
                .css('position', "absolute");
            ib.bindAll($box);
        }

        var boxHeight = $box.outerHeight();
        var boxWidth  = $box.outerWidth();

        var posTop  = linkRect.top + window.scrollY;

        // Is the box's bottom below the bottom of the screen?
        if (posTop + boxHeight + 25 > window.scrollY + window.innerHeight) {
            // Selects the larger of two values:
            // A) Our position in the scroll, or
            // B) The hidden part of the post subtracted from the top.
            // This check will try to keep the entire post visible,
            // but will always keep the top of the post visible.
            var posTopDiff = (posTop + boxHeight + 25) - (window.scrollY + window.innerHeight);
            posTop = Math.max( window.scrollY, posTop - posTopDiff );
        }


        // Float to the right.
        // Default for LTR
        var posLeft;
        var posLeftOnRight = linkRect.right + 5;
        var posLeftOnLeft  = linkRect.left  - 5;
        var maxWidth       = (document.body.scrollWidth * 0.7) - 15;
        var newWidth;

        // LTR display
        if (ib.ltr) {
            // Left side has more space than right side,
            // and box is wider than remaining space.
            if (linkRect.left > document.body.scrollWidth - posLeftOnRight
                && boxWidth > document.body.scrollWidth - posLeftOnRight) {
                posLeft  = posLeftOnLeft;
                newWidth = Math.min(maxWidth, boxWidth, linkRect.left - 15);
                posLeft -= newWidth;
            }
            // Right side has more adequate room,
            // Or box fits in.
            else {
                posLeft  = posLeftOnRight;
                newWidth = Math.min(
                    maxWidth,
                    boxWidth,
                    document.body.scrollWidth - posLeftOnRight  - 15
                );
            }
        }
        // RTL Display
        else {
            // TODO
        }

        $box.css({
            'top'       : posTop,
            'left'      : posLeft,
            'width'     : newWidth,
        });

        this.$cite = $box;
    };

    // Includes (You) classes on posts that we think we own.
    blueprint.prototype.addAuthorship = function() {
        var widget = this;
        var $widget = widget.$widget;
        var cites  = [];
        var op_board_id = $widget.data('reply-to-board-id').toString();
        var post_board_uri = $widget.data('board_uri');

        // Loop through each citation.
        $(widget.options.selector['cite'], $widget).each(function() {
            var board    = this.dataset.board_uri;
            var post     = this.dataset.board_id.toString();

            // Check and see if we have an item for this citation's board.
            if (typeof cites[board] === "undefined") {
                if (localStorage.getItem("yourPosts."+board) !== null) {
                    cites[board] = localStorage.getItem("yourPosts."+board).split(",");
                }
                else {
                    cites[board] = [];
                }
            }

            if (cites[board].length > 0 && cites[board].indexOf(post) >= 0) {
                this.className += " " + widget.options.classname['cite-you'];
            }

            if (board === post_board_uri && post === op_board_id) {
                this.className += " " + widget.options.classname['cite-op'];
            }
        });

        (function() {
            var board = widget.$widget[0].dataset.board_uri;
            var post  = widget.$widget[0].dataset.board_id.toString();
            var posts = localStorage.getItem("yourPosts."+board);

            if (posts !== null && posts.split(",").indexOf(post) > 0) {
                widget.$widget[0].className += " post-you";
            }
        })();
    };

    // Event bindings
    blueprint.prototype.bind = function() {
        var widget  = this;
        var $widget = this.$widget;
        var data    = {
            widget  : widget,
            $widget : $widget
        };

        $(window)
            .on('au-updated.ib-post', data, widget.events.threadNewPosts)
            .on('click.ib-post', data, widget.events.windowClick);

        $widget
            //custom events
            .on('contentUpdate.ib-post', data, widget.events.postContentUpdate)
            .on('highlight-syntax.ib-post', data, widget.events.codeHighlight)
            .on('collapse-post', data, widget.events.postCollapse)
            .on('uncollapse-post', data, widget.events.postUncollapse)
            .on('hide-post', data, widget.events.postHide)
            .on('unhide-post', data, widget.events.postUnhide)

            // Post interactions
            .on('click.ib-post', widget.options.selector['post-reply'], data, widget.events.replyClick)
            .on('click.ib-post', widget.options.selector['action-tab'], data, widget.events.actionTabClick)

            // Citations
            .on('click.ib-post', widget.options.selector['cite'], data, widget.events.citeClick)
            .on('mouseover.ib-post', widget.options.selector['cite'], data, widget.events.citeMouseOver)
            .on('mouseout.ib-post', widget.options.selector['cite'], data,widget.events.citeMouseOut)

            // Macros that ought to yield to other events
            .on('click.ib-post', data, widget.events.postClick)
        ;

        $widget.trigger('contentUpdate');
        widget.cachePosts($widget);

        // auto-hide
        var postId = $widget[0].dataset.post_id;
        var board = $widget[0].dataset.board_uri;
        var hidden = false;

        if (localStorage.getItem("hidePosts."+board) !== null && localStorage.getItem("hidePosts."+board).split(",").indexOf(postId) > -1) {
            $widget.trigger('collapse-post');
            var hidden = true;
        }

        // bind toggling
        var wingding = $widget[0].previousSibling;
        if (wingding !== null && wingding.className == 'wingding') {
            $(wingding).on('click', data, widget.events.toggleCollapse);

            if (hidden) {
                wingding.innerHTML = widget.options.template['uncollapse'];
            }
            else {
                wingding.innerHTML = widget.options.template['collapse'];
            }
        }

        widget.isOp = false;
        if ($widget.hasClass(this.options.classname['op'])) {
            widget.isOp = true;

            var $details = $(".post-details", $widget);
            var $heart = $details.prepend(this.options.template['watch']);

            $widget
                .on('click.ib-post', widget.options.selector['watch'], data, widget.events.postWatch)
                .on('click.ib-post', widget.options.selector['unwatch'], data, widget.events.postUnwatch)
            ;

            $(window).on('storage.ib-thread-watcher', data, widget.events.storage);

            widget.updateHeart();
        }
    };

    // Stores a post in the session store.
    blueprint.prototype.cachePosts = function(jsonOrjQuery) {
        // This stores the post data into a session storage for fast loading.
        if (typeof sessionStorage === "object") {
            var $post;

            if (jsonOrjQuery instanceof jQuery) {
                $post = jsonOrjQuery;
            }
            else if (jsonOrjQuery.html) {
                var $post = $(jsonOrjQuery.html);
            }

            // We do this even with an item we pulled from AJAX to remove the ID.
            // The HTML dom cannot have duplicate IDs, ever. It's important.
            var $post = $($post[0].outerHTML); // Can't use .clone()

            if (typeof $post[0] !== "undefined") {
                var id    = $post[0].id;
                $post.removeAttr('id');
                var html = $post[0].outerHTML;

                // Attempt to set a new storage item.
                // Destroy older items if we are full.
                var setting = true;

                while (setting === true) {
                    try {
                        sessionStorage.setItem( id, html );
                        break;
                    }
                    catch (e) {
                        if (sessionStorage.length > 0) {
                            sessionStorage.removeItem( sessionStorage.key(0) );
                        }
                        else {
                            setting = false;
                        }
                    }
                }

                return $post;
            }
        }

        return null;
    };

    // Clears existing hover-over divs created by anchorBoxToLink.
    blueprint.prototype.clearCites = function() {
        if (this.$cite instanceof jQuery) {
            this.$cite.remove();
        }

        $("."+this.options.classname['post-hover']).remove();

        this.$cite    = null;
        this.citeLoad = null;
    };

    blueprint.prototype.updateHeart = function() {
        if (!this.isOp) {
            return;
        }

        var storage = ib.getThreadsWatched();
        var $details = $(".post-watch, .post-unwatch", this.$widget);
        var isWatched = this.$widget.data('post_id') in storage;

        $details.toggleClass('post-watch', !isWatched).toggleClass('post-unwatch', isWatched);
    };

    // Events
    blueprint.prototype.events = {
        actionTabClick : function(event) {
            var widget  = event.data.widget;
            var $this   = $(this);
            var $target = $(event.target);

            // Make sure we're not actually in the menu.
            if ($target.parents(widget.options.selector['action-tabs']).length) {
                return true;
            }

            // Toggle all tabs off.
            $(widget.options.selector['action-tab'] + "." + widget.options.classname['tabs-open'])
                .not($this)
                .toggleClass(widget.options.classname['tabs-open']);

            $(this).toggleClass(widget.options.classname['tabs-open']);

            event.preventDefault();
            return false;
        },

        citeClick : function(event) {
            if (event.altKey || event.ctrlKey) {
                return true;
            }

            var $cite     = $(this);
            var board_uri = $cite.attr('data-board_uri');
            var board_id  = parseInt($cite.attr('data-board_id'), 10);
            var $target   = $("#post-"+board_uri+"-"+board_id);

            if ($target.length) {
                window.location.hash = board_id;
                $target[0].scrollIntoView();

                event.preventDefault();
                return false;
            }
        },

        citeMouseOver : function(event) {
            var widget    = event.data.widget;
            var $cite     = $(this);
            var board_uri = $cite.attr('data-board_uri');
            var board_id  = parseInt($cite.attr('data-board_id'), 10);
            var post_id   = "post-"+board_uri+"-"+board_id;
            var $post;

            // Prevent InstantClick hijacking requests we can handle without
            // reloading the document.
            if ($("#"+post_id).length) {
                $cite.attr('data-no-instant', "data-no-instant");
            }
            else {
                $cite.removeAttr('data-no-instant');
            }

            widget.clearCites();

            if (widget.citeLoad == post_id) {
                return true;
            }

            // Loads session storage for our post if it exists.
            if (typeof sessionStorage === "object") {
                $post = $(sessionStorage.getItem( post_id ));

                if ($post instanceof jQuery && $post.length) {
                    widget.anchorBoxToLink($post, $cite);
                    return true;
                }
            }

            widget.citeLoad = post_id;

            jQuery.ajax({
                type:        "GET",
                url:         "/"+board_uri+"/post/"+board_id+".json",
                contentType: "application/json; charset=utf-8"
            }).done(function(response, textStatus, jqXHR) {
                $post = widget.cachePosts(response);

                if (widget.citeLoad === post_id) {
                    widget.anchorBoxToLink($post, $cite);
                }
            });
        },

        citeMouseOut : function(event) {
            event.data.widget.clearCites();
        },

        // Adds HLJS syntax formatting to posts.
        codeHighlight : function(event) {
            var widget  = event.data.widget;
            var $widget =  event.data.$widget;

            // Activate code highlighting if the JS module is enabled.
            if (typeof window.hljs === "object") {
                $(widget.options.selector['element-code'], $widget).each(function() {
                    window.hljs.highlightBlock(this);
                });
            }
            else {
                console.log("post.codeHighlight: missing hljs");
            }
        },

        postCollapse : function (event) {
            var widget  = event.data.widget;
            var $widget =  event.data.$widget;

            $widget.addClass(widget.options.classname['post-collapsed']);

            // delete from catalog
            var $parent = $widget.parent();
            if ($parent.hasClass("catalog-thread")) {
                $parent.hide();
            }
        },

        postClick : function (event) {
            var widget  = event.data.widget;
            var $widget =  event.data.$widget;

            // if we're making a meaningful click.
            switch (event.target.tagName) {
                case "A" :
                case "IMG" :
                    return true;
            }

            if (event.shiftKey) {
                $widget.trigger('hide-post');
            }

            event.preventDefault();
            return false;
         },

        postContentUpdate : function (event) {
            var widget  = event.data.widget;
            var $widget =  event.data.$widget;

            $(widget.options.selector.author_id, $widget)
                .toggle(widget.is('author_id'));

            widget.addAuthorship();
            $widget.trigger('highlight-syntax');
        },

        postHide : function (event) {
            var widget = event.data.widget;
            var $widget = event.data.$widget;

            var postId = event.data.$widget[0].dataset.post_id;
            var board = event.data.$widget[0].dataset.board_uri;
            var hidePosts = [];

            if (localStorage.getItem("hidePosts."+board) !== null) {
                hidePosts[board] = localStorage.getItem("hidePosts."+board).split(",");
            }
            else {
                hidePosts[board] = [];
            }

            if (hidePosts[board].indexOf(postId) === -1) {
                hidePosts[board].push(postId);
                hidePosts[board] = hidePosts[board].filter(e => !!e);
                localStorage.setItem("hidePosts."+board, hidePosts[board].join(','));
            }

            $widget.trigger('collapse-post');
        },

        postUncollapse : function (event) {
            var widget  = event.data.widget;
            var $widget =  event.data.$widget;

            $widget.removeClass(widget.options.classname['post-collapsed']);
        },

        postUnhide : function (event) {
            var widget = event.data.widget;
            var $widget = event.data.$widget;

            var postId = event.data.$widget[0].dataset.post_id;
            var board = event.data.$widget[0].dataset.board_uri;
            var hidePosts = [];

            if (localStorage.getItem("hidePosts."+board) !== null) {
                hidePosts[board] = localStorage.getItem("hidePosts."+board).split(",")
                    .filter(i => i !== postId);
                localStorage.setItem("hidePosts."+board, hidePosts[board].join(','));
            }

            $widget.trigger('uncollapse-post');
        },

        postUnwatch : function (event) {
            // pass the global post_id as base10 to handler
            ib.threadUnwatch(event.data.$widget.data('post_id'));
            event.data.widget.updateHeart();
        },

        postWatch : function (event) {
            var $widget = event.data.$widget;
            var id = $widget.data('post_id');
            var data = {
                post_id: id,
                board_id: $widget.data('board_id'),
                board_uri: $widget.data('board_uri'),
                bumped_last: $widget.data('bumped-last'),
                unseen: 0
            };

            // make sure bump time is accurate
            var lastReply = $(".post-container", $widget.parent()).last()[0].dataset.createdAt || 0;
            data.bumped_last = Math.max(data.bumped_last, lastReply);

            // create excerpt
            var subject = $(".subject", $widget).text().trim();
            var body = $(".post", $widget).text().trim();
            var excerpt = null;

            if (subject.length && body.length) {
                excerpt = (subject + " - " + body).substr(0, 256).trim();
            }
            else if (subject.length) {
                excerpt = subject.substr(0, 256).trim();
            }
            else if (body.length) {
                excerpt = body.substr(0, 256).trim();
            }
            else {
                var attachmentCnt = $(".attachment", $widget).length;
                excerpt = window.app.lang['thread-watcher']['post_with_x_attachments'].replace(":attachments", attachmentCnt);
            }

            data.excerpt = excerpt;

            ib.threadWatch(id, data);
            event.data.widget.updateHeart();
        },

        replyClick : function(event) {
            var widget = event.data.widget;

            if ($(widget.options.selector['mode-reply']).length !== 0) {
                event.preventDefault();

                var $this = $(this);
                var $body = $(widget.options.selector['post-form-body']);
                var $postbox = $(widget.options.selector['post-form']);
                var postboxWidget = $postbox[0].widget;
                var selectedText = ib.getSelectedText();

                if (selectedText != "") {
                    selectedText = ">" + selectedText.trim().split("\n").join("\n>") + "\n";
                }

                // Focusing the textarea automatically scrolls the window.
                // Correct that.
                var x = window.scrollX;
                var y = window.scrollY;

                postboxWidget.replaceBodySelection(">>" + $this.data('board_id') + "\n" + selectedText);
                postboxWidget.responsiveAnchor(widget.$widget[0]);

                window.scrollTo(x, y);
                $postbox.trigger('open-form');

                return false;
            }

            return true;
        },

        // This is an HTML localStorage event.
        // it only fires if ANOTHER WINDOW trips the change.
        storage : function(event) {
            if (event.data.widget.isOp && event.originalEvent.key == "watchThreads") {
                event.data.widget.updateHeart();
            }
        },

        toggleCollapse : function (event) {
            var widget = event.data.widget;
            var $widget = event.data.$widget;
            var $wingding = $(this);

            if ($widget.hasClass(widget.options.classname['post-collapsed'])) {
                $wingding.html(widget.options.template['collapse']);
                console.log('unhiding');
                $widget.trigger('unhide-post');
            }
            else {
                $wingding.html(widget.options.template['uncollapse']);
                $widget.trigger('hide-post');
            }

            event.preventDefault();
            return false;
        },

        threadNewPosts : function(event, posts) {
            // Data of our widget, the item we are hoping to insert new citations into.
            var widget           = event.data.widget;
            var $detail          = $(widget.options.selector['cite-slot'], widget.$widget);
            var $backlinks       = $detail.children();
            var backlinks        = 0;
            var widget_board_uri = widget.$widget.attr('data-board_uri');
            var widget_board_id  = widget.$widget.attr('data-board_id');

            // All new updates show off their posts in three difeferent groups.
            jQuery.each(posts, function(index, group) {
                // Each group can have many jQuery dom elements.
                jQuery.each(group, function(index, $post) {
                    // This information belongs to a new post.
                    var post_board_uri = $post.attr('data-board_uri');
                    var post_board_id  = $post.attr('data-board_id');
                    var $cites         = $(widget.options.selector['forwardlink'], $post);

                    // Each post may have many citations.
                    $cites.each(function(index) {
                        // This information represents the post we are citing.
                        var $cite          = $(this);
                        var cite_board_uri = $cite.attr('data-board_uri');
                        var cite_board_id  = $cite.attr('data-board_id');

                        // If it doesn't belong to our widget, we don't want it.
                        if (cite_board_uri == widget_board_uri && cite_board_id == widget_board_id)
                        {
                            var $target    = $("#post-" + cite_board_uri + "-" + post_board_id);

                            if (!$backlinks.filter("[data-board_uri="+post_board_uri+"][data-board_id="+post_board_id+"]").length)
                            {
                                var $backlink = $(widget.options.template['backlink'])
                                    .attr('data-board_uri', post_board_uri)
                                    .data('board_uri', post_board_uri)
                                    .attr('data-board_id', post_board_id)
                                    .data('board_id', post_board_id)
                                    .attr('href', "/" + post_board_uri + "/post/" + post_board_id)
                                    .appendTo($detail);

                                $backlinks = $backlinks.add($backlink);
                                ++backlinks;

                                // Believe it or not this is actually important.
                                // it adds a space after each item.
                                $detail.append("\n");

                                if (post_board_uri == window.app.board)
                                {
                                    $backlink.addClass('cite-local').html("&gt;&gt;" + post_board_id);
                                }
                                else
                                {
                                    $backlink.addClass('cite-remote').html("&gt;&gt;&gt;/" + post_board_uri + "/" + post_board_id);
                                }
                            }
                        }
                    });
                });
            });

            if (backlinks)
            {
                widget.addAuthorship();
            }
        },

        windowClick : function(event) {
            var $widget = event.data.$widget;
        }
    };

    ib.widget("post", blueprint, options);
})(window, window.jQuery);
