/**
 * Widget Master
 */
(function(window, $, undefined) {
	var ib = window.ib = function() {};
	
	ib.widgets = {};
	
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
	
	ib.bindElement = function(element) {
		var requestedWidget = element.getAttribute('data-widget');
		
		if (ib.widgets[requestedWidget])
		{
			return ib.bindWidget(element, ib.widgets[requestedWidget]);
		}
		else
		{
			console.log("Requested widget \""+requestedWidget+"\" does not exist.");
		}
	};
	
	ib.bindWidget = function(dom, widget)
	{
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
	
	ib.config = function(name, configDefault) {
		if (typeof window.app !== "undefined" && typeof window.app[name] !== "undefined")
		{
			return window.app[name];
		}
		
		return configDefault;
	};
	
	ib.widget = function(name, widget) {
		console.log("Declaring widget \""+name+"\".");
		
		if (ib.widgets[name] !== undefined)
		{
			console.log("Trying to re-declare widget \""+name+"\".");
			return false;
		}
		
		ib.widgets[name] = widget;
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
				try {
					widget.bind.widget();
				}
				catch (error) {
					console.error("Failed to initiate widget!", error);
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
	
	$(document).on('ready', ib.bindAll);
	//$(document).on('ready', "[data-widget]", ib.bindOnEvent);
	
	return ib;
	
})(window, jQuery);
