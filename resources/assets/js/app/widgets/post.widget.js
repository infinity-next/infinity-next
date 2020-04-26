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
            'tabs-open'      : "open"
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
            'backlink' : "<a class=\"cite cite-post cite-backlink\"></a>"
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
            .on('hide-post', data, widget.events.postHide)

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
        },

        postClick : function (event) {
            var widget  = event.data.widget;
            var $widget =  event.data.$widget;

            if (event.target.tagName == 'a') {
                return; // if we're making a meaningful click.
            }

            if (event.shiftKey) {
                $widget.trigger('hide-post');
            }
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

            if (hidePosts[board].indexOf(postId) !== -1) {
                hidePosts[board].push(postId);
                localStorage.setItem("hidePosts."+board, hidePosts.join(','));
            }

            $widget.trigger('collapse-post');
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
