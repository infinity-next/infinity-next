/**
 * Widget Master
 */
(function(window, $, undefined) {
	var ib = window.ib = function() {};

	// Store existing Widgets.
	ib.widgets = {};

	// Setup directional logic
	ib.rtl = false;
	ib.ltr = !ib.rtl;

	// Binding Widgets to DOM.
	ib.bindAll = function(eventOrScope) {
		var $scope;

		if (typeof eventOrScope !== "undefined")
		{
			if (typeof eventOrScope.target !== "undefined")
			{
				$scope = $(eventOrScope.target);
			}
			else if (eventOrScope instanceof jQuery)
			{
				$scope = eventOrScope;
			}
			else if (eventOrScope instanceof HTMLElement)
			{
				$scope = $(eventOrScope);
			}
			else
			{
				$scope = $(document);
			}
		}
		else
		{
			$scope = $(document);
		}

		if ($scope instanceof jQuery)
		{
			if ($scope.is("[data-widget]")) {
				ib.bindElement($scope[0]);
			}

			$("[data-widget]", $scope).each(function() {
				ib.bindElement(this);
			});
		}
		else
		{
			console.log("ib.bindAll couldn't make sense of eventOrScope.", eventOrScope);
		}
	},

	ib.bindOnEvent = function(event) {
		return ib.bindElement(this);
	},

	ib.bindElement = function(dom) {
		var requestedWidget = dom.getAttribute('data-widget');

		if (ib.widgets[requestedWidget])
		{
			return ib.bindWidget(dom, ib.widgets[requestedWidget]);
		}
		else
		{
			console.log("Widget \""+requestedWidget+"\" does not exist.");
		}
	};

	ib.bindWidget = function(dom, widget) {
		if (typeof dom.widget === "undefined")
		{
			dom.widget = new widget(window, jQuery);
			dom.widget.initOnce = false;

			widget.instances.push(dom.widget);

			if (typeof dom.widget.init === "function")
			{
				dom.widget.init(dom);
			}
			else
			{
				window.ib.widgetArguments.call(dom.widget, [dom]);
			}

			dom.widget.initOnce = true;
		}

		return dom.widget;
	};

	// Handle config searching.
	// This is site config, not user settings.
	ib.config = function(name, configDefault) {
		if (typeof window.app !== "undefined" && typeof window.app[name] !== "undefined")
		{
			return window.app[name];
		}

		return configDefault;
	};

	// Translate a phrase.
	ib.trans = function(phrase) {
		// Split a 'x.y.z' string into ['x','y','z']
		var items = phrase instanceof Array ? phrase : phrase.split(".");
		// Point to the application language object-array.
		var traverse = window.app.lang;

		// For each item in our phrase key, traverse down.
		for (var i = 0; i < items.length; ++i)
		{
			traverse = traverse[items[i]];

			// If we've hit an undefined point, bail out with an empty string.
			if (traverse === undefined)
			{
				return "";
			}
		}

		// If we didn't come up with a string, bail out.
		if (typeof traverse !== "string")
		{
			return "";
		}

		return traverse;
	};

	// Lpad 5 into "05".
	ib.lpad = function(n, width, z) {
		z = z || '0';
		n = n + '';

		if (n.length >= width)
		{
			return n;
		}

		return new Array(width - n.length + 1).join(z) + n;
	};

	// Generates a random string with alternating case.
	ib.randomString = function(length) {
		length = length || 8;

		var text = "";
		var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" +
			"abcdefghijklmnopqrstuvwxyz" +
			"0123456789!@#$%^&*()";

		for (var i = 0; i < length; ++i)
		{
			text += possible.charAt(Math.floor(Math.random() * possible.length));
		}

		return text;
	};

	/**
	 * Options and Settings
	 */
	ib.option = function(widget, params) {
		var widget  = widget;
		var name    = params.name;
		var type    = params.type;
		var initial = params.initial;
		var values  = params.values;

		if (!this.validateWidget(widget)) {
			throw "ib.option :: widget \"" + widget + "\" not defined.";
		}
		if (!this.validateName(name)) {
			throw "ib.option :: name \"" + name + "\" not valid.";
		}
		if (!this.validateType(type)) {
			throw "ib.option :: type \"" + type + "\" not valid.";
		}

		var setting  = this;
		this.name    = name;
		this.initial = initial;
		this.storage = "ib.setting." + widget + "." + name;
		this.type    = type;
		this.values  = values;
		this.widget  = widget;

		// Synchronize widgets on update
		this.onUpdate(this.eventStorageUpdate);
	};

	ib.option.prototype.eventInputChanged = function(event) {
		var setting = event.data.setting;
		var value;

		switch (event.data.setting.type)
		{
			case 'bool':
				var checked = $(this).prop('checked');
				value = checked === "on" || checked == true ? 1 : 0;
				break;

			default:
				value = this.value;
				break;
		}

		// Enforce data integrity if we have a whitelist.
		if (setting.values instanceof Array
			&& setting.values.indexOf(value) < 0)
		{
			event.preventDefault();
			return false;
		}

		setting.set(value);
	};

	ib.option.prototype.eventStorageUpdate = function(event) {
		if (event.originalEvent.key === event.data.setting.storage)
		{
			var setting = event.data.setting;
			var $input  = $("#js-config-"+setting.widget+"-"+setting.name);

			switch (setting.type)
			{
				case 'bool':
					$input.prop('checked', setting.get() == "1");
					break;

				default:
					$input.val(setting.get());
					break;
			}
		}
	};

	ib.option.prototype.get = function() {
		var value = localStorage.getItem(this.storage);

		if ((typeof value === "undefined" || value === null) && this.initial)
		{
			value = this.initial;
		}

		switch (this.type)
		{
			case 'bool' :
				return value === "1" || value === 1 || value === true;
				break;
		}

		return value;
	};

	ib.option.prototype.getLabel = function() {
		return ib.trans(this.widget + ".option." + this.name);
	};

	ib.option.prototype.getName = function() {
		return this.name;
	};

	ib.option.prototype.getType = function() {
		return this.type;
	};

	ib.option.prototype.onUpdate = function(closure) {
		if (typeof closure !== "function") {
			throw "ib.option :: onUpdate not supplied a closure."
		}

		$(window).on('storage', { setting : this }, closure);
	};

	ib.option.prototype.set = function(value) {
		return localStorage.setItem(this.storage, value);
	};

	ib.option.prototype.setInitial = function(overwrite) {
		var value = localStorage.getItem(this.storage);
		var isUndefined = (typeof value === "undefined" || value === null);

		if (overwrite === true || (isUndefined && this.initial))
		{
			value = this.initial;
			localStorage.setItem(this.storage, value);
			console.log("option.setInitial force writing " + this.widget +
				"." + this.name + " to \"" + value + "\".");
		}

		return value;
	};

	ib.option.prototype.toHTML = function() {
		var $html;
		var value = this.get();

		switch (this.type)
		{
			case 'bool':
				$html = $("<input />");
				$html.attr('type', "checkbox");
				$html.prop('checked', value);
				value = 1;
				break;

			case 'int':
				$html = $("<input />");
				$html.attr('type', "number");
				break;

			case 'select':
				$html = $("<select></select>");

				for (var i = 0; i < this.values.length; ++i)
				{
					var option = this.values[i];
					option = "<option value=\""+option+"\">"+option+"</option>";
					$html.append(option);
				}
				break;

			case 'string':
			case 'text':
				$html = $("<input />");
				$html.attr('type', "text");
				break;

			case 'textarea':
				$html = $("<textarea></textarea>");
				break;

			default:
			//case 'array':
				$html = $("<span></span>")
				break;

		}

		$html.attr('id',    "js-config-"+this.widget+"-"+this.name);
		$html.attr('class', "config-option");
		$html.val(value);
		$html.on(
			'change',
			{ 'setting' : this },
			this.eventInputChanged
		);

		if (typeof this.eventCustomInputChanged === "function")
		{
			$html.on(
				'change',
				{ 'setting' : this },
				this.eventCustomInputChanged
			);
		}

		return $html;
	};

	ib.option.prototype.validateName = function(name) {
		return typeof name === "string" && name.length > 0;
	};

	ib.option.prototype.validateType = function(type) {
		switch (type)
		{
			case 'array'    :
			case 'bool'     :
			case 'int'      :
			case 'select'   :
			case 'string'   :
			case 'text'     :
			case 'textarea' :
				return true;
		}

		return false;
	};

	ib.option.prototype.validateWidget = function(widget) {
		return typeof ib.widgets[widget] === "function" || widget === "main";
	};

	ib.settings = {
		// 'main' : {
		// 	'widgets' : new ib.option("main", 'widgets', "bool")
		// }
	};


	// Setup new Widget class.
	ib.widget = function(name, widget, options) {
		console.log("Declaring widget \""+name+"\".");

		if (ib.widgets[name] !== undefined)
		{
			console.log("Trying to re-declare widget \""+name+"\".");
			return false;
		}

		widget.instances = [];
		widget.prototype.name = name;
		ib.widgets[name] = widget;

		if (typeof options === "object")
		{
			if (typeof ib.settings[name] === "undefined")
			{
				ib.settings[name] = {};
			}

			jQuery.each(options, function(optionName, optionParams)
			{
				var optionData = {
					widget : name,
					name : optionName,
					type : null,
					initial : null,
					onChange : null,
					onUpdate : null
				};

				if (typeof optionParams === "object")
				{
					optionData = jQuery.extend(true, optionData, optionParams);
				}
				else
				{
					optionData.type = optionParams;
				}

				// Declare new option instance.
				var option = new ib.option(name, optionData);

				ib.settings[name][optionName] = option;

				// On HTML input change
				if (typeof optionParams.onChange === "function")
				{
					option.eventCustomInputChanged = optionParams.onChange;
				}

				// On option change in another tab
				if (typeof optionParams.onUpdate === "function")
				{
					option.onUpdate(optionParams.onUpdate);
				}
			});
		}

		return true;
	};

	// Widget blueprint for instance extension.
	ib.blueprint = function() { };

	ib.blueprint.prototype.get = function(item) {
		// Does this widget have settings?
		if (typeof ib.settings[this.name] === "undefined")
		{
			return false;
		}

		// Does this setting instance our option prototype?
		if (!(ib.settings[this.name][item] instanceof ib.option))
		{
			return false;
		}

		return ib.settings[this.name][item].get();
	};

	ib.blueprint.prototype.is = function(item) {
		return !!this.get(item);
	};

	// This returns a non-referential copy of the blueprint for widget building.
	ib.getBlueprint = function() {
		var blueprint = function() { };
		blueprint.prototype = jQuery.extend(true, {}, ib.blueprint.prototype);
		return blueprint;
	}

	// Returns all instances of a widget as jQuery.
	ib.getInstances = function(widget) {
		var instances  = ib.widgets[widget].instances;
		var $instances = $();

		for (var i = 0; i < instances.length; ++i)
		{
			$instances = $instances.add(instances[i].$widget);
		}

		return $instances;
	};

	ib.widgetArguments  = function(args) {
		var widget  = this;
		var target  = args[0];
		var options = args[1];

		if (typeof options !== "object")
		{
			options = {};
		}

		widget.options = $.extend(true, options, widget.defaults);

		if (typeof target !== "string" && $(target).length === 0)
		{
			target = widget.options.selector.widget;
		}


		var $widget = widget.$widget = $(target).first();

		if ($widget.length)
		{
			if (typeof widget.can !== "function" || widget.can())
			{
				if (typeof widget.bind === "object")
				{
					try
					{
						widget.bind.widget.call(widget);
					}
					catch (error)
					{
						console.error("Failed to initiate v1 widget!", error);
					}
				}
				else if (typeof widget.bind === "function")
				{
					try
					{
						widget.bind();
					}
					catch (error)
					{
						console.error("Failed to initiate v2 widget!", error);
					}
				}

				return true;
			}
			else if (typeof widget.bind.failure === "function")
			{
				widget.bind.failure();
			}
		}

		return false;
	};

	// We handle widget binding in two ways, depending on browser support.
	if (typeof MutationObserver === "function")
	{
		// If the newer MutationObserver object exists, we can watch the DOM
		// for new elements and bind widgets immediately as they're conceived.
		ib.observeMutation = function(records) {
			// This method must be EXTREMELY FAST as it is called on every
			// dom mutation as it happens.
			ib.bindQueued();

			for (var x = 0; x < records.length; ++x)
			{
				var nodes = records[x].addedNodes;

				for (var y = 0; y < nodes.length; ++y)
				{
					var node = nodes[y];

					if (node.attributes && node.attributes['data-widget'])
					{
						ib.bindWhenLoaded(node);
					}
				}
			}

			if (document.readyState !== "loading")
			{
				ib.mutationObserver.disconnect();
			}
		};

		// Widgets not fully loaded are queued for binding at a later time.
		ib.queuedElements = [];

		ib.bindWhenLoaded = function(node) {
			if (ib.widgetLoading(node))
			{
				ib.queuedElements.push(node);
			}
			else
			{
				ib.bindElement(node);
			}
		};

		ib.widgetLoading = function(node) {
			if (document.readyState !== "loading")
			{
				return false;
			}

			while(!node.nextSibling && node.parentNode)
			{
				node = node.parentNode;
			}

			return !node.nextSibling || (node.parentNode === document.body);
		};

		ib.bindQueued = function() {
			var queue = ib.queuedElements;
			ib.queuedElements = [];

			for (var i = 0; i < queue.length; ++i)
			{
				ib.bindWhenLoaded(queue[i]);
			}
		};

		ib.mutationObserver = new MutationObserver(ib.observeMutation);
		ib.mutationObserver.observe(
			document.documentElement,
			{
				childList : true,
				subtree : true
			}
		);
		$(document).on('ready', ib.bindQueued);
	}
	else
	{
		// Otherwise, we must use jQuery after the document has loaded.
		$(document).on('ready', ib.bindAll);
	}

	return ib;
})(window, jQuery);

