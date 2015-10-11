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
		
		if (ib[requestedWidget])
		{
			return ib.bindWidget(element, ib[requestedWidget]);
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
			
			if (typeof dom.widget.init === "function")
			{
				dom.widget.init(dom);
			}
			else
			{
				window.ib.widgetArguments.call(dom.widget, [dom]);
			}
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
		
		if (ib[name] !== undefined)
		{
			console.log("Trying to re-declare widget \""+name+"\".");
			return false;
		}
		
		ib[name] = widget;
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
			widget.bind.widget();
			return true;
		}
		
		return false;
	};
	
	//$(document).on('ready', ib.bindAll);
	$(document).on('ready', "[data-widget]", ib.bindOnEvent);
	
	if (typeof InstantClick === "object")
	{
		InstantClick.on('change', ib.bindAll);
	}
	
	return ib;
	
})(window, jQuery);
