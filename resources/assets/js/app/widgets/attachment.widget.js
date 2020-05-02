// ===========================================================================
// Purpose          : Attachments
// Contributors     : jaw-sh
// Widget Version   : 2
// ===========================================================================

(function(window, $, undefined) {
    // Widget blueprint
    var blueprint = ib.getBlueprint();

    // Configuration options
    var options = {
        attachment_preview : {
            type : "bool",
            initial : true
        },
        csam_delete : {
            type : "bool",
            initial : true

        }
    };

    blueprint.prototype.defaults = {
        preview_delay : 100,

        // Important class names.
        classname : {
            'image-expanded' : "attachment-expanded",
            'image-deleted' : "attachment-deleted"
        },

        // Selectors for finding and binding elements.
        selector : {
            'attachment'         : ".attachment",
            'attachment-media'   : "audio.attachment-inline, video.attachment-inline",
            'attachment-image'   : "img.attachment-img",
            'attachment-image-download'   : "img.attachment-type-file",
            'attachment-image-expandable' : "img.attachment-type-img",
            'attachment-image-audio'      : "img.attachment-type-audio",
            'attachment-image-video'      : "img.attachment-type-video",
            'attachment-inline'  : "audio.attachment-inline, video.attachment-inline",
            'attachment-link'    : ".attachment-link",
            'attachment-expand'   : ".attachment-link:not(.attachment-expanded)",
            'attachment-collapse' : ".attachment-link.attachment-expanded",

            'hover-box'      : "#attachment-preview",
            'hover-box-img'  : "#attachment-preview-img",
            'hover-box-video'  : "#attachment-preview-video"
        }
    };

    // Event hooks
    blueprint.prototype.events = {
        attachmentMediaClick : function(event) {
            var $widget = event.data.$widget;
            var $link = $(this);
            var $target = $(event.target);
            var allowDefault = true;

            if ($widget.is(".attachment-expanded")) {
                var collapse = true;

                if ($target.is(".attachment-video")) {
                    collapse = ($target.height() - event.originalEvent.offsetY) > 75;
                }

                if (collapse) {
                    $link.trigger('ended');
                    $link.trigger('media-collapse');
                    allowDefault = false;
                }
            }
            else {
                $link.trigger('media-expand');
                allowDefault = !$widget.is(".attachment-expanded");
            }

            if (!allowDefault) {
                event.preventDefault();
                event.stopPropagation();
                return false;
            }
        },

        attachmentMediaCollapse : function(event) {
            var widget  = event.data.widget;
            var $widget = event.data.$widget;
            var $link   = $(this);
            var $img    = $(widget.options.selector['attachment-image'], $widget);
            var $inline = $(widget.options.selector['attachment-inline'], $widget);

            if ($inline.length > 0) {
                // $("[src]", $widget).removeAttr('src');
                $inline[0].pause(0);
                $inline[0].removeAttribute('src');
                $inline[0].load();
            }

            if ($img.is('[data-thumb-width]')) {
                $img.css('width', $img.attr('data-thumb-width') + "px");
            }
            if ($img.is('[data-thumb-height]')) {
                $img.css('height', $img.attr('data-thumb-height') + "px");
            }

            $widget.removeClass('attachment-expanded');
            $img.attr('src', $link.data('thumb-url') || $img.attr('src'));
            $inline.remove();
            $img.toggle(true);
            $img.parent().css({
                'background-image' : 'none',
                'min-width'        : '',
                'min-height'       : '',
            });

            if (event.delegateTarget === widget.$widget[0]) {
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
            if ($link.is(".attachment-canplay")) {
                return true;
            }
            else if ($link.is(".attachment-cannotplay")) {
                return false;
            }

            // Test audio.
            if ($img.is(widget.options.selector['attachment-image-audio'])) {
                var $audio  = $("<audio></audio>");
                var mimetype = $img.attr('data-mime');
                var fileext  = $link.attr('href').split('.').pop();

                if ($audio[0].canPlayType(mimetype) != "" || $audio[0].canPlayType("audio/"+fileext) != "") {
                    $widget.addClass("attachment-canplay");
                    return true;
                }
            }
            // Test video.
            else if ($img.is(widget.options.selector['attachment-image-video'])) {
                var $video   = $("<video></video>");
                var mimetype = $img.attr('data-mime');
                var fileext  = $link.attr('href').split('.').pop();

                if ($video[0].canPlayType(mimetype) || $video[0].canPlayType("video/"+fileext)) {
                    $link.addClass("attachment-canplay");
                    return true;
                }
            }
            else {
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

        attachmentMediaPlay : function(event) {
            var $media  = $(this).parents(".attachment-type-audio");
            $media.addClass('playing');
        },

        attachmentMediaPause : function(event) {
            var $media  = $(this).parents(".attachment-type-audio");
            $media.removeClass('playing');
        },

        attachmentMediaEnded : function(event) {
            var widget  = event.data.widget;
            var $widget = event.data.$widget;
            var $media  = $(this);
            var $link   = $(widget.options.selector['attachment-link'], $widget);
            var $img    = $(widget.options.selector['attachment-image'], $widget);
            var $inline = $(widget.options.selector['attachment-inline'], $widget);

            $widget.removeClass('attachment-expanded', 'playing');
            $img.attr('src', $link.attr('data-thumb-url') || $img.attr('src'));
            $inline.remove();
            $img.toggle(true);
            $img.parent().addClass('attachment-grow');

            event.preventDefault();
            return false;
        },

        attachmentMediaLoadedData : function(event) {
            var rect = this.getBoundingClientRect();
            if (rect.bottom > window.innerHeight) {
                this.scrollIntoView(false);
            }
            if (rect.top < 0) {
                this.scrollIntoView();
            }

            this.play();
        },

        attachmentMediaMouseOver : function(event) {
            var widget   = event.data.widget;

            if (!widget.is('attachment_preview') || ib.isMobile()) {
                return true;
            }

            var $img = $(this);

            // content is not open already
            if ($img.parents("."+widget.options.classname['image-expanded']).length) {
                return true;
            }

            var $link = $img.parents(widget.options.selector['attachment-link']).first();

            if ($link.attr('data-download-url') === undefined) {
                return true;
            }

            var multimedia = false;
            var $previewContent;

            if ($img.is(widget.options.selector['attachment-image-expandable'])) {
                $previewContent = $(widget.options.selector['hover-box-img']);
            }
            else if ($img.is(widget.options.selector['attachment-image-video'])) {
                multimedia = true;
                $previewContent = $(widget.options.selector['hover-box-video']);
            }
            else {
                return true;
            }

            var $preview = $(widget.options.selector['hover-box']);

            widget.previewTimer = setTimeout(function() {
                $preview.show();
                $previewContent[0].src = $link.attr('data-download-url');

                if (multimedia) {
                    $previewContent[0].play();
                }
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

        attachmentMediaExpand : function (event) {
            var widget  = event.data.widget;
            var $widget = event.data.$widget;
            var $link   = $(this);
            var $img    = $(widget.options.selector['attachment-image'], $link);

            // We don't do anything if the user is CTRL+Clicking,
            // or if the file is a download type.
            if (event.altKey || event.ctrlKey || $img.is(widget.options.selector['attachment-image-download'])) {
                return true;
            }

            if ($link.parents(".index-catalog").length) {
                return true;
            }

            var $preview = $(widget.options.selector['hover-box']);

            // Kill an overlay if it exists.
            $img.trigger('mouseout.ib-attachment');

            // If the attachment type is not an image, we can't expand inline.
            if ($img.is(widget.options.selector['attachment-image-expandable'])) {
                $widget.addClass('attachment-expanded');
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
            else if ($img.is(widget.options.selector['attachment-image-audio'])) {
                var $audio  = $("<audio controls autoplay class=\"attachment-inline attachment-audio\"></audio>");
                var $source = $("<source />");
                var mimetype = $img.attr('data-mime');
                var fileext  = $link.attr('href').split('.').pop();

                if ($audio[0].canPlayType(mimetype) || $audio[0].canPlayType("audio/"+fileext)) {
                    $widget.addClass('attachment-expanded');

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

                    $audio.insertAfter($link);
                    widget.bindMediaEvents($audio);

                    $audio.parent().addClass('attachment-grow');

                    event.preventDefault();
                    return false;
                }
            }
            else if ($img.is(widget.options.selector['attachment-image-video'])) {
                var $video   = $("<video controls autoplay class=\"attachment-inline attachment-video\"></video>");
                var $source  = $("<source />");
                var mimetype = $img.attr('data-mime');
                var fileext  = $link.attr('href').split('.').pop();

                if ($video[0].canPlayType(mimetype) || $video[0].canPlayType("video/"+fileext)) {
                    $widget.addClass('attachment-expanded');

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
                    $video.insertAfter($link);

                    event.preventDefault();
                    return false;
                }
                else {
                    $img
                        .addClass('attachment-type-file')
                        .removeClass('attachment-type-video');

                    return true;
                }
            }
            else {
                return true;
            }
        },

        fileBanned : function (event) {
            var widget = event.data.widget;
            var $widget = event.data.$widget;

            if (!widget.is('csam_delete')) {
                return false;
            }

            // get URLs before deleting
            var $link = $(widget.options.selector['attachment-image'], $widget);
            var dlUrl = $link.attr('data-download-url');
            var thumbUrl = $link.attr('data-thumb-url');

            // delete from dom
            $widget.addClass(event.data.widget.options.classname['image-deleted']);
            $widget.height($widget.height());
            $widget.width($widget.width());
            $widget[0].innerHTML = window.app.lang.attachment.csam;

            // pull from cache
            //if (window.caches !== 'undefined') {
            //    window.caches.delete(dlUrl);
            //    window.caches.delete(thumbUrl);
            //    $widget[0].innerHTML += window.app.lang.attachment.csam_uncache;
            //}
        },

        windowResize : function (event) {
            event.data.widget.scaleThumbnails();
        }
    };

    // Scales thumbnails down based on resolution.
    blueprint.prototype.scaleThumbnails = function() {
        // Disabling this for now because it behaves strangely and I don't even know why it's here.
        return true;

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

                if (width > newMax || height > newMax) {
                    var ratio;

                    if (height !== 0) {
                        if (width >= height) {
                            ratio = newMax / width;
                            height *= ratio;
                            width = newMax;
                        }
                        else {
                            ratio = newMax / height;
                            width *= ratio;
                            height = newMax;
                        }
                    }
                    else {
                        width = newMax;
                        height = "auto";
                    }

                    $img.css({
                        'height' : height,
                        'width'  : width,
                    });
                }
            });
        }
    };

    // Main Widget Initialization Binding
    blueprint.prototype.bind = function() {
        var widget  = this;
        var $widget = this.$widget;
        var data = {
            widget  : widget,
            $widget : $widget
        };

        $widget
            .on('file-banned.ib-attachment', data, widget.events.fileBanned)
            .on('mouseover.ib-attachment', widget.options.selector['attachment-image'], data, widget.events.attachmentMediaMouseOver)
            .on('mouseout.ib-attachment', widget.options.selector['attachment-image'], data, widget.events.attachmentMediaMouseOut)
            .on('click.ib-attachment', widget.options.selector['attachment-link'], data, widget.events.attachmentMediaClick)
            .on('media-check.ib-attachment', widget.options.selector['attachment-image'], data, widget.events.attachmentMediaCheck)
            .on('media-collapse.ib-attachment', widget.options.selector['attachment-link'], data, widget.events.attachmentMediaCollapse)
            .on('media-expand.ib-attachment', widget.options.selector['attachment-link'], data, widget.events.attachmentMediaExpand)
        ;

        $(window).on('resize.ib-post', data, widget.events.windowResize);

        widget.scaleThumbnails();
    };


    blueprint.prototype.bindMediaEvents = function($element) {
        var data = {
            widget  : this,
            $widget : this.$widget
        };

        $element
            .on('loadeddata.ib-attachment', data, this.events.attachmentMediaLoadedData)
            // collapse video when the video is done playing
            .on('ended.ib-attachment', data, this.events.attachmentMediaEnded)
            // collapse video when it's useless-clicked
            .on('click.ib-attachment', data, this.events.attachmentMediaClick)
            .on('play.ib-attachment', data, this.events.attachmentMediaPlay)
            .on('pause.ib-attachment', data, this.events.attachmentMediaPause);
        }

    ib.widget("attachment", blueprint, options);
})(window, window.jQuery);