// ===========================================================================
// Purpose          : Posts
// Contributors     : jaw-sh
// Widget Version   : 2
// ===========================================================================

(function(window, $, undefined) {
	// Widget blueprint
	var blueprint = ib.getBlueprint();

	var options = {};

	blueprint.prototype.defaults = {
		// Important class names.
		classname : {
			'last-reply' : "thread-last-reply"
		},

		// Selectors for finding and binding elements.
		selector : {
			'enabled'        : ".autoupdater-enabled",
			'timer'          : ".autoupdater-timer",
			'force-update'   : ".autoupdater-update",
			'updating'       : ".autoupdater-updating",

			'cite'           : "a.cite-post",

			'thread-event-target' : ".thread:first",
			'thread-reply'        : ".thread-reply"
		},
	},

	// Update tracking
	blueprint.prototype.updating     = false;
	blueprint.prototype.updateTimer  = false;
	blueprint.prototype.updateURL    = false;
	blueprint.prototype.updateAsked  = false;
	blueprint.prototype.updateLast   = false;
	blueprint.prototype.updateMisses = 0;

	// Keeps track of what our last post was before we focused the window.
	blueprint.prototype.$lastPost    = null;
	blueprint.prototype.hasFocus     = false;
	blueprint.prototype.newReplies   = 0;

	// Keeps track of if we're scrolled to the bottom.
	blueprint.prototype.scrollLocked = false;

	// Returns the UNIX timestamp of our last received post.
	blueprint.prototype.getTimeFromPost = function($post) {
		var $container = $post;

		if (!$container.is(".post-container"))
		{
			$container = $post.find(".post-container:first");
		}

		if (!$container.length)
		{
			return false;
		}


		var times = [0];

		if ($container.is("[data-created-at]"))
		{
			var createdAt = parseInt($container.attr('data-created-at'), 10);

			if (!isNaN(createdAt))
			{
				times.push(createdAt);
			}
		}

		if ($container.is("[data-deleted-at]"))
		{
			var deletedAt = parseInt($container.attr('data-deleted-at'), 10);

			if (!isNaN(deletedAt))
			{
				times.push(deletedAt);
			}
		}

		if ($container.is("[data-updated-at]"))
		{
			var updatedAt = parseInt($container.attr('data-updated-at'), 10);

			if (!isNaN(updatedAt))
			{
				times.push(updatedAt);
			}
		}

		return Math.max.apply(Math, times);
	};

	blueprint.prototype.addYouPost = function(uri, id) {
		if (typeof window.localStorage !== "object")
		{
			return [];
		}

		try
		{
			var storage = localStorage.getItem("yourPosts."+uri).split(",");
		}
		catch (e)
		{
			var storage = [];
		}

		storage.push(id);
		storage = storage.filter(function(index, item, array) {
			return array.lastIndexOf(index) === item;
		});

		localStorage.setItem("yourPosts."+uri, storage.join(","));
	};

	blueprint.prototype.bind = function() {
		var widget  = this;
		var $widget = this.$widget;
		var data    = {
			widget  : widget,
			$widget : $widget
		};

		$(widget.options.selector['force-update'])
			.show();

		$(widget.options.selector['updating'])
			.hide();

		$(window)
			.on(
				'scroll.ib-au',
				data,
				widget.events.windowScroll
			)
			.on(
				'focus.ib-au',
				data,
				widget.events.windowFocus
			)
			.on(
				'blur.ib-au',
				data,
				widget.events.windowUnfocus
			)
		;

		$widget
			.on(
				'click.ib-au',
				widget.options.selector['force-update'],
				data,
				widget.events.updaterUpdateClick
			)
			.on(
				'au-update.ib-au',
				data,
				widget.events.update
			)
		;

		widget.bindTimer();
	};

	blueprint.prototype.bindTimer = function() {
		var widget       = this;
		var $widget      = this.$widget;
		var $lastReply   = $widget.prev();
		widget.$lastPost = $lastReply;

		if (!$lastReply.length)
		{
			// Select OP if we have no replies.
			$lastReply = $widget.parents( widget.options.selector['thread-event-target'] );

			// We don't want OP to be our last post.
			widget.$lastPost = null;
		}

		widget.updateLast = widget.getTimeFromPost($lastReply);
		widget.hasFocus   = document.hasFocus();

		var url   = $widget.data('url');

		if (url)
		{
			widget.updateURL = url;
			widget.$widget.show();

			clearInterval(widget.updateTimer);
			widget.updateTimer = setInterval(function() {
				widget.updateInterval.apply(widget);
			}, 1000);
		}
	};

	// Events
	blueprint.prototype.events = {
		update : function(event) {
			// Calls the widget's update function with a contextual this.
			event.data.widget.update.call(event.data.widget);
		},

		updateComplete : function(json, textStatus, jqXHR) {
			var widget = jqXHR.widget;

			widget.updating = false;

			$(widget.options.selector['force-update'])
				.show();
			$(widget.options.selector['updating'])
				.hide();

			clearInterval(widget.updateTimer);
			widget.updateTimer = setInterval(function() {
				widget.updateInterval.apply(widget);
			}, 1000);
		},

		updateSuccess : function(json, textStatus, jqXHR, scrollIntoView) {
			var widget       = jqXHR.widget;
			var $widget      = widget.$widget;
			var newPosts     = $();
			var updatedPosts = $();
			var deletedPosts = $();
			var postData     = json;

			// This important event fire ensures that sibling data is intercepted.
			if (json.messenger)
			{
				postData = json.data;
				$(window).trigger('messenger', json);
			}

			if (postData instanceof Array)
			{
				$.each(postData, function(index, reply)
				{
					var $existingPost = $(".post-container[data-post_id=" + reply.post_id+"]");

					if ($existingPost.length > 0)
					{
						if (reply.html !== null)
						{
							$newPost      = $(reply.html);

							var existingUpdated = parseInt($existingPost.attr('data-updated-at'), 10),
								newUpdated      = parseInt($newPost.attr('data-updated-at'), 10);

							if (isNaN(existingUpdated) || isNaN(newUpdated) || (newUpdated > existingUpdated))
							{
								console.log("Autoupdater: Replacing " + reply.post_id);

								$existingPost.replaceWith($newPost);
								ib.bindAll($newPost[0]);

								updatedPosts.push($newPost);

								widget.updateLast = Math.max(widget.updateLast, widget.getTimeFromPost($newPost));

								return true;
							}
						}
						else
						{
							console.log("Autoupdater: Deleting " + reply.post_id);

							$existingPost.addClass('post-deleted');

							updatedPosts.push($existingPost);
							deletedPosts.push($existingPost);

							widget.updateLast = Math.max(widget.updateLast, widget.getTimeFromPost($existingPost));

							return true;
						}
					}
					else if (reply.html !== null)
					{
						console.log("Autoupdater: Inserting " + reply.post_id);

						$newPost = $(reply.html);

						var $li = $("<li class=\"thread-reply\"></li>");
						var $article = $("<article class=\"reply\"></article>");

						// Insertion has to be done very carefully so we have a
						// valid reference for $newPost.
						$article.append($newPost);
						$li.append($article);
						$li.insertBefore($widget);

						widget.updateLast = Math.max(widget.updateLast, widget.getTimeFromPost($newPost));

						// Push this ID into our You lists if we made it.
						if (reply.recently_created)
						{
							widget.addYouPost(reply.board_uri, reply.board_id);
						}

						// Used primarily by postbox updates to force the scroll to see our new post.
						if (scrollIntoView === true)
						{
							if (typeof $newPost[0].scrollIntoViewIfNeeded !== "undefined")
							{
								$newPost[0].scrollIntoViewIfNeeded({
									behavior : "smooth",
									block    : "end"
								});
							}
							else if (typeof $newPost[0].scrollIntoView !== "undefined")
							{
								$newPost[0].scrollIntoView({
									behavior : "smooth",
									block    : "end"
								});
							}
						}

						ib.bindAll($newPost);
						newPosts.push($newPost);

						return true;
					}
				});
			}

			if (newPosts.length)
			{
				if (!widget.hasFocus)
				{
					widget.newReplies += newPosts.length;
				}
				else if (widget.scrollLocked)
				{
					window.scrollTo(0, document.body.scrollHeight);
				}

				widget.updateMisses = 0;
			}
			else
			{
				++widget.updateMisses;
			}

			$(window).trigger('au-updated', [{
				'newPosts'     : newPosts,
				'updatedPosts' : updatedPosts,
				'deletedPosts' : deletedPosts,
			}]);

			widget.updateLastReply();

			return false;
		},

		updaterUpdateClick : function(event) {
			var widget  = event.data.widget;
			var $widget = event.data.$widget;
			var $timer  = $(widget.options.selector['timer'], $widget);

			widget.updateMisses = 0;
			$timer.attr('data-time', 5);
			widget.update();

			event.preventDefault();
			return false;
		},

		windowFocus : function(event) {
			var widget  = event.data.widget;
			var $widget = event.data.$widget;

			widget.hasFocus   = true;
			widget.$lastPost  = null;
			widget.newReplies = 0;

			document.title = $('<div/>').html(window.app.title).text();
			$("#favicon").attr('href', window.app.favicon.normal);
		},

		windowUnfocus : function(event) {
			var widget  = event.data.widget;
			var $widget = event.data.$widget;

			// Sets our last seen post to the post immediately above the widget.
			widget.$lastPost    = widget.$widget.prev();
			widget.$lastPost    = widget.$lastPost.length ? widget.$lastPost : null;
			widget.hasFocus     = false;
			widget.scrollLocked = false;
		},

		windowScroll : function(event) {
			var widget  = event.data.widget;
			var $widget = event.data.$widget;
			var $window = $(window);

			// Determine if the boundaries of this image are in the viewport.

			var docViewTop    = $window.scrollTop();
			var docViewBottom = docViewTop + $window.height();

			var viewPad = 16;

			var elemTop       = $widget.offset().top - viewPad;
			var elemBottom    = elemTop + $widget.height() + viewPad;

			// We don't need the entire image to be present, just either edge.

			// If the top boundary is present
			widget.scrollLocked = (elemBottom <= docViewBottom) && (elemBottom >= docViewTop);
		}
	};

	// Main update trigger.
	blueprint.prototype.update = function() {
		var widget  = this;
		var $widget = this.widget;

		if (!widget.updating)
		{
			$(widget.options.selector['force-update'])
				.hide();

			$(widget.options.selector['updating'])
				.show();

			clearInterval(widget.updateTimer);

			var jqXHR = $.ajax(widget.updateURL, {
				data : {
					'updatesOnly'  : 1,
					'updateHtml'   : 1,
					'updatedSince' : widget.updateLast,
					'messenger'    : 1
				}
			});

			jqXHR.widget = widget;
			jqXHR.done(widget.events.updateSuccess)
			jqXHR.always(widget.events.updateComplete);

			widget.updating    = true;
			widget.updateTimer = false;
			widget.updateAsked = parseInt(parseInt(Date.now(), 10) / 1000, 10);
		}
	};

	blueprint.prototype.updateInterval = function() {
		var widget  = this;
		var $widget = this.$widget;
		clearInterval(widget.updateTimer);

		if ($(widget.options.selector['enabled']).is(":checked"))
		{
			var $timer = $(widget.options.selector['timer'], widget.$widget);
			var time   = parseInt($timer.attr('data-time'), 10);

			if (isNaN(time))
			{
				time = 0;
			}

			--time;

			if (time <= 0)
			{
				time = (widget.hasFocus ? widget.updateMisses * 2 : Math.pow(widget.updateMisses, 1.5)) + 3;
				time = parseInt( Math.min(time, 30), 10);

				$widget.trigger('au-update');
			}

			$timer
				.text(time+'s')
				.attr('data-time', time);
		}

		widget.updateTimer = setInterval(function() {
			widget.updateInterval.apply(widget);
		}, 1000);
	};

	blueprint.prototype.updateLastReply = function() {
		var widget  = this;
		var $widget = this.$widget;

		// This corrects which post has the last reply class.
		if (widget.$lastPost !== null)
		{
			widget.$widget
				.siblings("." + widget.options.classname['last-reply'])
				.removeClass(widget.options.classname['last-reply']);

			// If we have replies after this, add the border.
			if (widget.$lastPost.next(widget.options.selector['thread-reply']).length)
			{
				widget.$lastPost
					.addClass(widget.options.classname['last-reply']);
			}
		}

		// This corrects our favicon.
		if (widget.newReplies > 0)
		{
			$("#favicon").attr('href', window.app.favicon.alert);
			document.title = "(" + widget.newReplies + ") " + $('<div/>').html(window.app.title).text();
		}
		else
		{
			$("#favicon").attr('href', window.app.favicon.normal);
			document.title = $('<div/>').html(window.app.title).text();
		}
	};

	ib.widget("autoupdater", blueprint, options);
})(window, window.jQuery);

