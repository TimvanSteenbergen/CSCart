(function(_, $) {

    var counter = 0;

    $.fn.extend({
        //
        // Adds the tag
        //
        // @level - level in variable name that should be replaced
        // @clone - if set, the field values will be copied
        // E.g. (replace on '30')
        // level = 1, varname = data[20][sub][50] - after replacement data[30][sub][50]
        // level = 3, varname = data[20][sub][50] - after replacement data[20][sub][30]
    
        cloneNode: function(level, clone, before)
        {
            var before = before || false;
            var clone = clone || false;
    
            var self = $(this);
            $('textarea.cm-wysiwyg', self).ceEditor('destroy');
           
            var regex = new RegExp('((?:\\[\\w+\\]){' + (level - 1) + '})\\[(\\d+)\\]');
            var image_regex = new RegExp('((?:\\[\\w+\\]){0})\\[(\\d+)\\]');
    
            counter++;
    
            new_id = self.prop('id') + '_' + counter;
            $('[data-original-title]', self).tooltip('destroy').addClass('cm-tooltip');

            var new_node = self.clone();
            new_node.prop('id', new_id);
    
            $('select', new_node).each(function(ind) { // copy values of selectboxes
                $(this).val($('select', self).eq(ind).val());
            });
    
            $('textarea', new_node).each(function(ind) { // copy values of textareas
                $(this).val($('textarea', self).eq(ind).val());
            });
    
            // Remove all script tags
            $('script', new_node).remove();
    
            // Remove all picker tags
            $('.cm-picker', new_node).remove();

            // Correct Ids
            var changes = {};
            $('[id],[for],[data-ca-target-id],[data-ca-result-id]', new_node).each(function() {
                var self = $(this);
                if (self.prop('id')) {
                    var id = self.prop('id');
                    if (id.indexOf('_wrap_') != -1) {
                        var tmp_id = id.substr(0, id.indexOf('_wrap_'));
                        var tmp_id2 = id.substr(id.indexOf('_wrap_'));
                        var new_id = tmp_id + '_' + counter + tmp_id2;
                    } else {
                        var new_id = id + '_' + counter;
                    }
                    self.prop('id', new_id);
                    changes[id] = new_id;
                }
                if (self.prop('for')) {
                    var for_value = self.prop('for');
                    var new_for = for_value + '_' + counter;
                    self.prop('for', new_for);
                    changes[for_value] = new_for;
                }
                if (self.attr('data-ca-result-id')) { // '.data(' is not working, may be because it is cloned object, and it has not inserted into DOM yet.
                    var result_id = self.attr('data-ca-result-id');
                    var new_result_id = result_id + '_' + counter;
                    self.attr('data-ca-result-id', new_result_id);
                    changes[result_id] = new_result_id;
                }
                if (self.attr('data-ca-target-id')) {
                    var target_id = self.attr('data-ca-target-id');
                    // If more than one values separated by ',' are specified, update them all
                    self.attr('data-ca-target-id', $.map(target_id.split(','), function(id) {
                        return id + '_' + counter;
                    }).join(','));
                }
            });

            // Check if the clone object is link. If so, convert the href path.
            $('[href]', new_node).each(function() {
                var self = $(this);
                var href = self.prop('href');

                for (k in changes) {
                    var expr = new RegExp(k + '(?=&|$)');
                    href = href.replace(expr, changes[k]);
                }
                
                self.prop('href', href);
            });            

            // Move "clone" objects to main content
            $('[id*=clone_]', new_node).each(function() {
                var node = $(this).clone();
                var new_id = node.prop('id').replace('clone_', '');
                if ($('#' + new_id, new_node).length == 0) {
                    node.prop('id', new_id);
                    node.insertAfter($(this));
                }
            });            
    
            // Update elements
            $('[name]', new_node).each(function() {
                var self = $(this);
                var name = self.prop('name');
                var prev_name = name;
                var it = 0;
                var matches = name.match(/(\[\d+\]+)/g);
    
                // Increment array index
                if (matches) {
                    name = name.replace(self.hasClass('cm-image-field') ? image_regex : regex, '$1[#HASH#]'); // Magic... parseInt does not work for $2 in replace method...
                    self.prop('name', name.str_replace('#HASH#', parseInt(RegExp.$2) + counter));
                }
    
                // Set default values
                if (clone == false) {
                    if (self.is('input[type=checkbox], input[type=radio]')) {
                        var default_checked = self.get(0).defaultChecked;
                        
                        if ($.browser.msie) {
                            default_checked = $('input[name="' + prev_name + '"][type=checkbox],input[type=radio]').prop('defaultChecked');
                            self.prop('defaultChecked', default_checked);
                        }
                        
                        self.prop('checked', default_checked ? true : false);
                    } else if (self.is(':input') && self.prop('type') != 'hidden') {
                        if (self.prop('name') != 'submit') {
                            self.val('');

                            // reset select box
                            if (self.prop('tagName').toLowerCase() == 'select') {
                                self.prop('selectedIndex', '');
                            }
                        }
                    }
                }
    
                // Display enabled remove button
                if (name == 'remove') {
                    self.addClass('hidden');
                    self.next().removeClass('hidden');
                }
            });
            
            if (clone == false) {
                $('.cm-select', new_node).each(function() {
                    $('a:first', $(this)).click();
                });
            }
    
            // magic increment for checkbox element classes like add-0 -> add-1 (to fix check_all microformat work)
            $('input[type=checkbox][class]', new_node).each(function() {
                if (this.name == 'check_all') {
                    var m = this.className.match(/cm-check-items-([\w]*)-(\d+)/);
                    $(this).removeClass('cm-check-items-' + m[1] + '-' + m[2]).addClass('cm-check-items-' + m[1] + '-' + (parseInt(m[2]) + counter));
    
                    $('input[type=checkbox].cm-item-' + m[1] + '-' + m[2], new_node).each(function() {
                        $(this).removeClass('cm-item-' + m[1] + '-' + m[2]).addClass('cm-item-' + m[1] + '-' + (parseInt(m[2]) + counter));
                    });
    
                    return false;
                }
            });
    
            // Insert node into the document
            if (before == true) {
                self.before(new_node);
            } else {
                self.after(new_node);
            }

            $('textarea.cm-wysiwyg', self).ceEditor('recover');
                        
            // if node has file uploader, process it
            $('[id^=clean_selection]', new_node).each(function() {

                var type_id = this.id.str_replace('clean_selection', 'type');
                if ($('#' + type_id).val() == 'local' || clone == false){
                    _.fileuploader.clean_selection(this.id);
                }
            });

            // if node has ajax content loader, init it
            $('.cm-ajax-content-more', new_node).each(function() {
                var self = $(this);
                $('#' + self.data('caTargetId')).empty();
                self.show();
                self.appear(function() {
                    $.loadAjaxContent(self);
                }, {
                    one: false,
                    container: '#scroller_' + self.data('caTargetId')
                });
            });

            // init calendar
            $('.cm-calendar', new_node).each(function () {
                $(this).removeClass('hasDatepicker').datepicker(window.calendar_config || {});
            });
            
            $('textarea.cm-wysiwyg', new_node).appear(function() {
                $(this).ceEditor();
            });

            // init autoNumeric plugin
            if (_.area == 'A') {
                $('.cm-numeric', new_node).autoNumeric("init");
            }

            $('.cm-hint', new_node).ceHint('init');
    
            return new_id;
        },
    
        //
        // Remove the tag
        //
        removeNode: function()
        {
            var self = $(this);
            if (!self.prev().length || self.hasClass('cm-first-sibling')) {
                return false;
            }
    
            self.remove();
        }
    });
}(Tygh, Tygh.$));
