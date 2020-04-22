// ============================================================
// Purpose                      : Config Sheets
// Contributors                 : jaw-sh
// ============================================================

ib.widget("config", function(window, $, undefined) {
    var widget = {
        defaults : {
            selector : {
                'field'         : ".field-control",
                'list-template' : ".option-list .option-item-template .field-control"
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