// ============================================================
// Purpose                      : Board Favorites
// Contributors                 : jaw-sh
// ============================================================

ib.widget("board-favorite", function(window, $, undefined) {
	var widget = {
		
		can :  function() {
			var ls    = typeof localStorage === "object";
			var board = widget.$widget.attr('data-board');
			
			return ls && board;
		},
		
		favorites : function() {
			var storageItem = localStorage.getItem(widget.options.storage.favorites);
			
			if (typeof storageItem === "string")
			{
				return storageItem.split(",");
			}
			
			return [];
		},
		
		favoriteThis : function( addFavorite ) {
			var boards = widget.favorites();
			
			if (addFavorite)
			{
				boards.push(widget.board);
				boards = jQuery.unique(boards);
			}
			else
			{
				boards = jQuery.grep(boards, function(value) {
					return value != widget.board;
				});
			}
			
			localStorage.setItem(widget.options.storage.favorites, boards);
		},
		
		defaults : {
			classname : {
				'favorited' : "board-favorited"
			},
			
			storage   : {
				'favorites'      : "ib.favorites",
				'favorites-data' : "ib.favoritedata"
			},
			
			selector  : {
				'favorites-menu' : "#favorite-boards"
			}
		},
		
		// Events
		events   : {
			
			favoriteChange : function(event) {
				var favorites = widget.favorites();
				
				for (var i = 0; i < favorites.length; ++i)
				{
					if (widget.board == favorites[i])
					{
						widget.$widget.addClass(widget.options.classname.favorited);
						return true;
					}
				}
				
				widget.$widget.removeClass(widget.options.classname.favorited);
				return false;
			},
			
			favoriteClick : function(event) {
				widget.favoriteThis( !widget.$widget.hasClass(widget.options.classname.favorited) );
				widget.events.favoriteChange.call(this, event);
				widget.events.favoriteUpdate.call(this, event);
			},
			
			favoriteUpdate : function(event) {
				$.get(window.app.url+"/board-details.json", {
					'boards' : widget.favorites()
				}).done(function(response) {
					
					localStorage.setItem(
						widget.options.storage['favorites-data'],
						JSON.stringify(response)
					);
					
					$(widget.options.selector['favorites-menu'])
						.trigger('build');
					
				});
			},
			
			// This is an HTML localStorage event.
			// it only fires if ANOTHER WINDOW trips the change.
			storage : function(event) {
				if (event.originalEvent.key == widget.options.storage.favorites)
				{
					widget.events.favoriteChange();
				}
			}
			
		},
		
		// Event bindings
		bind     : {
			failure : function() {
				widget.$widget.hide();
			},
			
			widget  : function() {
				widget.board = widget.$widget.attr('data-board');
				
				widget.$widget
					.css('display', 'inline-block')
					.on( 'click.ib-board-favorite', widget.events.favoriteClick )
				;
				
				$(window)
					.on( 'storage.ib-board-favorite', widget.events.storage )
				;
				
				widget.events.favoriteChange();
			}
		}
		
	};
	
	return widget;
});


// ============================================================
// Purpose                      : Board directory handling
// Contributors                 : jaw-sh
// ============================================================

