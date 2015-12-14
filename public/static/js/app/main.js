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
				$scope = eventOrScope;
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
		
		$("[data-widget]", $scope).each(function() {
			ib.bindElement(this);
		});
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
			for (var x = 0; x < records.length; ++x)
			{
				var nodes = records[x].addedNodes;
				
				for (var y = 0; y < nodes.length; ++y)
				{
					var node = nodes[y];
					
					if (node.attributes && node.attributes['data-widget'])
					{
						ib.bindElement(node)
					}
				}
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
	}
	else
	{
		// Otherwise, we must use jQuery after the document has loaded.
		$(document).on('ready', ib.bindAll);
	}
	
	return ib;
})(window, jQuery);
