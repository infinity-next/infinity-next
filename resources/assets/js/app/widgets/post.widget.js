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
        },
        attachment_preview : {
            type : "bool",
            initial : true
        }
    };

    blueprint.prototype.defaults = {
        preview_delay : 300,

        // Important class names.
        classname : {
            'post-hover'     : "post-hover",
            'cite-you'       : "cite-you",
            'cite-op'        : "cite-op",
            'tabs-open'      : "open",
            'image-expanded' : "attachment-expanded"
        },

        // Selectors for finding and binding elements.
        selector : {
            'widget'         : ".post-container",

            'hover-box'      : "#attachment-preview",
            'hover-box-img'  : "#attachment-preview-img",

            'mode-reply'     : "main.mode-reply",
            'mode-index'     : "main.mode-index",

            'post-reply'     : ".post-reply",

            'element-code'   : "pre code",
            'element-quote'  : "blockquote",

            'action-tab'     : "div.post-action-tab",
            'action-tabs'    : "ul.post-action-groups",

            'author'         : ".author",
            'author_id'      : ".authorid",

            'cite-slot'      : "li.detail-cites",
            'cite'           : "a.cite-post",
            'backlinks'      : "a.cite-backlink",
            'forwardlink'    : "blockquote.post a.cite-post",

            'post-form'      : "#post-form",
            'post-form-body' : "#body",

            'attachment'         : "li.post-attachment",
            'attacment-expand'   : "li.post-attachment:not(.attachment-expanded) a.attachment-link",
            'attacment-collapse' : "li.post-attachment.attachment-expanded a.attachment-link",
            'attachment-media'   : "audio.attachment-inline, video.attachment-inline",
            'attachment-image'   : "img.attachment-img",
            'attachment-image-download'   : "img.attachment-type-file",
            'attachment-image-expandable' : "img.attachment-type-img",
            'attachment-image-audio'      : "img.attachment-type-audio",
            'attachment-image-video'      : "img.attachment-type-video",
            'attachment-hoverable'        : "li.post-attachment:not(.attachment-expanded) img.attachment-type-img",
            'attachment-inline'  : "audio.attachment-inline, video.attachment-inline",
            'attachment-link'    : "a.attachment-link"
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

        if (!$box.parents().length)
        {
            $box.appendTo("body")
                .addClass(this.options.classname['post-hover'])
                .css('position', "absolute");
            ib.bindAll($box);
        }

        var boxHeight = $box.outerHeight();
        var boxWidth  = $box.outerWidth();

        var posTop  = linkRect.top + window.scrollY;

        // Is the box's bottom below the bottom of the screen?
        if (posTop + boxHeight + 25 > window.scrollY + window.innerHeight)
        {
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
        if (ib.ltr)
        {
            // Left side has more space than right side,
            // and box is wider than remaining space.
            if (linkRect.left > document.body.scrollWidth - posLeftOnRight
                && boxWidth > document.body.scrollWidth - posLeftOnRight)
            {
                posLeft  = posLeftOnLeft;
                newWidth = Math.min(maxWidth, boxWidth, linkRect.left - 15);
                posLeft -= newWidth;
            }
            // Right side has more adequate room,
            // Or box fits in.
            else
            {
                posLeft  = posLeftOnRight;
                newWidth = Math.min(
                    maxWidth,
                    boxWidth,
                    document.body.scrollWidth - posLeftOnRight  - 15
                );
            }
        }
        // RTL Display
        else
        {
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
            if (typeof cites[board] === "undefined")
            {
                if (localStorage.getItem("yourPosts."+board) !== null)
                {
                    cites[board] = localStorage.getItem("yourPosts."+board).split(",");
                }
                else
                {
                    cites[board] = [];
                }
            }

            if (cites[board].length > 0 && cites[board].indexOf(post) >= 0)
            {
                this.className += " " + widget.options.classname['cite-you'];
            }

            if (board === post_board_uri && post === op_board_id)
            {
                this.className += " " + widget.options.classname['cite-op'];
            }
        });

        (function() {
            var board = widget.$widget[0].dataset.board_uri;
            var post  = widget.$widget[0].dataset.board_id.toString();
            var posts = localStorage.getItem("yourPosts."+board);

            if (posts !== null && posts.split(",").indexOf(post) > 0)
            {
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
            .on(
                'au-updated.ib-post',
                data,
                widget.events.threadNewPosts
            )
            .on(
                'click.ib-post',
                data,
                widget.events.windowClick
            )
            .on(
                'resize.ib-post',
                data,
                widget.events.windowResize
            );

        $widget
            .on(
                'contentUpdate.ib-post',
                data,
                widget.events.postContentUpdate
            )
            .on(
                'highlight-syntax.ib-post',
                data,
                widget.events.codeHighlight
            )
            .on(
                'click.ib-post',
                widget.options.selector['post-reply'],
                data,
                widget.events.postClick
            )
            .on(
                'click.ib-post',
                widget.options.selector['action-tab'],
                data,
                widget.events.actionTabClick
            )
            .on(
                'mouseover.ib-post',
                widget.options.selector['attachment-hoverable'],
                data,
                widget.events.attachmentMediaMouseOver
            )
            .on(
                'mouseout.ib-post',
                widget.options.selector['attachment-hoverable'],
                data,
                widget.events.attachmentMediaMouseOut
            )
            .on(
                'media-check.ib-post',
                widget.options.selector['attachment-image'],
                data,
                widget.events.attachmentMediaCheck
            )
            .on(
                'click.ib-post',
                widget.options.selector['attacment-expand'],
                data,
                widget.events.attachmentExpandClick
            )
            .on(
                'click.ib-post',
                widget.options.selector['attacment-collapse'],
                data,
                widget.events.attachmentCollapseClick
            )

            // Citations
            .on(
                'click.ib-post',
                widget.options.selector['cite'],
                data,
                widget.events.citeClick
            )
            .on(
                'mouseover.ib-post',
                widget.options.selector['cite'],
                data,
                widget.events.citeMouseOver
            )
            .on(
                'mouseout.ib-post',
                widget.options.selector['cite'],
                data,
                widget.events.citeMouseOut
            )
        ;

        $widget.trigger('contentUpdate');
        widget.scaleThumbnails();
        widget.cachePosts($widget);
    };

    blueprint.prototype.bindMediaEvents = function($element) {
        var data = {
            widget  : this,
            $widget : this.$widget
        };

        $element.on(
            'ended.ib-post',
            data,
            this.events.attachmentMediaEnded
        );
    };

    // Stores a post in the session store.
    blueprint.prototype.cachePosts = function(jsonOrjQuery) {
        // This stores the post data into a session storage for fast loading.
        if (typeof sessionStorage === "object")
        {
            var $post;

            if (jsonOrjQuery instanceof jQuery)
            {
                $post = jsonOrjQuery;
            }
            else if (jsonOrjQuery.html)
            {
                var $post = $(jsonOrjQuery.html);
            }

            // We do this even with an item we pulled from AJAX to remove the ID.
            // The HTML dom cannot have duplicate IDs, ever. It's important.
            var $post = $($post[0].outerHTML); // Can't use .clone()

            if (typeof $post[0] !== "undefined")
            {
                var id    = $post[0].id;
                $post.removeAttr('id');
                var html = $post[0].outerHTML;

                // Attempt to set a new storage item.
                // Destroy older items if we are full.
                var setting = true;

                while (setting === true)
                {
                    try
                    {
                        sessionStorage.setItem( id, html );
                        break;
                    }
                    catch (e)
                    {
                        if (sessionStorage.length > 0)
                        {
                            sessionStorage.removeItem( sessionStorage.key(0) );
                        }
                        else
                        {
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
        if (this.$cite instanceof jQuery)
        {
            this.$cite.remove();
        }

        $("."+this.options.classname['post-hover']).remove();

        this.$cite    = null;
        this.citeLoad = null;
    };

    // Scales thumbnails down based on resolution.
    blueprint.prototype.scaleThumbnails = function() {
        if ($("body").is(".responsive")) {
            var $widget = this.$widget;
            var maxDimension = 200;
            var rangeMax = 1440;
            var rangeMin = 320;

            $(this.options.selector['attachment-image'], $widget).each(function()
            {
                var $img = $(this);
                var $box = $img.parent();
                var width, height;

                if (undefined === (width = $img.data('original-width'))) {
                    width = $img.width();
                    $img.data('original-width', width);
                }
                if (!$img.is(".thumbnail-spoiler")) {
                    if (undefined === (height = $img.data('original-height'))) {
                        height = $img.height();
                        $img.data('original-height', height);
                    }
                }
                else {
                    height = 0;
                }

                // We want a number between 320 and 1440, minus 320.
                // Used to get a value between 0 and 0.5.
                var factor = Math.min(Math.max(window.innerWidth, rangeMin), rangeMax) - rangeMin;
                var percentage = (factor / (rangeMax - rangeMin) / 2) + 0.5;
                var newMax = Math.floor(maxDimension * percentage);

                if (width > newMax || height > newMax)
                {
                    var ratio;

                    if (height !== 0) {
                        if (width >= height)
                        {
                            ratio = newMax / width;
                            height *= ratio;
                            width = newMax;
                        }
                        else
                        {
                            ratio = newMax / height;
                            width *= ratio;
                            height = newMax;
                        }
                    }
                    else
                    {
                        width = newMax;
                        height = "auto";
                    }

                    $img.add($box).css({
                        'height' : height,
                        'width'  : width,
                    });
                }
            });
        }
    };

    // Events
    blueprint.prototype.events = {
        actionTabClick : function(event) {
            var widget  = event.data.widget;
            var $this   = $(this);
            var $target = $(event.target);

            console.log($target, $target.parents());
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

        attachmentCollapseClick : function(event) {
            var widget  = event.data.widget;
            var $link   = $(this);
            var $item   = $link.parents("li.post-attachment");
            var $img    = $(widget.options.selector['attachment-image'], $item);
            var $inline = $(widget.options.selector['attachment-inline'], $item);

            if ($inline.length > 0)
            {
                $("[src]", $item).attr('src', "");
                $inline[0].pause(0);
                $inline[0].src = "";
                $inline[0].load();
            }

            if ($img.is('[data-thumb-width]'))
            {
                $img.css('width', $img.attr('data-thumb-width') + "px");
            }

            if ($img.is('[data-thumb-height]'))
            {
                $img.css('height', $img.attr('data-thumb-height') + "px");
            }

            $item.removeClass('attachment-expanded');
            $img.attr('src', $link.attr('data-thumb-url'));
            $inline.remove();
            $img.toggle(true);
            $img.parent().css({
                'background-image' : 'none',
                'min-width'        : '',
                'min-height'       : '',
            });

            if (event.delegateTarget === widget.$widget[0])
            {
                widget.$widget[0].scrollIntoView({
                    block    : "start",
                    behavior : "instant"
                });
            }

            event.preventDefault();
            return false;
        },

        attachmentMediaCheck : function(event) {
            var widget = event.data.widget;
            var $img   = $(this);
            var $link  = $img.parents(widget.options.selector['attachment-link']).first();

            // Check for previous results.
            if ($link.is(".attachment-canplay"))
            {
                return true;
            }
            else if ($link.is(".attachment-cannotplay"))
            {
                return false;
            }

            // Test audio.
            if ($img.is(widget.options.selector['attachment-image-audio']))
            {
                var $audio  = $("<audio></audio>");
                var mimetype = $img.attr('data-mime');
                var fileext  = $link.attr('href').split('.').pop();

                if ($audio[0].canPlayType(mimetype) != "" || $audio[0].canPlayType("audio/"+fileext) != "")
                {
                    $link.addClass("attachment-canplay");
                    return true;
                }
            }
            // Test video.
            else if ($img.is(widget.options.selector['attachment-image-video']))
            {
                var $video   = $("<video></video>");
                var mimetype = $img.attr('data-mime');
                var fileext  = $link.attr('href').split('.').pop();

                if ($video[0].canPlayType(mimetype) || $video[0].canPlayType("video/"+fileext))
                {
                    $link.addClass("attachment-canplay");
                    return true;
                }
            }
            else
            {
                $link.addClass("attachment-canplay");
                return true;
            }

            // Add failure results.
            $link.addClass('attachment-cannotplay');

            $img
                .addClass('attachment-type-file')
                .removeClass('attachment-type-video attachment-type-audio');

            return false;
        },

        attachmentMediaEnded : function(event) {
            var widget  = event.data.widget;
            var $media  = $(this);
            var $item   = $media.parents("li.post-attachment");
            var $link   = $(widget.options.selector['attachment-link'], $item);
            var $img    = $(widget.options.selector['attachment-image'], $item);
            var $inline = $(widget.options.selector['attachment-inline'], $item);

            $item.removeClass('attachment-expanded');
            $img.attr('src', $link.attr('data-thumb-url'));
            $inline.remove();
            $img.toggle(true);
            $img.parent().addClass('attachment-grow');
        },

        attachmentMediaMouseOver : function(event) {
            var widget   = event.data.widget;

            if (!widget.is('attachment_preview') || ib.isMobile()) {
                return true;
            }

            var $img = $(this);

            if ($img.parents("."+widget.options.classname['image-expanded']).length) {
                return true;
            }

            var $link = $img.parents(widget.options.selector['attachment-link']).first();
            var $preview = $(widget.options.selector['hover-box']);

            if ($link.attr('data-download-url') === undefined) {
                return true;
            }

            $preview.children().attr('src', $link.attr('data-download-url'));

            widget.previewTimer = setTimeout(function() {
                $preview.show();
            }, widget.options.preview_delay);
        },

        attachmentMediaMouseOut : function(event) {
            var widget   = event.data.widget;
            var $img     = $(this);
            var $link    = $img.parents(widget.options.selector['attachment-link']).first();
            var $preview = $(widget.options.selector['hover-box']);

            $preview.children().attr('src', "");
            $preview.hide();
            clearTimeout(widget.previewTimer);
        },

        attachmentExpandClick : function(event) {
            var widget = event.data.widget;
            var $link  = $(this);
            var $item  = $link.parents("li.post-attachment");
            var $img   = $(widget.options.selector['attachment-image'], $link);

            // We don't do anything if the user is CTRL+Clicking,
            // or if the file is a download type.
            if (event.altKey || event.ctrlKey || $img.is(widget.options.selector['attachment-image-download']))
            {
                return true;
            }

            var $preview = $(widget.options.selector['hover-box']);

            // Kill an overlay if it exists.
            $img.trigger('mouseout.ib-post');

            // If the attachment type is not an image, we can't expand inline.
            if ($img.is(widget.options.selector['attachment-image-expandable']))
            {
                $item.addClass('attachment-expanded');
                $img.parent().css({
                        // Removed because the effect was gross.
                        // 'background-image'    : 'url(' + $link.attr('data-thumb-url') + ')',
                        // 'background-size'     : '100%',
                        // 'background-repeat'   : 'no-repeat',
                        // 'background-position' : 'center center',
                        'min-width'           : $img.width() + 'px',
                        'min-height'          : $img.height() + 'px',
                        'height'              : "auto",
                        'width'               : "auto",
                        'opacity'             : 0.5,
                    });

                // Clear source first so that lodaing works correctly.
                $img
                    .attr('data-thumb-width', $img.width())
                    .attr('data-thumb-height', $img.height())
                    .attr('src', "")
                    .css({
                        'width'  : "auto",
                        'height' : "auto"
                    });

                $img
                    // Change the source of our thumb to the full image.
                    .attr('src', $link.attr('data-download-url'))
                    // Bind an event to handle the image loading.
                    .one("load", function() {
                        // Remove our opacity change.
                        $(this).parent().css({
                            // 'background-image' : "none",
                            // 'min-width'        : '',
                            // 'min-height'       : '',
                            'opacity'          : ""
                        });
                    });

                event.preventDefault();
                return false;
            }
            else if ($img.is(widget.options.selector['attachment-image-audio']))
            {
                var $audio  = $("<audio controls autoplay class=\"attachment-inline attachment-audio\"></audio>");
                var $source = $("<source />");
                var mimetype = $img.attr('data-mime');
                var fileext  = $link.attr('href').split('.').pop();

                if ($audio[0].canPlayType(mimetype) || $audio[0].canPlayType("audio/"+fileext))
                {
                    $item.addClass('attachment-expanded');

                    $source
                        .attr('src',  $link.attr('href'))
                        .attr('type', $img.attr('data-mime'))
                        .one('error', function(event) {
                            // Our source has failed to load!
                            // Trigger a download.
                            $img
                                .trigger('click')
                                .removeClass('attachment-type-audio')
                                .addClass('attachment-type-file');
                        })
                        .appendTo($audio);

                    $audio.insertBefore($link);
                    widget.bindMediaEvents($audio);

                    $audio.parent().addClass('attachment-grow');

                    event.preventDefault();
                    return false;
                }
            }
            else if ($img.is(widget.options.selector['attachment-image-video']))
            {
                var $video   = $("<video controls autoplay class=\"attachment-inline attachment-video\"></video>");
                var $source  = $("<source />");
                var mimetype = $img.attr('data-mime');
                var fileext  = $link.attr('href').split('.').pop();

                if ($video[0].canPlayType(mimetype) || $video[0].canPlayType("video/"+fileext))
                {
                    $item.addClass('attachment-expanded');

                    $source
                        .attr('src',  $link.attr('href'))
                        .attr('type', $img.attr('data-mime'))
                        .one('error', function(event) {
                            // Our source has failed to load!
                            // Trigger a download.
                            $img
                                .trigger('click')
                                .removeClass('attachment-type-video')
                                .addClass('attachment-type-download attachment-type-failed');
                        })
                        .appendTo($video);

                    $img.toggle(false);

                    widget.bindMediaEvents($video);
                    $video.insertBefore($link);

                    event.preventDefault();
                    return false;
                }
                else
                {
                    $img
                        .addClass('attachment-type-file')
                        .removeClass('attachment-type-video');

                    return true;
                }
            }
            else
            {
                return true;
            }
        },

        citeClick : function(event) {
            if (event.altKey || event.ctrlKey)
            {
                return true;
            }

            var $cite     = $(this);
            var board_uri = $cite.attr('data-board_uri');
            var board_id  = parseInt($cite.attr('data-board_id'), 10);
            var $target   = $("#post-"+board_uri+"-"+board_id);

            if ($target.length)
            {
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
            if ($("#"+post_id).length)
            {
                $cite.attr('data-no-instant', "data-no-instant");
            }
            else
            {
                $cite.removeAttr('data-no-instant');
            }

            widget.clearCites();

            if (widget.citeLoad == post_id)
            {
                return true;
            }

            // Loads session storage for our post if it exists.
            if (typeof sessionStorage === "object")
            {
                $post = $(sessionStorage.getItem( post_id ));

                if ($post instanceof jQuery && $post.length)
                {
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

                if (widget.citeLoad === post_id)
                {
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
            if (typeof window.hljs === "object")
            {
                $(widget.options.selector['element-code'], $widget).each(function()
                {
                    window.hljs.highlightBlock(this);
                });
            }
            else
            {
                console.log("post.codeHighlight: missing hljs");
            }
        },

        postClick : function(event) {
            var widget = event.data.widget;

            if ($(widget.options.selector['mode-reply']).length !== 0)
            {
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
                $postbox.trigger('click');

                return false;
            }

            return true;
        },

        postContentUpdate : function(event) {
            var widget  = event.data.widget;
            var $widget =  event.data.$widget;

            $(widget.options.selector.author_id, $widget)
                .toggle(widget.is('author_id'));

            widget.addAuthorship();
            $widget.trigger('highlight-syntax');
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
        },

        windowResize : function(event) {
            event.data.widget.scaleThumbnails();
        }
    };

    ib.widget("post", blueprint, options);
})(window, window.jQuery);