ib.widget("boardlist", function(window, $, undefined) {
	var widget = {
		
		// The default values that are set behind init values.
		defaults : {
			
			searchUrl  : "/boards.html",
			
			// Selectors for finding and binding elements.
			selector   : {
				'board-head'    : ".board-list-head",
				'board-body'    : ".board-list-tbody",
				'board-loading' : ".board-list-loading",
				'board-omitted' : ".board-list-omitted",
				
				'search'        : "#search-form",
				'search-lang'   : "#search-lang-input",
				'search-sfw'    : "#search-sfw-input",
				'search-tag'    : "#search-tag-input",
				'search-title'  : "#search-title-input",
				'search-submit' : "#search-submit",
				
				'tag-list'      : ".tag-list",
				'tag-link'      : ".tag-link",
				
				'sortable'      : "th.sortable",
				
				'footer-page'   : ".board-page-num",
				'footer-count'  : ".board-page-count",
				'footer-total'  : ".board-page-total",
				'footer-more'   : "#board-list-more"
			},
			
			// HTML Templates for dynamic construction
			templates   : {
				// Board row item
				'board-row'          : "<tr></tr>",
				
				// Individual cell definitions
				'board-cell-meta'    : "<td class=\"board-meta\"></td>",
				'board-cell-uri'     : "<td class=\"board-uri\"></td>",
				'board-cell-title'   : "<td class=\"board-title\"></td>",
				'board-cell-stats_pph'          : "<td class=\"board-pph\"></td>",
				'board-cell-stats_ppd'          : "<td class=\"board-ppd\"></td>",
				'board-cell-stats_plh'          : "<td class=\"board-plh\"></td>",
				'board-cell-stats_active_users' : "<td class=\"board-unique\"></td>",
				'board-cell-posts_total'        : "<td class=\"board-max\"></td>",
				'board-cell-active'  : "<td class=\"board-unique\"></td>",
				'board-cell-tags'    : "<td class=\"board-tags\"></td>",
				
				// Content wrapper
				// Used to help constrain contents to their <td>.
				'board-content-wrap' : "<p class=\"board-cell\"></p>",
				
				// Individual items or parts of a single table cell.
				'board-datum-fav'    : "<i class=\"board-favorite fa fa-star\" data-widget=\"board-favorite\"></i>",
				'board-datum-lang'   : "<span class=\"board-lang\"></span>",
				'board-datum-uri'    : "<a class=\"board-link\"></a>",
				'board-datum-sfw'    : "<i class=\"fa fa-briefcase board-sfw\" title=\"SFW\"></i>",
				'board-datum-nsfw'   : "<i class=\"fa fa-briefcase board-nsfw\" title=\"NSFW\"></i>",
				'board-datum-tags'   : "<a class=\"tag-link\" href=\"#\"></a>",
				
				
				// Tag list.
				'tag-list'           : "<ul class=\"tag-list\"></ul>",
				'tag-item'           : "<li class=\"tag-item\"></li>",
				'tag-link'           : "<a class=\"tag-link\" href=\"#\"></a>"
			}
		},
		
		lastSearch : {},
		
		bind : {
			form : function() {
				var selectors = widget.options.selector;
				
				var $search       = $( selectors['search'] );
				var $searchLang   = $( selectors['search-lang'] );
				var $searchSfw    = $( selectors['search-sfw'] );
				var $searchTag    = $( selectors['search-tag'] );
				var $searchTitle  = $( selectors['search-title'] );
				var $searchSubmit = $( selectors['search-submit'] );
				
				var searchForms   = {
						'boardlist'    : widget.$widget,
						'search'       : $search,
						'searchLang'   : $searchLang,
						'searchSfw'    : $searchSfw,
						'searchTag'    : $searchTag,
						'searchTitle'  : $searchTitle,
						'searchSubmit' : $searchSubmit
					};
				
				if ($search.length > 0)
				{
					// Bind form events.
					widget.$widget
						// Sort column
						.on( 'click',  selectors['sortable'], searchForms, widget.events.sortClick )
						// Load more
						.on( 'click',  selectors['board-omitted'], searchForms, widget.events.loadMore )
						// Tag click
						.on( 'click',  selectors['tag-link'], searchForms, widget.events.tagClick )
						// Form Submission
						.on( 'submit', selectors['search'], searchForms, widget.events.searchSubmit )
						// Submit click
						.on( 'click',  selectors['search-submit'], searchForms, widget.events.searchSubmit );
					
					$(window)
						.on( 'hashchange', searchForms, widget.events.hashChange );
					
					$searchSubmit.prop( 'disabled', false );
				}
			},
			
			widget : function() {
				
				// Parse ?GET parameters into lastSearch object.
				if (window.location.search != "" && window.location.search.length > 0)
				{
					// ?a=1&b=2 -> a=1&b=2 -> { a : 1, b : 2 }
					window.location.search.substr(1).split("&").forEach( function(item) {
						widget.lastSearch[item.split("=")[0]] = item.split("=")[1];
					} );
				}
				
				$( widget.options.selector['board-loading'], widget.$widget ).hide();
				
				widget.bind.form();
				
				if (window.location.hash != "")
				{
					$(window).trigger( 'hashchange' );
				}
			}
		},
		
		build  : {
			boardlist : function(data) {
				widget.build.boards(data['boards']);
				widget.build.lastSearch(data['search']);
				widget.build.footer(data);
				widget.build.tags(data['tagWeight']);
			},
			
			boards : function(boards) {
				// Find our head, columns, and body.
				var $head = $( widget.options.selector['board-head'], widget.$widget );
				var $cols = $("[data-column]", $head );
				var $body = $( widget.options.selector['board-body'], widget.$widget );
				
				$.each( boards, function( index, board ) {
					var row  = board;
					var $row = $( widget.options.templates['board-row'] );
					
					$cols.each( function( index, col ) {
						widget.build.board( row, col ).appendTo( $row );
					} );
					
					ib.bindAll( $row.appendTo( $body ) );
				} );
				
			},
			
			board : function(row, col) {
				var $col   = $(col);
				var column = $col.attr('data-column');
				var value  = row[column];
				var $cell  = $( widget.options.templates['board-cell-' + column] );
				var $wrap  = $( widget.options.templates['board-content-wrap'] );
				
				if (typeof widget.build.boardcell[column] === "undefined")
				{
					if (value instanceof Array)
					{
						if (typeof widget.options.templates['board-datum-' + column] !== "undefined")
						{
							$.each( value, function( index, singleValue )
							{
								$( widget.options.templates['board-datum-' + column] )
									.text( singleValue )
									.appendTo( $wrap );
							} );
						}
						else
						{
							$wrap.text( value.join(" ") );
						}
					}
					else
					{
						$wrap.text( value );
					}
				}
				else
				{
					var $content = widget.build.boardcell[column]( row, value );
					
					if ($content instanceof jQuery)
					{
						if ($content.is("." + $wrap[0].class)) {
							// Our new content has the same classes as the wrapper.
							// Replace the old wrapper.
							$wrap = $content;
						}
						else
						{
							// We use .append() instead of .appendTo() as we do elsewhere
							// because $content can be multiple elements.
							$wrap.append( $content );
						}
					}
					else if (typeof $content === "string")
					{
						$wrap.html( $content );
					}
					else
					{
						console.log("Special cell constructor returned a " + (typeof $content) + " that board-directory.js cannot interpret.");
					}
				}
				
				$wrap.appendTo( $cell );
				return $cell;
			},
			
			boardcell : {
				'meta' : function(row, value) {
					return $( widget.options.templates['board-datum-lang'] ).text( row['locale'] );
				},
				
				'uri'  : function(row, value) {
					var $fav  = $( widget.options.templates['board-datum-fav'] );
					var $link = $( widget.options.templates['board-datum-uri'] );
					var $sfw  = $( widget.options.templates['board-datum-' + (row['is_worksafe'] == 1 ? "sfw" : "nsfw")] );
					
					$fav
						.attr( 'data-board', row['board_uri'] );
					
					$link
						.attr( 'href', window.app.url + "/" + row['board_uri'] + "/" )
						.text( "/"+row['board_uri']+"/" );
					
					// I decided against NSFW icons because it clutters the index.
					// Blue briefcase = SFW. No briefcase = NSFW. Seems better.
					return $fav[0].outerHTML + $link[0].outerHTML + (row['is_worksafe'] == 1 ? $sfw[0].outerHTML : "");
				},
				
				'active' : function(row, value) {
					return $( widget.options.templates['board-datum-pph'] )
						.attr( 'title', function(index, value) {
							return value.replace("%1", row['stats_pph']).replace("%2", row['pph_average']);
						} )
						.text( row['stats_pph'] );
				},
				
				'tags'  : function(row, value) {
					var $datum = $( widget.options.templates['board-datum-tags'] )
					
					$.each( value, function( index, singleValue )
					{
						$( widget.options.templates['board-datum-tags'] )
							.text( singleValue.tag )
							.appendTo( $datum );
					} );
					
					return $datum;
				}
			},
			
			lastSearch : function(search) {
				
				return widget.lastSearch = { 
					'lang'  : search.lang === false ? "" : search.lang,
					'page'  : search.page,
					'tags'  : search.tags === false ? "" : search.tags.join(" "),
					'time'  : search.time,
					'title' : search.title === false ? "" : search.title,
					'sfw'   : search.sfw ? 1 : 0,
					
					'sort'   : search.sort ? search.sort : null,
					'sortBy' : search.sortBy == "asc" ? "asc" : "desc"
				};
			},
			
			footer : function(data) {
				var selector = widget.options.selector;
				var $page    = $( selector['footer-page'], widget.$widget );
				var $count   = $( selector['footer-count'], widget.$widget );
				var $total   = $( selector['footer-total'], widget.$widget );
				var $more    = $( selector['footer-more'], widget.$widget );
				var $omitted = $( selector['board-omitted'], widget.$widget );
				
				var count    = (data['current_page'] * data['per_page']);
				var total    = data['total'];
				var omitted  = data['omitted'];
				
				//$page.text( data['search']['page'] * data['per_page']);
				$count.text( data['current_page'] * data['per_page'] );
				$total.text( total );
				$more.toggleClass( "board-list-hasmore", omitted > 0 );
				$omitted.toggle( omitted > 0 );
				$omitted.attr('data-page', data['page']);
			},
			
			tags : function(tags) {
				var selector = widget.options.selector;
				var template = widget.options.template;
				var $list    = $( selector['tag-list'], widget.$widget );
				
				if ($list.length && tags instanceof Object)
				{
					$.each( tags, function(tag, weight) {
						var $item = $( template['tag-item'] );
						var $link = $( template['tag-link'] );
						
						$link
							.css( 'font-size', weight+"%" )
							.text( tag.tag )
							.appendTo( $item );
						
						$item.appendTo( $list );
					} );
				}
			}
		},
		
		events : {
			sortClick : function(event) {
				event.preventDefault();
				
				var $th       = $(this);
				var sort       = $th.attr('data-column');
				var sortBy     = "desc";
				var parameters = $.extend( {}, widget.lastSearch );
				
				if ($th.hasClass("sorting-by-desc")) {
					sortBy = "asc";
				}
				else if ($th.hasClass("sorting-by-asc")) {
					sort   = false;
					sortBy = false;
				}
				
				$( widget.options.selector['tag-list'], widget.$widget ).html("");
				$( widget.options.selector['board-body'], widget.$widget ).html("");
				
				$(".sorting-by-asc, .sorting-by-desc")
					.removeClass("sorting-by-asc sorting-by-desc");
				
				$th.toggleClass("sorting-by-desc", sortBy == "desc");
				$th.toggleClass("sorting-by-asc",  sortBy == "asc");
				
				parameters.page   = 1;
				parameters.sort   = sort;
				parameters.sortBy = sortBy;
				
				if (sort === false || sortBy === false)
				{
					delete parameters.sort;
					delete parameters.sortBy;
				}
				
				widget.submit( parameters );
				
				return false;
			},
			
			loadMore : function(event) {
				event.preventDefault();
				
				var parameters = $.extend( {}, widget.lastSearch );
				
				parameters.page = parseInt(parameters.page, 10);
				
				if (isNaN(parameters.page))
				{
					parameters.page = 1;
				}
				
				++parameters.page;
				
				if (parameters.page === 1)
				{
					++parameters.page;
				}
				
				widget.submit( parameters );
				
				return false;
			},
			
			hashChange : function(event) {
				if (window.location.hash != "")
				{
					// Turns "#porn,tits" into "porn tits" for easier search results.
					var tags = window.location.hash.substr(1, window.location.hash.length).split(",");
					var hash = tags.join(" ");
				}
				else
				{
					var tags = [];
					var hash = "";
				}
				
				$( widget.options.selector['search-tag'], widget.$widget ).val( hash );
				$( widget.options.selector['tag-list'], widget.$widget ).html("");
				$( widget.options.selector['board-body'], widget.$widget ).html("");
				
				widget.submit( { 'tags' : tags } );
				
				return true;
			},
			
			searchSubmit : function(event) {
				event.preventDefault();
				
				$( widget.options.selector['tag-list'], widget.$widget ).html("");
				$( widget.options.selector['board-body'], widget.$widget ).html("");
				
				widget.submit( { 
					'lang'  : event.data.searchLang.val(),
					'tags'  : event.data.searchTag.val(),
					'title' : event.data.searchTitle.val(),
					'sfw'   : event.data.searchSfw.prop('checked') ? 1 : 0
				} );
				
				return false;
			},
			
			tagClick : function(event) {
				event.preventDefault();
				
				var $this  = $(this),
					$input = $( widget.options.selector['search-tag'] );
				
				$input
					.val( ( $input.val() + " " + $this.text() ).replace(/\s+/g, " ").trim() )
					.trigger( 'change' )
					.focus();
				
				return false;
			}
		},
		
		submit : function( data ) {
			var $boardlist    = widget.$widget;
			var $boardload    = $( widget.options.selector['board-loading'], $boardlist );
			var $searchSubmit = $( widget.options.selector['search-submit'], $boardlist );
			var $footerMore   = $( widget.options.selector['board-omitted'], $boardlist );
			
			$searchSubmit.prop( 'disabled', true );
			$boardload.css('display', 'table-row');
			$footerMore.hide();
			
			return jQuery.ajax({
					type:        "GET",
					method:      "GET",
					url:         widget.options.searchUrl,
					data:        data,
					dataType:    "json",
					contentType: "application/json; charset=utf-8"
				})
				.done(function(data) {
					$searchSubmit.prop( 'disabled', false );
					$boardload.hide();
					
					widget.build.boardlist( data );
				});
		}
	};
	
	return widget;
} );
/**
 * Captcha widget
 */
ib.widget("captcha", function(window, $, undefined) {
	var widget = {
		
		// The default values that are set behind init values.
		defaults : {
			
			captchaUrl    : "/cp/captcha",
			reloadUrl     : "/cp/captcha/replace.json",
			
			// Selectors for finding and binding elements.
			selector : {
				'captcha'         : ".captcha",
			}
		},
		
		// Events
		events   : {
			captchaAjaxFail : function(jqXHR, textStatus, errorThrown) {
				if (jqXHR.status == 429) {
					// Retry again in a second.
					setTimeout(function() {
						widget.events.captchaReload();
					}, 1000);
				}
			},
			
			captchaLoad : function(event) {
				var $captcha = $(this),
					$parent  = $captcha.parent();
				
				$parent.removeClass("captcha-loading");
			},
			
			captchaLoadIn : function(event, captcha) {
				var $captcha = $(widget.options.selector['captcha'], widget.$widget),
					$hidden  = $captcha.next();
				
				$captcha.attr('src', widget.options.captchaUrl + "/" + captcha['hash_string'] + ".png");
				$hidden.val(captcha['hash_string']);
			},
			
			captchaReload : function() {
				var $captcha = $(widget.options.selector['captcha'], widget.$widget),
					$parent  = $captcha.parent(),
					$field   = $captcha.parent().children("input");
				
				$parent.addClass("captcha-loading");
				$field.val("").focus();
				
				jQuery.getJSON(widget.options.reloadUrl, function(data) {
					widget.$widget.trigger('load', data);
				})
				.fail(widget.events.captchaAjaxFail);
			}
		},
		
		// Event bindings
		bind     : {
			widget : function() {
				
				$(widget.options.selector['captcha'])
					// Load events cannot be tied on parents.
					// Watch for source changes on the captcha.
					.on('load.ip-captcha', widget.events.captchaLoad);
				
				widget.$widget
					// Watch for captcha clicks.
					.on('load.ib-captcha',                                       widget.events.captchaLoadIn)
					.on('reload.ib-captcha',                                     widget.events.captchaReload)
					.on('click.ib-captcha',  widget.options.selector['captcha'], widget.events.captchaReload);
				
			}
		}
		
	};
	
	return widget;
});

// ============================================================
// Purpose                      : Config Sheets
// Contributors                 : jaw-sh
// ============================================================

ib.widget("config", function(window, $, undefined) {
	var widget = {
		
		defaults : {
			selector : {
				'field'         : ".field-control",
				
				'list-template' : ".option-list .option-item-template .field-control",
			}
		},
		
		// Events
		events   : {
			
			// Adds additional config list items on demand.
			listTemplateChange : function(event) {
				var $template = $(this);
				var $oldItem  = $template.parent();
				var $newItem  = $oldItem.clone();
				
				$oldItem.removeClass("option-item-template");
				$newItem.hide().insertAfter($oldItem).fadeIn(250);
				$newItem.children( widget.options.selector['field'] ).val("");
			}
			
		},
		
		// Event bindings
		bind     : {
			widget : function() {
				
				widget.$widget
					.on('keydown', widget.options.selector['list-template'], widget.events.listTemplateChange)
				;
				
			}
		}
		
	};
	
	return widget;
});

// ===========================================================================
// Purpose          : Content
// Contributors     : jaw-sh
// Widget Version   : 2
// ===========================================================================

