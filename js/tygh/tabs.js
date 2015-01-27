(function(_, $) {

    (function($){

        function _getTabTools(id)
        {
            return $('.cm-tab-tools[id^="tools_' + id + '"]');
        }

        function _getTabIds(id)
        {
            var result_ids = ['content_' + id];
            var additional_ids = $('#content_' + id).data('caTabTargetId');
            if (additional_ids) {
                result_ids.push(additional_ids);
            }
            _getTabTools(id).each(function() {
                result_ids.push($(this).prop('id'));
            });

            return result_ids.join(',');
        }

        function _switchTab(tab_id, show)
        {
            var id_obj = $('#content_' + tab_id);
            var tab_tools = _getTabTools(tab_id);

            if (show) {
                id_obj.show();
                tab_tools.show();
                $('.cm-j-tabs', id_obj).ceTabs('resize');

                if (id_obj.hasClass('cm-hide-save-button')) {
                    $('.cm-save-buttons').hide();
                } else {
                    $('.cm-save-buttons').show();
                }

                $.ceEvent('trigger', 'ce.tab.show');
            } else {
                id_obj.hide();
                tab_tools.hide();
            }

            return true;
        }

        function _checkSize(tabs_elm)
        {
            if (_.area == 'C') {
                return false;
            }

            var ul = $('ul:first', tabs_elm);
            var sub_tab = $('.cm-subtabs', ul);

            if (sub_tab.length) {
                // if submenu already defined move subtabs back
                $('li', sub_tab).each(function() {
                    $(this).appendTo(ul);
                    // remove cloned subtab
                    $('.cm-subtab-item').remove();
                });
                sub_tab.appendTo(ul);
            }

            if (!sub_tab.length) {
                sub_tab = $('<li class="dropdown cm-subtabs subtab dropleft pull-right">' +
                    '<a class="dropdown-toggle" data-toggle="dropdown">' + _.tr('more') + '<b class="caret icon-down-dir ty-icon-down-dir"></b></a>' +
                    '<ul class="dropdown-menu">' +
                    '</ul>' +
                    '</li>').appendTo(ul);
            }

            // show or hide submenu on hover
            $('.cm-subtabs').hover(
                function () {
                    $(this).addClass('open');
                },
                function () {
                    $(this).removeClass('open');
                }
            );


            var list = $('li:not(.cm-subtabs)', ul);
            var sub_menu = $('ul', sub_tab);
            var sub_width = sub_tab.outerWidth();
            // set custom width
            var max_width = $(tabs_elm).data('caWidth') ? $(tabs_elm).data('caWidth') : 960;
            var total_width = 0;
            var total_items = list.length;
            var is_sub = false;
            var offset_width = 0,
                offset = 100; // min width of the active tab
            sub_tab.hide();

            // If the sub tab is selected then the display text
            $('.dropdown-toggle', sub_tab).html(_.tr('more') + '<b class="caret icon-down-dir ty-icon-down-dir"></b>');

            list.each(function(index) {
                var self = $(this);
                total_width += self.outerWidth();
                self.removeClass('pull-right');

                // check if further items should be moved to submenu
                if (!is_sub) {
                    if (total_width > max_width) {
                        // current item does not fit the width
                        is_sub = true;
                        offset_width = max_width - total_width + self.outerWidth();
                    } else if ((index + 1) < total_items) {
                        // not last item
                        if ((total_width + sub_width + offset) > max_width) {
                            // both submenu and current item will not fit
                            offset_width = max_width - total_width + self.outerWidth();
                            is_sub = true;
                        }
                    }
                }
                if (is_sub) {
                    self.appendTo(sub_menu);
                    if (self.hasClass('active')) {
                        var elem = $(self).clone();

                        $('.dropdown-toggle', sub_tab).html('<b class="caret icon-down-dir ty-icon-down-dir"></b>');
                        elem.addClass('active pull-right subtab nowrap cm-subtab-item').css('max-width', offset_width - sub_tab.outerWidth());
                        $(sub_tab).after(elem);
                    }
                }
            });

            if (is_sub) {
                sub_tab.show();
            }
        }

        function _cloneTools(tab_id, prev_id)
        {
            if (!tab_id || !prev_id) {
                return;
            }

            var _prev_tools = _getTabTools(prev_id);
            _prev_tools.each(function() {
                var self = $(this);
                var _new_id = self.prop('id').replace(prev_id, tab_id);

                if (!$('#' + _new_id).length) {
                    var _new_tool = self.clone();
                    _new_tool.children().remove();
                    _new_tool.prop('id', _new_id).hide().appendTo(self.parent());
                }
            });

        }

        var methods = {
            init: function() {
                $(this).each(function() {
                    var tabs_elm = $(this);

                    var bt_tabs = $('[data-toggle="tab"], [data-toggle="pill"]', tabs_elm)
                    if (bt_tabs.length) {
                        bt_tabs.on('shown', function() {
                            // fix bootstrap tabs switch
                            _checkSize(tabs_elm);
                        });
                    }

                    //Setup Tabs
                    var ul = $('ul:first', tabs_elm);
                    var list = $('li', ul).on('click', function(e)
                    {
                        var elm = $(this);
                        var tab_id = elm.prop('id');

                        if (!tab_id) {
                            return true;
                        }

                        // we set selected_section to keep active tab opened after form submit
                        // we do it for all forms to fix settings_dev situation: forms under tabs
                        if ($(tabs_elm).hasClass('cm-track')) {
                            $('input[name=selected_section]').val(tab_id);
                        }

                        if (elm.hasClass('cm-js') == false) {
                            return true;
                        }

                        var active_id = $('li.active:first', ul).prop('id');

                        $('li', ul).each(function() {
                            var self= $(this);
                            self.removeClass('active');
                            _switchTab(self.prop('id'), false);
                        });

                        //Select clicked tab and show content
                        elm.addClass('active');
                        var sub_tab = elm.parents('.cm-subtabs', ul);

                        _checkSize(tabs_elm);

                        if (sub_tab.length && elm.hasClass('cm-no-highlight')) {
                            sub_tab.addClass('active');
                        }
                        var id = 'content_' + tab_id;
                        if (elm.hasClass('cm-ajax') && $('#' + id).length == 0) {
                            // Create tab content if it is not exist
                            tabs_elm.after('<div id="' + id + '"></div>');

                            _cloneTools(tab_id, active_id);
                            $.ceAjax('request', $('a', elm).prop('href'), {result_ids: _getTabIds(tab_id), callback: function (data){
                                _switchTab(tab_id, true);
                            }});
                        } else {
                            _switchTab(tab_id, true);
                        }

                        e.preventDefault();
                        return true;
                    });

                    //Select default tab
                    var test;
                    if ((test = list.filter('.active')).length) {
                        test.trigger('click'); //Select tab with class 'active'
                    } else {
                        test = list.filter(':first').trigger('click'); //Select first tab
                    }

                    // create similar active tab tools
                    var active_id = test.prop('id');

                    $('li.cm-ajax.cm-js').each(function(){
                        var self = $(this);
                        var tab_id = self.prop('id');

                        // Check if the active content needs to be loaded
                        if (self.hasClass('active')) {
                            content = $('#content_' + tab_id).html().replace(/<!--.*?-->/, '').replace(/(^\s+|\s+$)/, '');
                            if (content.length) {
                                return true;
                            }
                        }

                        if (!self.data('passed') && $('a', self).prop('href')) {
                            self.data('passed', true);
                            var id = 'content_' + tab_id;
                            // Check if block already exists
                            var block = $('#' + id);

                            if (!block.length) {
                                self.parents('.cm-j-tabs').eq(0).next().prepend('<div id="' + id + '"></div>');
                                block = $('#' + id);
                            }



                            if (!self.hasClass('active')) {
                                block.hide();
                            }

                            _cloneTools(tab_id, active_id);
                            $.ceAjax('request', $('a', self).prop('href'), {
                                result_ids: _getTabIds(tab_id),
                                hidden: true,
                                repeat_on_error: true
                            });
                        }
                    });

                    // move exceed link to sub_menu
                    _checkSize(tabs_elm);

                    return true;
                });

                $.ceEvent('trigger', 'ce.tab.init');

            },

            resize: function() {
                $(this).each(function() {
                    _checkSize($(this));
                });
            }
        };

        $.fn.ceTabs = function(method) {
            if (methods[method]) {
                return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
            } else if ( typeof method === 'object' || ! method ) {
                return methods.init.apply(this, arguments);
            } else {
                $.error('ty.tabs: method ' +  method + ' does not exist');
            }
        };

    })($);

}(Tygh, Tygh.$));
