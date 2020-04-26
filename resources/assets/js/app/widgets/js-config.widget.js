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
            close     : "<div id=\"js-config-close\"><i class=\"fas fa-window-close\"></i></div>",

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