(function(window, $, undefined) {
    // Widget blueprint
    var blueprint = ib.getBlueprint();

    // Content events
    var events = {
        doContentUpdate : function(event) {
            blueprint.prototype.adjustDisplay(event.data.setting.get());
        }
    };

    // Configuration options
    var options = {
        sfw : {
            type : "bool",
            initial : true,
            onChange : events.doContentUpdate,
            onUpdate : events.doContentUpdate
        }
    };

    blueprint.prototype.adjustDisplay = function(sfw) {
        var widget  = this;

        var sfw = widget.is('sfw');
        $("body").toggleClass('sfw-filter', sfw);

        // var $pageStylesheet = $(widget.defaults.selector['page-stylesheet']);
        // $pageStylesheet.attr('href', sfw
        //     ? $pageStylesheet.data('empty')
        //     : widget.defaults.nsfw_skin
        // );
    };

    // Event bindings
    blueprint.prototype.bind = function() {
        var widget  = this;
        var $widget = this.$widget;
        var data    = {
            widget  : widget,
            $widget : $widget
        };

        widget.adjustDisplay(widget.is('sfw'));
    };

    blueprint.prototype.defaults = {
        nsfw_skin : "/static/css/skins/next-yotsuba.css",

        selector : {
            'page-stylesheet' : "#page-stylesheet"
        }
    };

    blueprint.prototype.events = {
    };

    ib.widget("content", blueprint, options);
})(window, window.jQuery);

/**
 * Stripe Cashier Form
 */
ib.widget("donate", function(window, $, undefined) {
	var widget = {
		
		notices  : null,
		
		// The default values that are set behind init values.
		defaults : {
			// Config options for this widget.
			config   : {
				'merchant'      : window.ib.config('merchant', false),
				'stripe-key'    : window.ib.config('stripe_key', false),
				'braintree-key' : window.ib.config('stripe_key', false)
			},
			
			// Selectors for finding and binding elements.
			selector : {
				'widget'             : "#payment-form",
				'notices'            : "[data-widget=notice]:first",
				
				'time'               : "#payment-time",
				
				'input-ccn'          : "#ccn",
				'input-cvc'          : "#cvc",
				'input-exp-month'    : "#month",
				'input-exp-year'     : "#year",
				'input-pay-monthly'  : "#payment-monthly",
				'input-pay-once'     : "#payment-once",
				'input-sub'          : "#subscription",
				'input-amount'       : ".donate-option-input:checked",
				'input-select-other' : "#input_amount_other",
				'input-amount-other' : "#input_amount_other_box",
				
				'inputs-cycle'       : ".donate-cycle-input:checked",
				'inputs-amount'      : ".donate-option-input, #input_amount_other_box",
				
				'message'            : "#payment-process"
			},
			
			// HTML Templates for dynamic construction
			template : {
				'message-sent'      : "<div id=\"payment-process\">Securely Contacting Merchant</div>",
				'message-server'    : "<div id=\"payment-process\">Processing Transaction</div>",
				'thank-you'         : "<div id=\"payment-received\">Thank you!</div>"
			}
		},
		
		// Compiled settings.
		options  : false,
		
		// Event binding.
		bind     : {
			merchant : function() {
				
				switch (widget.options.config['merchant'])
				{
					case "braintree" :
						window.braintree.setup(window.ib.config('braintree_key'), "custom", {
							container: widget.options.selector['widget'],
						});
						break;
					
					case "stripe" :
						window.Stripe.setPublishableKey(widget.options.config['stripe-key']);
						break;
				}
				
			},
			
			widget : function() {
				
				widget.bind.merchant();
				
				// $(widget.options.selector['input-pay-once']).insertBefore(widget.options.selector['input-pay-monthly']);
				
				widget.$widget
					.on('submit', widget.events.formSubmit)
					.on('change', widget.options.selector['input-ccn'], widget.events.ccnChange)
					.on('change', widget.options.selector['inputs-cycle'], widget.events.cycleChange)
					.on('change', widget.options.selector['inputs-amount'], widget.events.paymentChange)
					.on('change', widget.options.selector['input-amount-other'], widget.events.otherChange)
					.on('focus', widget.options.selector['input-amount-other'], widget.events.otherFocus);
				
				widget.events.cycleChange();
				widget.events.paymentChange();
				
				widget.notices = $(widget.options.selector['notices'])[0].widget;
			}
		},
		
		// HTML building.
		build    : {
			
		},
		
		// Event trigger handlers.
		events   : {
			ajaxAlways     : function(data, textStatus, errorThrown) {
				widget.$widget.find('button').prop('disabled', false);
			},
			
			ajaxDone     : function(data, textStatus, errorThrown) {
				if (data.amount !== false)
				{
					var $ty = $(widget.options.template['thank-you']);
					
					$(widget.options.selector['message']).replaceWith($ty);
					$ty.hide().fadeIn(500);
					setTimeout(function() { widget.$widget.unblock(); }, 1500);
					
					widget.notices.push("You were successfully charged for <strong>" + data.amount + "</strong>. Thank you for your support!", "success");
				}
				else
				{
					widget.$widget.unblock();
				}
				
				$.each(data.errors, function(index, error) {
					widget.notices.push(error, "error");
				});
			},
			
			ajaxFail     : function(data, textStatus, errorThrown) {
				console.log(data);
				
				widget.$widget.unblock();
				widget.notices.push("The server responded with an unknown error. You were not charged. Please report this issue.", "error");
			},
			
			ccnChange      : function(event) {
				var $ccn = $(this);
				
				$ccn.val( $ccn.val().trim() );
				$ccn.validateCreditCard(
						widget.events.ccnValidate,
						{
							accept: [
								'visa',
								'mastercard',
								'amex',
								'jcb',
								'discover',
								'diners_club_international',
								'diners_club_carte_blanche'
							]
						}
					);
			},
			
			ccnValidate    : function(result) {
				$(this)[0].className = "field-control";
				
				if (result.card_type)
				{
					$(this).addClass(result.card_type.name);
					
					var cvcMax = result.card_type.name === "amex" ? 4 : 3;
					
					$(widget.options.selector['input-cvc'], widget.$widget).attr({
						'maxlength' : cvcMax,
						'size'      : cvcMax,
						'pattern'   : "[0-9]{"+cvcMax+"}"
					});
					
					if (result.valid) {
						return $(this).addClass('control-valid');
					}
					else {
						return $(this).removeClass('control-invalid');
					}
				}
			},
			
			cycleChange    : function(event) {
				var paymentVal = $(widget.options.selector['inputs-cycle']).filter(":checked").val();
				
				if (paymentVal != "once")
				{
					$(widget.options.selector['input-amount-other'])
						.prop('checked', false)
						.parent()
							.toggle(false);
					
					$(widget.options.selector['inputs-amount'])
						.filter("[value=12]")
							.prop('checked', true);
				}
				else
				{
					$(widget.options.selector['input-amount-other'])
						.parent()
							.toggle(true);
				}
				
				widget.events.paymentChange();
			},
			
			otherFocus     : function(event) {
				$(this).val("");
				$(widget.options.selector['input-select-other']).prop('checked', true);
				widget.events.paymentChange();
			},
			
			otherChange    : function(event) {
				widget.events.paymentChange();
			},
			
			formSubmit     : function(event) {
				widget.notices.clear();
				
				var valid = true;
				var sel   = widget.options.selector;
				
				// Make sure the CCN has been validated by the jQuery tool.
				var $ccn  = $(sel['input-ccn']);
				if (!$ccn.is(".control-valid"))
				{
					widget.notices.push("Please enter a valid credit card number.", 'error');
					$ccn.focus().trigger('focus');
					valid = false;
				}
				
				// Check to see if CVC is valid.
				var $cvc = $(sel['input-cvc']);
				if ((new RegExp("^"+$cvc.attr('pattern')+"$")).test($cvc.val()) === false)
				{
					widget.notices.push("Please enter a valid security code. It is three or four digits and found on the back of the card.", 'error');
					$ccn.focus().trigger('focus');
					valid = false;
				}
				
				// Check if expiration date is older than this month.
				var $month     = $(sel['input-exp-month']);
				var $year      = $(sel['input-exp-year']);
				var expiration = parseInt($month.val(), 10) + (parseInt($year.val(), 10) * 12);
				var expiredBy  = new Date().getMonth() + (new Date().getFullYear() * 12);
				if (expiration < expiredBy)
				{
					widget.notices.push("Double-check your expiration date. This card is invalid.", 'error');
					valid = false;
				}
				
				// See what amount we've entered.
				var $amountSel = $(sel['input-amount']).filter(":checked");
				var $amountInp = $(sel['input-amount-other']);
				var amount     = 0;
				if (!$amountSel.length)
				{
					widget.notices.push("Please enter an amount.", 'error');
					valid = false;
				}
				else if ($amountSel.val() == "Other")
				{
					amount = parseInt($amountInp.val(), 10);
					
					if (isNaN(amount) || amount <= 3)
					{
						widget.notices.push("Please enter a real amount that is greater than $3.", 'error');
						$amountInp.focus();
						valid = false;
					}
					else if (amount.toString() !== $amountInp.val())
					{
						widget.notices.push("Please enter a real, whole number as a donation amount.", 'error');
						$amountInp.focus();
						valid = false;
					}
				}
				
				if (valid)
				{
					var $form = $(this);
					
					$form.block({
						message : widget.options.template['message-sent'],
						theme   : true
					});
					
					// Disable the submit button to prevent repeated clicks
					$form.find('button').prop('disabled', true);
					
					// Send the information to our merchant.
					switch (widget.options.config['merchant'])
					{
						case "stripe" :
							Stripe.card.createToken($form, widget.events.stripeResponse);
							break;
						
						default :
						case "braintree" :
							var client = new braintree.api.Client({clientToken: window.ib.config('braintree_key')});
							
							client.tokenizeCard({
									number:          $ccn.val(),
									expirationMonth: $month.val(),
									expirationYear:  $year.val(),
									cvv:             $cvc.val()
								}, widget.events.braintreeResponse);
							
							break;
					}
					
					// Clear personal information.
					$(widget.options.selector['input-ccn'])
						.add(widget.options.selector['input-cvc'])
							.val("")
							.trigger('change');
				}
				
				// Prevent the form from submitting with the default action
				return false;
			},
			
			paymentChange  : function(event) {
				var workFactor = 0.1;
				var timestamp = "";
				
				var paymentVal = $(widget.options.selector['inputs-cycle']).filter(":checked").val();
				var amount = $(widget.options.selector['input-amount']).filter(":checked").val();
				
				if( amount == "Other")
				{
					amount = parseInt($(widget.options.selector['input-amount-other']).val(), 10);
				}
				else
				{
					amount = parseInt(amount, 10);
				}
				
				if (isNaN(amount))
				{
					amount = 0;
				}
				
				var hours = parseFloat(amount * workFactor);
				
				if (hours < 1)
				{
					timestamp = (hours*60).toFixed(0) + " minutes";
				}
				else
				{
					timestamp = hours.toFixed(2) + " hours";
				}
				
				var text = "<strong>$" + amount + " USD</strong> will afford up to <wbr> <strong>" + timestamp + "</strong> of development time" + (paymentVal == "monthly" ? " per month" : "");
				
				$(widget.options.selector['time']).html(text);
			},
			
			
			braintreeResponse : function(err, nonce) {
				var $form = widget.$widget;
				
				if (err) {
					// Show the errors on the form
					widget.notices.push(err, "error");
					
					$form.unblock();
					$form.find('button').prop('disabled', false);
				}
				else if (nonce) {
					// Response contains id and card, which contains additional card details
					var token = nonce;
					
					// Insert the token into the form so it gets submitted to the server
					$form.append($('<input type="hidden" name="nonce" />').val(token));
					
					// Submit to server
					var parameters = $form
						.add("<input type=\"hidden\" name=\"ajax\" value=\"1\" />")
						.serialize();
					
					if ($(widget.options.selector['input-amount']).val() == "Other")
					{
						parameters += "&amount=" + $(widget.options.selector['input-amount-other']).val();
					}
					
					widget.submit(parameters);
				}
				else
				{
					console.log("Unrecognized braintree response.", arguments);
				}
			},
			
			stripeResponse : function(status, response) {
				var $form = widget.$widget;
				
				if (response.error) {
					// Show the errors on the form
					widget.notices.push(response.error.message, "error");
					
					$form.unblock();
					$form.find('button').prop('disabled', false);
				}
				else {
					// Response contains id and card, which contains additional card details
					var token = response.id;
					
					// Insert the token into the form so it gets submitted to the server
					$form.append($('<input type="hidden" name="nonce" />').val(token));
					
					// Submit to server
					var parameters = $form
						.add("<input type=\"hidden\" name=\"ajax\" value=\"1\" />")
						.serialize();
					
					if ($(widget.options.selector['input-amount']).val() == "Other")
					{
						parameters += "&amount=" + $(widget.options.selector['input-amount-other']).val();
					}
					
					widget.submit(parameters);
				}
			}
		},
		
		// Form submission.
		submit   : function(parameters) {
			var $form = widget.$widget;
			
			// Change our server message.
			$(widget.options.selector['message']).replaceWith(widget.options.template['message-server']);
			
			$.post(
				$form.attr('action'),
				parameters
			)
				.done(widget.events.ajaxDone)
				.fail(widget.events.ajaxFail)
				.always(widget.events.ajaxAlways);
		}
		
	};
	
	return widget;
});

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
					console.log($(event.target).closest(this));
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

