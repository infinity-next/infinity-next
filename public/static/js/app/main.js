/**
 * Widget Master
 */
(function(window, $, undefined) {
	var ib = window.ib = function() {};
	
	// Store existing Widgets.
	ib.widgets = {};
	
	// Setup directional logic
	ib.rtl = $("body").hasClass("rtl");
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
	
	/**
	 * Options and Settings
	 */
	ib.option = function(widget, name, type) {
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
		this.storage = "ib.setting." + widget + "." + name;
		this.type    = type;
		this.widget  = widget;
		
		// Synchronize widgets on update
		this.onUpdate(function() {
			$("#js-config-"+setting.widget+"-"+setting.name).val(
				setting.get()
			);
		});
	};
	
	ib.option.prototype.get = function() {
		return localStorage.getItem(this.storage);
	};
	
	ib.option.prototype.getName = function() {
		return this.name;
	};
	
	ib.option.prototype.getType = function() {
		return this.type;
	};
	
	ib.option.prototype.onChange = function(event) {
		event.data.setting.set(this.value);
	};
	
	ib.option.prototype.onUpdate = function(closure) {
		if (typeof closure !== "function") {
			throw "ib.option :: onUpdate not supplied a closure."
		}
		
		window.addEventListener('storage', closure, false);
	};
	
	ib.option.prototype.set = function(value) {
		return localStorage.setItem(this.storage, value);
	};
	
	ib.option.prototype.toHTML = function() {
		var $html = $("<input />");
		
		switch (this.type)
		{
			case 'bool'   :
				$html.attr('type', "checkbox");
				break;
			
			case 'array'  :
				// Not supported yet.
				break;
			
			case 'string' :
				$html.attr('type', "text");
				break;
			
			case 'int'    :
				$html.attr('type', "number");
				break;
		}
		
		$html.attr('id',    "js-config-"+this.widget+"-"+this.name);
		$html.attr('class', "config-option");
		$html.attr('val',   this.get());
		$html.on(
			'change',
			{ 'setting' : this },
			this.onChange
		);
		
		return $html;
	};
	
	ib.option.prototype.validateName = function(name) {
		return typeof name === "string" && name.length > 0;
	};
	
	ib.option.prototype.validateType = function(type) {
		switch (type)
		{
			case 'string' :
			case 'array'  :
			case 'int'    :
			case 'bool'   :
				return true;
		}
		
		return false;
	};
	
	ib.option.prototype.validateWidget = function(widget) {
		return typeof ib.widgets[widget] === "function" || widget === "main";
	};
	
	ib.settings = {
		'main' : {
			'test' : new ib.option("main", "test", "string")
		}
	};
	
	
	// Setup new Widget class.
	ib.widget = function(name, widget, config) {
		console.log("Declaring widget \""+name+"\".");
		
		if (ib.widgets[name] !== undefined)
		{
			console.log("Trying to re-declare widget \""+name+"\".");
			return false;
		}
		
		ib.widgets[name] = widget;
		
		if (typeof config === "object")
		{
			if (typeof ib.settings[name] === "undefined")
			{
				ib.settings[name] = {};
			}
			
			jQuery.each(config, function(optionName, optionParams)
			{
				var optionDefault = null;
				var optionType    = optionParams;
				
				if (typeof optionParams === "object")
				{
					optionDefault = optionParams.default;
					optionType    = optionParams.type;
				}
				
				var option = new ib.option(name, optionName, optionType);
				ib.settings[name][optionName] = option;
			});
		}
		
		return true;
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
	
	// Bind widgets on read.
	$(document).on('ready', ib.bindAll);
	
	return ib;
})(window, jQuery);
