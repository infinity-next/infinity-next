/**
 * Post form widget
 */
ib.widget("postbox", function(window, $, undefined) {
	
	var widget = {
		
		// Dropzone instance.
		dropzone  : null,
		
		// jQuery UI bind indicators
		resizable : false,
		draggable : false,
		axis      : ib.ltr ? "sw" : "se",
		
		// Widgets instance.
		notices   : null,
		
		// Number of uploads running.
		// Used to prevent premature form submission.
		activeUploads : 0,
		
		// The default values that are set behind init values.
		defaults : {
			
			checkFileUrl  : window.app.board_url + "check-file",
			
			// Selectors for finding and binding elements.
			selector : {
				'widget'          : "#post-form",
				'notices'         : "[data-widget=notice]:first",
				'autoupdater'     : ".autoupdater:first",
				
				'dropzone'        : ".dz-container",
				
				'submit-post'     : "#submit-post",
				
				'form-fields'     : ".form-fields",
				'form-body'       : "#body",
				'form-clear'      : "#subject, #body, #captcha",
				'form-spoiler'    : ".dz-spoiler-check",
				
				'captcha'         : ".captcha",
				'captcha-row'     : ".row-captcha",
				'captcha-field'   : ".field-control",
				
				'button-close'    : ".menu-icon-close",
				'button-maximize' : ".menu-icon-maximize",
				'button-minimize' : ".menu-icon-minimize"
			},
			
			template : {
				'counter'         : "<tt id=\"body-counter\"></tt>",
			},
			
			dropzone : {
				// Localization strings.
				// dictDefaultMessage: "Drop files here to upload",
				// dictFallbackMessage: "Your browser does not support drag'n'drop file uploads.",
				// dictFallbackText: "Please use the fallback form below to upload your files like in the olden days.",
				// dictFileTooBig: "File is too big ({{filesize}}MiB). Max filesize: {{maxFilesize}}MiB.",
				// dictInvalidFileType: "You can't upload files of this type.",
				// dictResponseError: "Server responded with {{statusCode}} code.",
				// dictCancelUpload: "Cancel upload",
				// dictCancelUploadConfirmation: "Are you sure you want to cancel this upload?",
				// dictRemoveFile: "Remove file",
				// dictRemoveFileConfirmation: null,
				// dictMaxFilesExceeded: "You can not upload any more files.",
				
				// The input field name.
				paramName      : "files",
				
				// File upload URL
				url            : window.app.board_url + "upload-file",
				
				// Allow multiple uploads.
				uploadMultiple : true,
				
				// Maximum filesize (MB)
				maxFilesize    : window.app.settings.attachmentFilesize / 1024,
				
				// Binds the instance to our widget.
				init: function() {
					widget.dropzone = this;
					
					$(this.element).append("<input type=\"hidden\" name=\"dropzone\" value=\"1\" />");
				},
				
				// Handles the acceptance of files.
				accept : function(file, done) {
					var reader = new FileReader();
					
					widget.$widget.trigger('fileUploading', [ file ]);
					
					reader.onload = function (event) {
						var Hasher = new SparkMD5;
						Hasher.appendBinary(this.result);
						
						var hash = Hasher.end();
						file.hash = hash;
						
						jQuery.get( widget.options.checkFileUrl, {
							'md5' : hash
						})
							.done(function(data, textStatus, jqXHR) {
								if (typeof data === "object")
								{
									var response = data;
									
									jQuery.each(response, function(index, datum) {
										// Make sure this datum is for our file.
										if (index !== hash)
										{
											return true;
										}
										
										// Does this file exist?
										if (datum !== null)
										{
											// Is the file banned?
											if (datum.banned == 1)
											{
												// Language
												console.log("File "+file.name+" is banned from being uploaded.");
												
												file.status = Dropzone.ERROR;
												widget.dropzone.emit("error", file, "File <tt>"+file.name+"</tt> is banned from being uploaded", jqXHR);
												widget.dropzone.emit("complete", file);
											}
											else
											{
												console.log("File "+file.name+" already exists.");
												
												file.status = window.Dropzone.SUCCESS;
												widget.dropzone.emit("success", file, datum, jqXHR);
												widget.dropzone.emit("complete", file);
											}
										}
										// If no presence, upload anew.
										else
										{
											console.log("Uploading file "+file.name+".");
											
											done();
										}
									});
								}
								else
								{
									console.log("Received weird response:", data);
								}
							});
					};
					
					reader.readAsBinaryString(file);
				},
				
				canceled : function(file) {
					widget.$widget.trigger('fileCanceled', [ file ]);
				},
				
				error : function(file, message, xhr) {
					widget.notices.push(message, 'error');
					
					$(file.previewElement).remove();
					
					widget.$widget.trigger('fileFailed', [ file ]);
				},
				
				removedfile : function(file) {
					var _ref;
					
					if (file.previewElement) {
						if ((_ref = file.previewElement) != null) {
							_ref.parentNode.removeChild(file.previewElement);
						}
					}
					
					widget.resizePostbox();
					
					return this._updateMaxFilesReachedClass();
				},
				
				success : function(file, response, xhr) {
					if (typeof response !== "object")
					{
						var response = jQuery.parseJSON(response);
					}
					
					if (typeof response.errors !== "undefined")
					{
						jQuery.each(response.errors, function(field, errors)
						{
							jQuery.each(errors, function(index, error)
							{
								widget.dropzone.emit("error", file, error, xhr);
								widget.dropzone.emit("complete", file);
							});
						});
					}
					else
					{
						var $preview = $(file.previewElement);
						
						$preview
							.addClass('dz-success')
							.append("<input type=\"hidden\" name=\""+widget.options.dropzone.paramName+"[hash][]\" value=\""+file.hash+"\" />")
							.append("<input type=\"hidden\" name=\""+widget.options.dropzone.paramName+"[name][]\" value=\""+file.name+"\" />")
						;
						
						$("[data-dz-spoiler]", $preview)
							.attr('name', widget.options.dropzone.paramName+"[spoiler][]");
					}
					
					widget.$widget.trigger('fileUploaded', [ file ]);
				},
				
				previewTemplate : 
					"<div class=\"dz-preview dz-file-preview\">" +
						"<div class=\"dz-image\">" +
							"<img data-dz-thumbnail />" +
						"</div>" +
						"<div class=\"dz-actions\">" +
							"<span class=\"dz-remove\" data-dz-remove>x</span>" +
							"<label class=\"dz-spoiler\">" +
								"<input type=\"checkbox\" class=\"dz-spoiler-check\" name=\"\" value=\"\" />" +
								"<input type=\"chidden\" class=\"dz-spoiler-hidden\" value=\"0\" data-dz-spoiler />" +
								"<span class=\"dz-spoiler-desc\">Spoiler</span>" +
							"</label>" +
						"</div>" +
						"<div class=\"dz-details\">" +
							"<div class=\"dz-size\"><span data-dz-size></span></div>" +
							"<div class=\"dz-filename\"><span data-dz-name></span></div>" +
						"</div>" +
						"<div class=\"dz-progress\"><span class=\"dz-upload\" data-dz-uploadprogress></span></div>" +
						"<div class=\"dz-success\">" +
							"<div class=\"dz-success-mark\">" +
								"<svg viewBox=\"0 0 54 54\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:sketch=\"http://www.bohemiancoding.com/sketch/ns\">" +
									"<g id=\"Page-1\" stroke=\"none\" stroke-width=\"1\" fill=\"none\" fill-rule=\"evenodd\" sketch:type=\"MSPage\">" +
										"<path d=\"M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z\" " +
											"id=\"Oval-2\" " +
											"stroke-opacity=\"0.198794158\" " +
											"stroke=\"#747474\" " +
											"fill-opacity=\"0.816519475\" " +
											"fill=\"#FFFFFF\" " +
											"sketch:type=\"MSShapeGroup\" " +
										"></path>" +
									"</g>" +
								"</svg>" +
							"</div>" +
						"</div>" +
					"</div>"
			}
		},
		
		// Compiled settings.
		options  : false,
		
		hasCaptcha : function() {
			return $(widget.options.selector['captcha-row'], widget.$widget).is(":visible");
		},
		
		resizePostbox : function() {
			if (widget.resizable)
			{
				if (window.innerHeight < 480 || window.innerWidth < 728)
				{
					widget.unbind.draggable();
					widget.unbind.resize();
				}
				else
				{
					// Trigger resize on the post body.
					// Forces the post box to obey new window constraints.
					var $post    = $(widget.options.selector['form-body'], widget.$widget);
					var uiWidget = $post.data('ui-resizable');
					
					// Widget is bound and we have data
					if (uiWidget && !jQuery.isEmptyObject(uiWidget.prevPosition))
					{
						// This is copy+pasted from the source code because there is no polite way
						// to handle it otherwise.
						uiWidget._updatePrevProperties();
						uiWidget._trigger( "resize", event, uiWidget.ui() );
						uiWidget._applyChanges();
					}
				}
			}
			else if (window.innerHeight >= 480 && window.innerWidth >= 728)
			{
				widget.bind.resize();
			}
		},
		
		// Events
		events   : {
			
			bodyChange    : function(event) {
				if (widget.$counter && widget.$counter instanceof jQuery)
				{
					var $body = $(this);
					var len   = $body.val().length;
					var text  = "<strong id=\"body-counter-curr\">" + len + "</strong>";
					var valid = true;
					var free  = true;
					var max   = parseInt(window.app.board_settings.postMaxLength, 10);
					var min   = parseInt(window.app.board_settings.postMinLength, 10);
					
					if (!isNaN(max))
					{
						text  = text + "<span id=\"body-counter-max\">" + max + "</span>";
						free  = false;
						valid = valid && (len <= max);
					}
					
					if (!isNaN(min))
					{
						text  = "<span id=\"body-counter-min\">" + min + "</span>" + text;
						free  = false;
						valid = valid && (len >= min);
					}
					
					if (!free)
					{
						widget.$counter
							.toggleClass("counter-valid",    valid)
							.toggleClass("counter-invalid", !valid)
							.html(text);
					}
				}
			},
			
			captchaHide   : function() {
				$(widget.options.selector['captcha-row'], widget.$widget)
					.hide();
			},
			
			captchaShow   : function() {
				$(widget.options.selector['captcha-row'], widget.$widget)
					.show();
			},
			
			closeClick    : function(event) {
				// Tweak classes.
				widget.$widget
					.removeClass("postbox-maximized postbox-minimized")
					.addClass("postbox-closed");
				
				// Unbind the jQuery UI resize.
				widget.unbind.resize();
				
				// Prevents formClick from immediately firing.
				event.stopPropagation();
			},
			
			fileUploading : function(event, file) {
				++widget.activeUploads;
				console.log(widget.activeUploads + " concurrent uploads.");
				
				$(widget.options.selector['submit-post'], widget.$widget)
					.prop('disabled', widget.activeUploads > 0);
			},
			
			fileCanceled  : function(event, file) {
				--widget.activeUploads;
				console.log(widget.activeUploads + " concurrent uploads.");
				
				$(widget.options.selector['submit-post'], widget.$widget)
					.prop('disabled', widget.activeUploads > 0);
			},
			
			fileFailed    : function(event, file) {
				--widget.activeUploads;
				console.log(widget.activeUploads + " concurrent uploads.");
				
				$(widget.options.selector['submit-post'], widget.$widget)
					.prop('disabled', widget.activeUploads > 0);
			},
			
			fileUploaded  : function(event, file) {
				--widget.activeUploads;
				console.log(widget.activeUploads + " concurrent uploads.");
				
				$(widget.options.selector['submit-post'], widget.$widget)
					.prop('disabled', widget.activeUploads > 0);
			},
			
			formClear     : function() {
				var $form = widget.$widget;
				
				// Stops redundant loading of captcha when we don't need one.
				if (widget.hasCaptcha())
				{
					$(widget.options.selector['captcha'], widget.$widget).trigger('reload');
				}
				
				if (widget.dropzone)
				{
					widget.dropzone.removeAllFiles();
				}
				
				$(widget.options.selector['form-clear'], $form)
					.val("")
					.html("");
				
				$(widget.options.selector['form-body'], $form)
					.trigger('change')
					.focus();
			},
			
			formClick     : function(event) {
				if (widget.$widget.is(".postbox-closed"))
				{
					// Tweak classes.
					widget.$widget.removeClass("postbox-minimized postbox-closed postbox-maximized");
					
					// Rebind jQuery UI widgets.
					widget.bind.draggable();
					widget.bind.resize();
				}
			},
			
			formSubmit    : function(event) {
				widget.notices.clear();
				
				var $form       = $(this).add("<input name=\"messenger\" value=\"1\" />");
				var $updater    = $(widget.options.selector['autoupdater']);
				var autoupdater = false;
				
				// Note: serializeJSON is a plugin we use to convert form data into
				// a multidimensional array for application/json posts.
				
				if ($updater.length && $updater[0].widget)
				{
					var data = $form.serialize();
					
					autoupdater = $updater[0].widget;
					data = $form
						.add("<input name=\"updatesOnly\" value=\"1\" />")
						.add("<input name=\"updateHtml\" value=\"1\" />")
						.add("<input name=\"updatedSince\" value=\"" + autoupdater.updateLast +"\" />")
						.serializeJSON();
				}
				else
				{
					var data = $form.serializeJSON();
				}
				
				// Indicate we want a full messenger response.
				data.messenger = true;
				
				$form.prop('disabled', true);
				
				jQuery.ajax({
					type:        "POST",
					method:      "PUT",
					url:         $form.attr('action') + ".json",
					data:        data,
					dataType:    "json",
					contentType: "application/json; charset=utf-8"
				})
					.done(function(response, textStatus, jqXHR) {
						$form.prop('disabled', false);
						
						
						if (typeof response !== "object")
						{
							try
							{
								response = jQuery.parseJSON(response);
							}
							catch (exception)
							{
								console.log("Post submission returned unpredictable response. Refreshing.");
								window.location.reload();
								return;
							}
						}
						
						// This event trigger will cascade effects with our supplemental Messenger information.
						if (response.messenger)
						{
							$(window).trigger('messenger', response);
							var json = response.data;
						}
						else
						{
							var json = response;
						}
						
						if (typeof json.redirect !== "undefined")
						{
							console.log("Post submitted. Redirecting.");
							window.location = json.redirect;
						}
						else if (typeof json.errors !== "undefined")
						{
							console.log("Post rejected.");
							
							jQuery.each(json.errors, function(field, errors)
							{
								jQuery.each(errors, function(index, error)
								{
									widget.notices.push(error, 'error');
								});
							});
						}
						else if (autoupdater !== false)
						{
							console.log("Post submitted. Inline updating.");
							
							clearInterval(autoupdater.updateTimer);
							
							jqXHR.widget = autoupdater;
							autoupdater.updating    = true;
							autoupdater.updateTimer = false;
							autoupdater.updateAsked = parseInt(parseInt(Date.now(), 10) / 1000, 10);
							autoupdater.events.updateSuccess(json, textStatus, jqXHR, true);
							autoupdater.events.updateComplete(json, textStatus, jqXHR);
							
							widget.events.formClear();
						}
						else
						{
							console.log("Post submitted. No autoupdater. Refreshing.");
							window.location.reload();
						}
					});
				
				event.preventDefault();
				return false;
			},
			
			maximizeClick : function(event) {
				// Tweak classes.
				widget.$widget
					.removeClass("postbox-minimized postbox-closed")
					.addClass("postbox-maximized");
				
				// Remove jQuery UI widgets.
				widget.unbind.draggable();
				widget.unbind.resize();
			},
			
			messenger     : function(event, messages) {
				if (messages.messenger)
				{
					// Toggles captcha based on messenger information.
					if (messages.captcha)
					{
						widget.events.captchaShow();
						$(widget.options.selector['captcha-row'], widget.$widget).trigger('load', messages.captcha);
					}
					else
					{
						widget.events.captchaHide();
					}
				}
			},
			
			minimizeClick : function(event) {
				// Tweak classes.
				widget.$widget
					.removeClass("postbox-maximized postbox-closed")
					.addClass("postbox-minimized");
				
				// Rebind jQuery UI Resize.
				widget.bind.draggable();
				widget.bind.resize();
			},
			
			pageChange    : function() {
				widget.options.checkFileUrl         = window.app.board_url + "check-file";
				widget.dropzone.options.url         = window.app.board_url + "upload-file";
				widget.dropzone.options.maxFilesize = window.app.settings.attachmentFilesize / 1024;
			},
			
			postDragStop  : function(event, ui) {
				if (ib.ltr && widget.axis == "sw")
				{
					// Okay, so:
					// Our styling using top,right.
					// Draggable sets the position using top,left.
					// This causes the box to expand to the right when dragging with the resize handles.
					// Because you cannot drag to expand and drag to move at the same time, it's safe
					// to fix the right/left assignent after the drag has stopped.
					// This uses little jQuery as well which is just gravy.
					var rect  = this.getBoundingClientRect();
					var right = (document.body.clientWidth - rect.right);
					
					if (rect.top <= 80 && right <= 40)
					{
						right = 10;
						this.style.top = 45 + "px";
					}
					
					this.style.height = "auto";
					this.style.left   = "auto";
					this.style.right  = right + "px";
				}
			},
			
			postKeyDown  : function(event) {
				if ((event.keyCode == 10 || event.keyCode == 13) && event.ctrlKey)
				{
					$(widget.options.selector['submit-post'], widget.$widget)
						.click();
					
					
					event.preventDefault();
					return false;
				}
			},
			
			postResize    : function(event, ui) {
				var $post = $(this);
				
				ui.position.top  = 0;
				ui.position.left = 0;
				
				var formHangY   = window.innerHeight - (widget.$widget.position().top + widget.$widget.outerHeight());
				ui.size.width   = Math.min(ui.size.width, widget.$widget.width());
				ui.size.height += Math.min(0, formHangY);
				
				widget.$widget.css({
					'height' : formHangY > 0 ? "auto" : window.innerHeight - widget.$widget.position().top
				});
				
				$post.css('width', ui.size.width);
				$post.children().first().css('width', "100%");
				
				return ui;
			},
			
			postResizeStart : function(event, ui) {
				var axis = $(this).data('ui-resizable').axis;
				
				if (widget.axis != axis)
				{
					var rect  = this.getBoundingClientRect();
					
					if (widget.axis == "sw")
					{
						widget.$widget[0].style.left  = rect.left + "px";
						widget.$widget[0].style.right = "auto";
					}
				}
			},
			
			postResizeStop  : function(event, ui) {
				var axis = $(this).data('ui-resizable').axis;
				
				if (widget.axis != axis)
				{
					var rect  = this.getBoundingClientRect();
					
					if (widget.axis == "sw")
					{
						var right = (document.body.clientWidth - rect.right);
						
						widget.$widget[0].style.left  = "auto";
						widget.$widget[0].style.right = right + "px";
					}
				}
			},
			
			spoilerChange : function(event) {
				var $this = $(this);
				var $next = $this.next();
				
				$this.next().attr('value', $this.prop('checked') ? 1 : 0);
			},
			
			windowResize  : function(event) {
				// For some pathetic reason, the jQery UI Resize widget uses the "resize"
				// event name, which is also an HTML default for window resizes. Events fired
				// also bubble up to the window, so this gets called when the post box resizes too.
				if (event.target === window)
				{
					widget.resizePostbox();
				}
			}
		},
		
		// Event bindings
		bind     : {
			counter   : function() {
				var $body    = $(widget.options.selector['form-body'], widget.$widget);
				var $counter = $(widget.options.template['counter']);
				
				$counter.insertAfter($body);
				widget.$counter = $counter;
				$body.trigger('change');
			},
			
			draggable : function() {
				if (window.innerHeight >= 480 && window.innerWidth >= 728)
				{
					widget.$widget.draggable({
						containment : "window",
						handle      : "legend.form-legend",
						stop        : widget.events.postDragStop
					});
					
					widget.draggable = true;
				}
			},
			
			resize    : function() {
				if (window.innerHeight >= 480 && window.innerWidth >= 728)
				{
					// Bind resizability onto the post area.
					var $body   = $(widget.options.selector['form-body'], widget.$widget);
					
					if (!widget.resizable && $body.length && typeof $body.resizable === "function")
					{
						$body.resizable({
							handles:     "sw,se",
							resize:      widget.events.postResize,
							start:       widget.events.postResizeStart,
							stop:        widget.events.postResizeStop,
							alsoResize:  widget.$widget,
							minWidth:    300,
							minHeight:   26
						});
						
						widget.$widget.resizable({
							handles:  null,
							minWidth: 300
						}).css({
							height: "auto"
						});
						
						widget.resizable = true;
					}
				}
			},
			
			widget    : function() {
				
				// Force the notices widget to be bound, and then record it.
				// We have to do this because the notices widget is a child within this widget.
				// The parent is bound first.
				widget.notices = window.ib.bindElement($(widget.options.selector['notices'])[0]);
				
				if (typeof window.Dropzone !== 'undefined')
				{
					var dropzoneOptions = jQuery.extend({}, widget.options.dropzone);
					$(widget.options.selector['dropzone'], widget.$widget).dropzone(dropzoneOptions);
				}
				
				$(window)
					.on('messenger.ib-postbox.', widget.events.messenger)
					.on('resize.ib-postbox',     widget.events.windowResize);
				
				// This will actually bind multiple times so make sure it only happens once.
				if (widget.initOnce !== true)
				{
					// Ensures window.app is current with dropzone stuff.
					InstantClick.on("change", widget.events.pageChange);
				}
				
				widget.$widget
					// Watch for key downs as to capture ctrl+enter submission.
					// We don't die this to any particular item.
					.on('keydown.ib-postbox', widget.events.postKeyDown)
					
					// Watch for form size clicks
					.on('click.ib-postbox',                                             widget.events.formClick)
					.on('click.ib-postbox', widget.options.selector['button-close'],    widget.events.closeClick)
					.on('click.ib-postbox', widget.options.selector['button-maximize'], widget.events.maximizeClick)
					.on('click.ib-postbox', widget.options.selector['button-minimize'], widget.events.minimizeClick)
					
					// Watch field changes
					.on('change.ib-postbox', widget.options.selector['form-body'],      widget.events.bodyChange)
					.on('keyup.ib-postbox',  widget.options.selector['form-body'],      widget.events.bodyChange)
					.on('change.ib-postbox', widget.options.selector['form-spoiler'],   widget.events.spoilerChange)
					
					// Watch form submission.
					.on('submit.ib-postbox',        widget.events.formSubmit)
					
					// Watch for file statuses.
					.on('fileFailed.ib-postbox',    widget.events.fileFailed)
					.on('fileCanceled.ib-postbox',  widget.events.fileCanceled)
					.on('fileUploaded.ib-postbox',  widget.events.fileUploaded)
					.on('fileUploading.ib-postbox', widget.events.fileUploading)
				;
				
				widget.bind.counter();
				widget.bind.draggable();
				widget.bind.resize();
			}
		},
		
		unbind   : {
			counter   : function() {
				if (widget.$counter && widget.$counter instanceof jQuery)
				{
					widget.$counter.remove();
				}
			},
			
			draggable : function() {
				if (widget.draggable && typeof widget.$widget.draggable === "function")
				{
					widget.$widget.draggable( "destroy" ).attr('style', "");
					
					widget.draggable = false;
				}
			},
			
			resize    : function() {
				// Bind resizability onto the post area.
				var $body   = $(widget.options.selector['form-body'], widget.$widget);
				
				if (widget.resizable && $body.length && typeof $body.resizable === "function")
				{
					$body.resizable( "destroy" ).attr('style', "");
					
					widget.$widget.resizable( "destroy" ).attr('style', "");
					
					widget.resizable = false;
				}
			}
		}
	};
	
	return widget;
});