// ===========================================================================
// Purpose          : Posts
// Contributors     : jaw-sh
// Widget Version   : 2
// ===========================================================================

(function(window, $, undefined) {
	// Widget blueprint
	var blueprint = ib.getBlueprint();

	// Configuration options
	var options = {
		enable : {
			type : "bool",
			initial : false
		}
	};

	// Event bindings
	blueprint.prototype.bind = function() {
		if (!this.is('enable'))
		{
			console.log("InstantClick ignored");
			return false;
		}

		console.log("InstantClick init");

		InstantClick.init(this.options.wait);

		blueprint.prototype.storage.jQuery       = window.jQuery;
		blueprint.prototype.storage.ib           = window.ib;
		blueprint.prototype.storage.InstantClick = window.InstantClick;

		$.each(this.events.InstantClick, function(eventName, eventClosure) {
			InstantClick.on(eventName, eventClosure);
		});
	};

	blueprint.prototype.defaults = {
		'wait' : 50
	};

	blueprint.prototype.events = {
		InstantClick : {
			change : function() {
				console.log("InstantClick change");

				this.storage;
				// Restore our cached objects.
				window.jQuery       = blueprint.prototype.storage.jQuery;
				window.$            = blueprint.prototype.storage.jQuery;
				window.ib           = blueprint.prototype.storage.ib;
				window.InstantClick = blueprint.prototype.storage.InstantClick;

				// Insert our window.app data.
				jQuery.globalEval( $("#js-app-data").html() );

				// Bind all widgets.
				ib.bindAll();

				// Scroll to requested item.
				if (window.location.hash != "")
				{
					var elem = document.getElementById(window.location.hash);

					if (elem && typeof elem.scrollToElement === "function")
					{
						elem.scrollToElement();
					}
				}
			}
		}
	};

	// Long-term storage that the InstantCLick widget uses to preserve script
	// items between page sessions.
	blueprint.prototype.storage = {
		jQuery       : null,
		ib           : null,
		InstantClick : null,
	};

	ib.widget("instantclick", blueprint, options);

	$(document).one('ready', function(event) {
		ib.bindElement(document.documentElement);
	});
})(window, window.jQuery);

// ===========================================================================
// Purpose          : JavaScript Configuration UI Dialog
// Contributors     : jaw-sh
// Widget Version   : 2
// ===========================================================================

(function(window, $, undefined) {
	// Widget blueprint
	var blueprint = ib.getBlueprint();
	
	// Configuration options
	var options = {
		
	};
	
	// Main Widget Initialization Binding
	blueprint.prototype.bind = function() {
		var data = {
			widget  : this,
			$widget : this.$widget
		};
		
		this.$widget.on('click.ib-js-config', data, this.events.navClick);
	};
	
	// Default configurable valuess.
	blueprint.prototype.defaults = {
		
		// Significant Class Names
		classname : {
			menuactive : "config-nav-active"
		},
		
		// jQuery Selectors
		selector : {
			close     : "#js-config-close",
			menuitems : ".config-nav-item",
			fieldsets : ".config-group"
		},
		
		// HTML Templates
		template : {
			// The outer panel.
			panel     : "<form id=\"js-config\"></form>",
			
			// Close button
			close     : "<div id=\"js-config-close\"><i class=\"fa fa-close\"></i></div>",
			
			// Config title
			title     : "<h1 class=\"config-title\">Infinity Next User Options</h1>",
			
			// Inner container.
			container : "<table class=\"config-table\"></table>",
			
			// Sub-container for both nav and fieldsets.
			interior  : "<tr class=\"config-interior\"></tr>",
			
			// Outer container for the navigation list.
			navcell   : "<td class=\"config-cell cell-nav\"></td>",
			
			// Navigation list.
			navlist   : "<ul class=\"config-nav-list\"></ul>",
			
			// Navigation item.
			navitem   : "<li class=\"config-nav-item\"><i class=\"fa\"></i></li>",
			
			// Outer container for the fieldsets
			fieldcell : "<td class=\"config-cell cell-fields\"></td>",
			
			// Widget fieldset
			fieldset  : "<fieldset class=\"config-group\"></fieldset>",
			
			// Widget fieldset legend
			legend    : "<legend class=\"config-legend\"></legend>",
			
			// Fieldset description
			fielddesc : "<p class=\"config-desc\"></p>",
			
			// Row for the fields.
			row       : "<label class=\"config-row\"></label>",
			
			// Text container for field labels.
			rowname   : "<span class=\"config-row-name\"></span>"
		}
	};
	
	// Event definitions
	blueprint.prototype.events = {
		
		menuClick : function(event) {
			// Change the active fieldset.
			var widget = event.data.widget;
			var target = event.delegateTarget;
			var menuWidgetName = event.target.dataset.fieldset;
			
			var $menuitems = $(widget.options.selector.menuitems, target);
			var $fieldsets = $(widget.options.selector.fieldsets, target);
			
			// Change visible fieldsets.
			$fieldsets.each(function() {
				$(this).toggle(this.dataset.fieldset == menuWidgetName);
			});
			
			// Change active menu item.
			$menuitems.each(function() {
				$(this).toggleClass(
					widget.options.classname.menuactive,
					this.dataset.fieldset == menuWidgetName
				);
			});
		},
		
		navClick : function(event) {
			// Open the interface for changing settings.
			event.data.widget.presentDialog();
		}
		
	};
	
	// Dialog (Option Menu) Presentation
	blueprint.prototype.presentDialog = function() {
		var widget     = this;
		var $dialog    = $(widget.options.template.panel);
		
		// Begin putting together the dialog.
		var $close = $(widget.options.template.close);
		$close.appendTo($dialog);
		
		var $title = $(widget.options.template.title);
		$title.appendTo($dialog);
		
		var $container = $(widget.options.template.container);
		$container.appendTo($dialog);
		
		var $interior  = $(widget.options.template.interior);
		$interior.appendTo($container);
		
		var $navcell   = $(widget.options.template.navcell);
		$navcell.appendTo($interior);
		
		var $fieldcell = $(widget.options.template.fieldcell);
		$fieldcell.appendTo($interior);
		
		var $navlist   = $(widget.options.template.navlist);
		$navlist.appendTo($navcell);
		
		// This indicates if we've unhidden our first fieldset pane.
		var firstFieldset = true;
		
		// Loop through each widget and pluck their settings.
		jQuery.each(ib.settings, function(widgetName, settings) {
			// Ensure there are options for this group.
			if (Object.keys(settings).length)
			{
				// Translate our widget name to a title.
				var widgetTitle = ib.trans(widgetName + ".title");
				var widgetDesc  = ib.trans(widgetName + ".desc");
				
				// Add a fieldset.
				var $fieldset = $(widget.options.template.fieldset)
					.data('fieldset', widgetName)
					.attr('data-fieldset', widgetName)
					.appendTo($fieldcell);
				
				// Append its legend.
				$(widget.options.template.legend)
					.append(widgetTitle)
					.appendTo($fieldset);
				
				// Append an optional description.
				if (widgetDesc.length > 0)
				{
					$(widget.options.template.fielddesc)
						.append(widgetDesc)
						.appendTo($fieldset);
				}
				
				// Add a navigation item.
				$(widget.options.template.navitem)
					.addClass('item-'+widgetName)
					.data('fieldset', widgetName)
					.attr('data-fieldset', widgetName)
					.append(widgetTitle)
					.appendTo($navlist);
				
				jQuery.each(settings, function(settingName, setting) {
					// Turn a setting into a row.
					var $name = $(widget.options.template.rowname)
						.append(setting.getLabel());
					
					var $field = setting.toHTML();
					
					$(widget.options.template.row)
						.append($name)
						.append($field)
						.appendTo($fieldset)
						.attr('id', "js-config-row-"+widgetName+"-"+settingName);
				});
				
				if (firstFieldset)
				{
					firstFieldset = false;
					$fieldset.show();
				}
			}
		});
		
		// Blocks out the screen with an interruptable dialog.
		$.blockUI({
			message    : $dialog,
			css        : {
				background : "none",
				border     : "none",
				padding    : 0,
				margin     : 0,
				textAlign  : "left",
				cursor     : "normal",
				top        : "10vh",
				left       : "0",
				width      : "100%",
				'pointer-events' : "none"
			},
			overlayCSS : {
				border     : "none",
				padding    : 0,
				margin     : 0,
				textAlign  : "left",
				cursor     : "normal"
			}
		});
		
		// Bind fade event.
		$(".blockOverlay").one('click.ib-js-config', $.unblockUI);
		$(widget.options.selector.close)
			.one('click.ib-js-config', $.unblockUI);
		
		// Bind menu toggle items
		$dialog.on(
			'click.ib-js-config',
			widget.options.selector.menuitems,
			{
				widget  : widget,
				$widget : widget.$widget
			},
			this.events.menuClick
		);
	};
	
	ib.widget("js-config", blueprint, options);
})(window, window.jQuery);

// ===========================================================================
// Purpose          : Lazy Image Loading
// Contributors     : jaw-sh
// Widget Version   : 2
// ===========================================================================

(function(window, $, undefined) {
	// Widget blueprint
	var blueprint = ib.getBlueprint();
	
	// Configuration options
	var options = {
		enable : {
			type : "bool",
			initial : false
		}
	};
	
	// Main Widget Initialization Binding
	blueprint.prototype.bind = function() {
		if (!this.is('enable'))
		{
			return false;
		}
		var widget  = this;
		var $widget = this.$widget;
		var data    = {
			widget  : widget,
			$widget : $widget
		};
		
		$widget.on('click', data, this.events.navClick);
		
		// With this image remove src data and indicate it's lazy.
		$widget.addClass("lazy-load");
		$widget.attr('data-src', $widget[0].src);
		$widget[0].src = "";
		
		$widget.on('lazywake.ib-lazymg',  data, widget.events.imageLazyWake);
		$(window).on('scroll.ib-lazyimg', data, widget.events.windowScroll);
		
		return true;
	};
	
	// The default values that are set behind init values.
	blueprint.prototype.defaults = {
		// Selectors for finding and binding elements.
		selector : {
			'img'      : "img",
			'img-lazy' : "img.lazy-load",
		}
	};
	
	blueprint.prototype.events = {
		imageLazyWake : function(event) {
			var widget  = event.data.widget;
			var $widget = event.data.$widget;
			
			$(widget.options.selector['img-lazy'], $widget).each(function() {
				var $this = $(this);
				
				$this.removeClass("lazy-load");
				
				// Correct the source.
				this.src = $this.attr('data-src');
				
				// Call the postbox widget to check if the image can be played
				// before the user tries to click it.
				$this.trigger('media-check');
			});
		},
		
		windowScroll : function(event) {
			// Determine if the boundaries of this image are in the viewport.
			var widget        = event.data.widget;
			var $widget       = event.data.$widget;
			var $window       = $(window);
			
			var docViewTop    = $window.scrollTop();
			var docViewBottom = docViewTop + $window.height();
			
			var viewPad = 200;
			
			var elemTop       = $widget.offset().top - viewPad;
			var elemBottom    = elemTop + $widget.height() + viewPad;
			
			// We don't need the entire image to be present, just either edge.
			
			// If the top boundary is present
			if ( ((elemTop <= docViewBottom) && (elemTop >= docViewTop)) ||
				((elemBottom <= docViewBottom) && (elemBottom >= docViewTop)) )
			{
				$widget.trigger('lazywake');
			}
		}
	};
	
	ib.widget("lazyimg", blueprint, options);
})(window, window.jQuery);

/**
 * Message Widget
 */
ib.widget("notice", function(window, $, undefined) {
	var widget = {
		
		// The default values that are set behind init values.
		defaults : {
			// Selectors for finding and binding elements.
			selector : {
				'widget'        : ".form-messages",
				'message'       : ".form-message",
			},
			
			// HTML Templates for dynamic construction
			template : {
				'message'         : "<li class=\"form-message\"></li>",
				'message-info'    : "<li class=\"form-message message-info\"></li>",
				'message-success' : "<li class=\"form-message message-success\"></li>",
				'message-error'   : "<li class=\"form-message message-error\"></li>"
			}
		},
		
		// Compiled settings.
		options  : false,
		
		bind     : {
			widget : function() {
				widget.$widget
					.on('click.ib-notice', widget.options.selector['message'], widget.events.noticeClick)
				;
			}
		},
		
		events   : {
			
			noticeClick : function(event) {
				var $this = $(this);
				
				// Make sure we don't hijack and link clicks.
				if ($(this.toElement).is('[href]'))
				{
					return true;
				}
				
				// Fade out and remove the notice very quickly after clicking it.
				$this.fadeOut(250, function() {
					$this.remove();
				});
				
				event.preventDefault();
				return false;
			}
			
		},
		
		build    : {
			
		},
		
		clear    : function() {
			widget.$widget.children().remove();
		},
		
		push     : function(message, messageType) {
			if (widget.options === false) {
				widget.init();
			}
			
			var $message;
			var className = "message";
			
			if (widget.options.template['message-'+messageType] !== undefined)
			{
				className = 'message-'+messageType;
			}
			
			$message = $(widget.options.template[className]);
			$message.append(message).appendTo(widget.$widget);
			
			// Scroll our window up to meet the notification if required.
			if ($message.offsetParent().css('position') !== "fixed")
			{
				$('html, body').animate({
						scrollTop : $message.offset().top - $(".board-header").height() - 10
					},
					250
				);
			}
			
			return $message;
		}
		
	};
	
	return widget;
});

// ============================================================
// Purpose                      : Permission Sheets
// Contributors                 : jaw-sh
// ============================================================

ib.widget("permissions", function(window, $, undefined) {
	var widget = {
		
		defaults : {
			selector : {
				
			}
		},
		
		// Events
		events   : {
			
		},
		
		// Event bindings
		bind     : {
			widget : function() {
				
			}
		}
		
	};
	
	return widget;
});


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
		preview_delay : 200,

		// Important class names.
		classname : {
			'post-hover'  : "post-hover",
			'cite-you'    : "cite-you"
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
		var cites  = [];

		// Loop through each citation.
		$(this.options.selector['cite'], this.$widget).each(function() {
			var board = this.dataset.board_uri;
			var post  = this.dataset.board_id.toString();

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
			);

		$(document)
			.on(
				'ready.ib-post',
				data,
				widget.events.codeHighlight
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
				'mouseover.ib-post',
				widget.options.selector['attachment-image-expandable'],
				data,
				widget.events.attachmentMediaMouseOver
			)
			.on(
				'mouseout.ib-post',
				widget.options.selector['attachment-image-expandable'],
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

	// Events
	blueprint.prototype.events = {
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

			if (!widget.is('attachment_preview')) {
				return true;
			}

			var $img     = $(this);
			var $link    = $img.parents(widget.options.selector['attachment-link']).first();
			var $preview = $(widget.options.selector['hover-box']);

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

				$body
					.val($body.val() + ">>" + $this.data('board_id') + "\n")
					.focus();

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

								console.log($backlink);
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
		}
	};

	ib.widget("post", blueprint, options);
})(window, window.jQuery);

// ===========================================================================
// Purpose          : Posts
// Contributors     : jaw-sh
// Widget Version   : 2
// ===========================================================================

(function(window, $, undefined) {
	// Widget blueprint
	var blueprint = ib.getBlueprint();

	var options = {
		password : {
			type : "text",
			initial : ib.randomString(8),
		}
	};

	// Dropzone instance.
	blueprint.prototype.dropzone = null;

		// jQuery UI bind indicators
	blueprint.prototype.resizable = false;
	blueprint.prototype.draggable = false;
	blueprint.prototype.axis      = ib.ltr ? "sw" : "se";

	// Other widget instances.
	blueprint.prototype.notices = null;

	// Number of uploads running.
	// Used to prevent premature form submission.
	blueprint.prototype.activeUploads = 0;

		// The default values that are set behind init values.
	blueprint.prototype.defaults = {
		checkFileUrl  : window.app.board_url + "/check-file",

		// Selectors for finding and binding elements.
		selector : {
			'widget'          : "#post-form",
			'notices'         : "[data-widget=notice]:first",
			'autoupdater'     : ".autoupdater:first",

			'dropzone'        : ".dz-container",

			'submit-post'     : "#submit-post",

			// This is the main postbox password.
			'password'        : "#password",
			// This is any field that uses the same password.
			'post-password'   : ".post-password",

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
			url            : window.app.board_url + "/upload-file",

			// Allow multiple uploads.
			uploadMultiple : true,

			// Maximum filesize (MB)
			maxFilesize    : window.app.settings.attachmentFilesize / 1024,

			// Binds the instance to our widget.
			init: function() {
				var widget = this.options.widget;

				widget.dropzone = this;
				this.widget     = widget;
				this.$widget    = widget.$widget;

				$(this.element).append("<input type=\"hidden\" name=\"dropzone\" value=\"1\" />");
			},

			// Handles the acceptance of files.
			accept : function(file, done) {
				var widget  = this.widget;
				var $widget = this.$widget;
				var reader  = new FileReader();

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
				var $widget = this.$widget;
				$widget.trigger('fileCanceled', [ file ]);
			},

			error : function(file, message, xhr) {
				var widget  = this.widget;
				var $widget = this.$widget;

				widget.notices.push(message, 'error');

				$(file.previewElement).remove();

				$widget.trigger('fileFailed', [ file ]);
			},

			removedfile : function(file) {
				var widget = this.widget;
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
				var widget  = this.widget;
				var $widget = this.$widget;

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
						.append($("<input type=\"hidden\" />").attr('name', widget.options.dropzone.paramName+"[hash][]").val(file.hash))
						.append($("<input type=\"hidden\" />").attr('name', widget.options.dropzone.paramName+"[name][]").val(file.name))
					;

					$("[data-dz-spoiler]", $preview)
						.attr('name', widget.options.dropzone.paramName+"[spoiler][]");
				}

				$widget.trigger('fileUploaded', [ file ]);
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
	};

	// Compiled settings.
	blueprint.prototype.options = false;

	blueprint.prototype.hasCaptcha = function() {
		return $(this.options.selector['captcha-row'], this.$widget).is(":visible");
	};

	blueprint.prototype.resizePostbox = function() {
		var widget  = this;
		var $widget = this.$widget;

		if (widget.resizable)
		{
			if (window.innerHeight < 480 || window.innerWidth < 728)
			{
				widget.unbindDraggable();
				widget.unbindResize();
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
			var isClosed = $widget.hasClass("postbox-closed");
			var isMaximized = $widget.hasClass("postbox-maximized");

			if (!isMaximized && !isClosed)
			{
				widget.bindResize();
			}

			if (!isMaximized)
			{
				widget.bindDraggable();
			}
		}
	};

	// Events
	blueprint.prototype.events = {
		bodyChange    : function(event) {
			var widget  = event.data.widget;
			var $widget = event.data.$widget;

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

		captchaHide   : function(widget) {
			var $widget = widget.$widget;

			$(widget.options.selector['captcha-row'], $widget).hide();
		},

		captchaShow   : function(widget) {
			var $widget = widget.$widget;

			$(widget.options.selector['captcha-row'], $widget).show();
		},

		closeClick    : function(event) {
			var widget  = event.data.widget;
			var $widget = event.data.$widget;

			// Tweak classes.
			$widget
				.removeClass("postbox-maximized postbox-minimized")
				.addClass("postbox-closed");

			// Unbind the jQuery UI resize.
			widget.unbindResize();

			// Prevents formClick from immediately firing.
			event.stopPropagation();
		},

		fileUploading : function(event, file) {
			var widget  = event.data.widget;
			var $widget = event.data.$widget;

			++widget.activeUploads;
			console.log(widget.activeUploads + " concurrent uploads.");

			$(widget.options.selector['submit-post'], $widget)
				.prop('disabled', widget.activeUploads > 0);
		},

		fileCanceled  : function(event, file) {
			var widget  = event.data.widget;
			var $widget = event.data.$widget;

			--widget.activeUploads;
			console.log(widget.activeUploads + " concurrent uploads.");

			$(widget.options.selector['submit-post'], $widget)
				.prop('disabled', widget.activeUploads > 0);
		},

		fileFailed    : function(event, file) {
			var widget  = event.data.widget;
			var $widget = event.data.$widget;

			--widget.activeUploads;
			console.log(widget.activeUploads + " concurrent uploads.");

			$(widget.options.selector['submit-post'], $widget)
				.prop('disabled', widget.activeUploads > 0);
		},

		fileUploaded  : function(event, file) {
			var widget  = event.data.widget;
			var $widget = event.data.$widget;

			--widget.activeUploads;
			console.log(widget.activeUploads + " concurrent uploads.");

			$(widget.options.selector['submit-post'], $widget)
				.prop('disabled', widget.activeUploads > 0);
		},

		formClear     : function(event) {
			var widget  = event.data.widget;
			var $widget = event.data.$widget;

			// Stops redundant loading of captcha when we don't need one.
			if (widget.hasCaptcha())
			{
				$(widget.options.selector['captcha'], $widget).trigger('reload');
			}

			if (widget.dropzone)
			{
				widget.dropzone.removeAllFiles();
			}

			$(widget.options.selector['form-clear'], $widget)
				.val("")
				.html("");

			$(widget.options.selector['form-body'], $widget)
				.trigger('change')
				.focus();
		},

		formClick     : function(event) {
			var widget  = event.data.widget;
			var $widget = event.data.$widget;

			if ($widget.is(".postbox-closed"))
			{
				// Tweak classes.
				$widget.removeClass("postbox-minimized postbox-closed postbox-maximized");

				// Rebind jQuery UI widgets.
				widget.bindDraggable();
				widget.bindResize();
			}
		},

		formSubmit    : function(event) {
			var widget  = event.data.widget;
			var $widget = event.data.$widget;

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

			// Temporarialy disable form and submit button to prevent double posting
			$form.prop('disabled', true);
			$(widget.options.selector['submit']).prop('disabled', true);

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
					$(widget.options.selector['submit']).prop('disabled', false);

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

						widget.events.formClear(event);
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
			var widget  = event.data.widget;
			var $widget = event.data.$widget;

			// Tweak classes.
			$widget
				.removeClass("postbox-minimized postbox-closed")
				.addClass("postbox-maximized");

			// Remove jQuery UI widgets.
			widget.unbindDraggable();
			widget.unbindResize();
		},

		messenger     : function(event, messages) {
			if (messages.messenger)
			{
				ib.getInstances('postbox').each(function()
				{
					var widget  = this.widget;
					var $widget = widget.$widget;

					// Toggles captcha based on messenger information.
					if (messages.captcha)
					{
						widget.events.captchaShow(widget);
						$(widget.options.selector['captcha-row'], $widget)
							.trigger('load', messages.captcha);
					}
					else
					{
						widget.events.captchaHide(widget);
					}
				});
			}
		},

		minimizeClick : function(event) {
			var widget  = event.data.widget;
			var $widget = event.data.$widget;

			// Tweak classes.
			$widget
				.removeClass("postbox-maximized postbox-closed")
				.addClass("postbox-minimized");

			// Rebind jQuery UI Resize.
			widget.bindDraggable();
			widget.bindResize();
		},

		pageChange    : function(event) {
			widget.options.checkFileUrl = window.app.board_url + "check-file";
			widget.dropzone.options.url = window.app.board_url + "upload-file";
			widget.dropzone.options.maxFilesize = window.app.settings.attachmentFilesize / 1024;
		},

		postDragStop  : function(event, ui) {
			var widget  = this.widget;
			var $widget = this.widget.$widget;

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
			var widget  = event.data.widget;
			var $widget = event.data.$widget;

			// Captures CTRL+ENTER
			if ((event.keyCode == 10 || event.keyCode == 13) && event.ctrlKey)
			{
				$(widget.options.selector['submit-post'], $widget)
					.trigger('click');

				event.preventDefault();
				return false;
			}
		},

		postResize    : function(event, ui) {
			var widget  = this.widget;
			var $widget = this.widget.$widget;

			var $post = $(this);

			ui.position.top  = 0;
			ui.position.left = 0;

			var formHangY   = window.innerHeight - ($widget.position().top + widget.$widget.outerHeight());
			ui.size.width   = Math.min(ui.size.width, $widget.width());
			ui.size.height += Math.min(0, formHangY);

			widget.$widget.css({
				'height' : formHangY > 0 ? "auto" : window.innerHeight - $widget.position().top
			});

			$post.css('width', ui.size.width);
			$post.children().first().css('width', "100%");

			return ui;
		},

		postResizeStart : function(event, ui) {
			var widget  = this.widget;
			var $widget = this.widget.$widget;
			var axis    = $(this).data('ui-resizable').axis;

			if (widget.axis != axis)
			{
				var rect  = this.getBoundingClientRect();

				if (widget.axis == "sw")
				{
					$widget[0].style.left  = rect.left + "px";
					$widget[0].style.right = "auto";
				}
			}
		},

		postResizeStop  : function(event, ui) {
			var widget  = this.widget;
			var $widget = this.widget.$widget;
			var axis    = $(this).data('ui-resizable').axis;

			if (widget.axis != axis)
			{
				var rect  = this.getBoundingClientRect();

				if (widget.axis == "sw")
				{
					var right = (document.body.clientWidth - rect.right);

					$widget[0].style.left  = "auto";
					$widget[0].style.right = right + "px";
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
				event.data.widget.resizePostbox();
			}
		}
	},

	// Event bindings
	blueprint.prototype.bind = function() {
		var widget  = this;
		var $widget = this.$widget;
		var data    = {
			widget  : widget,
			$widget : $widget
		};

		$(widget.options.selector['password'], $widget)
			.val(ib.settings.postbox.password.get());

		// Force the notices widget to be bound, and then record it.
		// We have to do this because the notices widget is a child within this widget.
		// The parent is bound first.
		widget.notices = window.ib.bindElement($(widget.options.selector['notices'])[0]);

		if (typeof window.Dropzone !== 'undefined')
		{
			var dropzoneOptions = jQuery.extend({}, widget.options.dropzone);
			dropzoneOptions.widget  = widget;
			dropzoneOptions.$widget = $widget;

			$(widget.options.selector['dropzone'], $widget)
				.dropzone(dropzoneOptions);
		}

		$(window)
			.on('messenger.ib-postbox.', data, widget.events.messenger)
			.on('resize.ib-postbox',     data, widget.events.windowResize);

		// This will actually bind multiple times so make sure it only happens once.
		if (widget.initOnce !== true)
		{
			// Ensures window.app is current with dropzone stuff.
			//InstantClick.on("change", data, widget.events.pageChange);
		}

		$widget
			// Watch for key downs as to capture ctrl+enter submission.
			// We don't die this to any particular item.
			.on(
				'keydown.ib-postbox',
				data,
				widget.events.postKeyDown
			)

			// Watch for form size clicks
			.on(
				'click.ib-postbox',
				data,
				widget.events.formClick
			)
			.on(
				'click.ib-postbox',
				widget.options.selector['button-close'],
				data,
				widget.events.closeClick
			)
			.on(
				'click.ib-postbox',
				widget.options.selector['button-maximize'],
				data,
				widget.events.maximizeClick
			)
			.on(
				'click.ib-postbox',
				widget.options.selector['button-minimize'],
				data,
				widget.events.minimizeClick
			)

			// Watch field changes
			.on(
				'change.ib-postbox',
				widget.options.selector['form-body'],
				data,
				widget.events.bodyChange
			)
			.on(
				'keyup.ib-postbox',
				widget.options.selector['form-body'],
				data,
				widget.events.bodyChange
			)
			.on(
				'change.ib-postbox',
				widget.options.selector['form-spoiler'],
				data,
				widget.events.spoilerChange
			)

			// Watch form submission.
			.on(
				'submit.ib-postbox',
				data,
				widget.events.formSubmit
			)

			// Watch for file statuses.
			.on(
				'fileFailed.ib-postbox',
				data,
				widget.events.fileFailed
			)
			.on(
				'fileCanceled.ib-postbox',
				data,
				widget.events.fileCanceled
			)
			.on(
				'fileUploaded.ib-postbox',
				data,
				widget.events.fileUploaded
			)
			.on(
				'fileUploading.ib-postbox',
				data,
				widget.events.fileUploading
			)
		;

		widget.bindCounter();
		widget.bindDraggable();
		widget.bindResize();
	};

	blueprint.prototype.bindCounter = function() {
		var widget   = this;
		var $widget  = this.$widget;
		var $body    = $(widget.options.selector['form-body'], widget.$widget);
		var $counter = $(widget.options.template['counter']);

		$counter.insertAfter($body);
		widget.$counter = $counter;
		$body.trigger('change');
	};

	blueprint.prototype.bindDraggable = function() {
		var widget   = this;
		var $widget  = this.$widget;

		if (window.innerHeight >= 480 && window.innerWidth >= 728)
		{
			$widget.draggable({
				containment : "window",
				handle      : "legend.form-legend",
				stop        : widget.events.postDragStop
			});

			widget.draggable = true;
		}
	};

	blueprint.prototype.bindResize = function() {
		var widget   = this;
		var $widget  = this.$widget;

		if (window.innerHeight >= 480 && window.innerWidth >= 728)
		{
			// Bind resizability onto the post area.
			var $body   = $(widget.options.selector['form-body'], $widget);

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

				// This gives the jQuery UI events a scope back to the widget.
				var jWidget = $body.resizable("widget")[0];
				jWidget.widget  = widget;
				jWidget.$widget = $widget;

				$widget
					.resizable({
						handles:  null,
						minWidth: 300
					})
					.css({
						height: "auto"
					});

				widget.resizable = true;
			}
		}
	};

	blueprint.prototype.unbindCounter = function() {
		var widget   = this;

		if (widget.$counter && widget.$counter instanceof jQuery)
		{
			widget.$counter.remove();
		}
	};

	blueprint.prototype.unbindDraggable = function() {
		var widget   = this;
		var $widget  = this.$widget;

		if (widget.draggable && typeof $widget.draggable === "function")
		{
			$widget.draggable( "destroy" ).attr('style', "");

			widget.draggable = false;
		}
	};

	blueprint.prototype.unbindResize = function() {
		var widget   = this;
		var $widget  = this.$widget;

		// Bind resizability onto the post area.
		var $body = $(widget.options.selector['form-body'], widget.$widget);

		if (widget.resizable && $body.length && typeof $body.resizable === "function")
		{
			$body.resizable( "destroy" ).attr('style', "");

			$widget.resizable( "destroy" ).attr('style', "");

			widget.resizable = false;
		}
	};

	ib.widget("postbox", blueprint, options);
	ib.settings.postbox.password.setInitial(false);

	$(document).on('ready.ib-postbox', function(event) {
		// Bit of a hack.
		// Sets form values to our password by default even outside
		// of the scope of a thread.
		$(blueprint.prototype.defaults.selector['post-password'])
			.val(ib.settings['postbox']['password'].get());
	});
})(window, window.jQuery);

// ===========================================================================
// Purpose          : Custom Styling
// Contributors     : jaw-sh
// Widget Version   : 2
// ===========================================================================

(function(window, $, undefined) {
	// Widget blueprint
	var blueprint = ib.getBlueprint();
	
	// Configuration options
	var options = {
		theme : {
			default : "",
			type    : "select",
			values  : [
				"",
				"next-yotsuba.css",
				"next-dark.css",
				"next-tomorrow.css",
				"kappa-burichan.css",
			],
			onChange : function(event) {
				var setting = event.data.setting.get();
				var domObj  = document.getElementById('theme-stylesheet');
				
				if (setting)
				{
					domObj.href = window.app.url + "/static/css/skins/" + setting;
				}
				else
				{
					domObj.href = "";
				}
			},
			onUpdate : function(event) {
				var setting = event.data.setting.get();
				var domObj  = document.getElementById('theme-stylesheet');
				
				if (setting)
				{
					domObj.href = window.app.url + "/static/css/skins/" + setting;
				}
				else
				{
					domObj.href = "";
				}
			}
		},
		
		css : {
			default : "",
			type    : "textarea",
			onChange : function(event) {
				var setting = event.data.setting.get();
				var domObj  = document.getElementById('user-css');
				domObj.innerHTML = setting;
			},
			onUpdate : function(event) {
				var setting = event.data.setting.get();
				var domObj  = document.getElementById('user-css');
				domObj.innerHTML = setting;
			}
		}
	};
	
	ib.widget("stylist", blueprint, options);
})(window, window.jQuery);

// ===========================================================================
// Purpose          : Timestamp Configuration
// Contributors     : jaw-sh
// Widget Version   : 2
// ===========================================================================

(function(window, $, undefined) {
	// Widget blueprint
	var blueprint = ib.getBlueprint();
	
	// Configuration options
	var options = {
		// Date Format
		format : {
			type : "select",
			initial : "YYYY-MMM-DD HH:MM:SS",
			values : [
				'YYYY-MMM-DD HH:MM:SS',
				'MM/DD/YY(DDD)HH:MM:SS'
			],
			onChange : function(event) {
				// On setting update, trigger reformating..
				var setting = event.data.setting;
				var widget  = setting.widget;
				ib.getInstances(widget).trigger('reformat.ib-time');
			},
			onUpdate : function(event) {
				// On storage update, trigger reformating.
				var setting = event.data.setting;
				var widget  = setting.widget;
				
				for (var i = 0; i < ib.widgets[widget].instances.length; ++i)
				{
					var instance = ib.widgets[widget].instances[i];
					instance.$widget.trigger('reformat.ib-time');
				}
			}
		}
	};
	
	// Main Widget Initialization Binding
	blueprint.prototype.bind = function() {
		var data = {
			widget  : this,
			$widget : this.$widget
		};
		
		this.$widget
			.on('reformat.ib-time', data, this.events.timeReformat)
			.trigger('reformat.ib-time');
	};
	
	// Event hooks
	blueprint.prototype.events = {
		// Reformat timestamps on command.
		timeReformat : function(event) {
			// Fetch our existing information.
			var widget = event.data.widget;
			var text   = event.data.$widget.text();
			var time   = new Date(event.data.$widget.attr('datetime'));
			
			var y = ib.lpad(time.getFullYear(), 2, "0");
			var m = ib.lpad(time.getMonth() + 1, 2, "0");
			var d = ib.lpad(time.getDate(), 2, "0");
			var hour = ib.lpad(time.getHours(), 2, "0");
			var min  = ib.lpad(time.getMinutes(), 2, "0");
			var sec  = ib.lpad(time.getSeconds(), 2, "0");
			
			switch (widget.get('format'))
			{
				case 'YYYY-MMM-DD HH:MM:SS' :
					// Translate 0 into Dec
					m = ib.trans("time.calendar.abbrevmonths."+time.getMonth());
					text = y+"-"+m+"-"+d+" "+hour+":"+min+":"+sec;
					break;
				
				case 'MM/DD/YY(DDD)HH:MM:SS' :
					var dow = time.getDay();
					dow = ib.trans("time.calendar.abbrevdays." + dow);
					
					text = m+"/"+d+"/"+y+"("+dow+")"+hour+":"+min+":"+sec;
					break;
				
				default :
					console.log("Invalid format \""+this.get('format')+"\"");
					break;
			}
			
			event.data.$widget.text(text);
		}
	};
	
	ib.widget("time", blueprint, options);
})(window, window.jQuery);

//# sourceMappingURL=app.js.map
