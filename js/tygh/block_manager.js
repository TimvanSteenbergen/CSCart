// Class Block manager Menu
function BlockManager_Class()
{
    // Private variables
    var _event = {};
    var _hover_element = {};
    var _init_params = {};
    var _self = this;
    var $ = Tygh.$;
    var _ = Tygh;

    var _params = {
        draggable_width: 250,
        draggable_cursor_pos: 125
    };
    
    // Public variables
    this.menu_type = ''; //container, grid, block
    this.menu_status = 'H'; // "H"idden; "D"isplayed;
    this.inited = false;
    
    // Private functions
    var _setEvent = function(event)
    {
        _event = event;
    };

    var _determineElementType = function(element)
    {
        element = element || _hover_element;
        var type =  '';

        if (element.hasClass(_init_params.container_class)) {
            type = 'container';
        } else if (element.hasClass(_init_params.grid_class)) {
            type = 'grid';
        } else {
            type = 'block';
        }

        return {type: type, id: element.prop('id')};
    };

    var _parseResponse = function(data, params)
    {
        // If we received "id" - apply this id to new element
        if (typeof(data.id) != 'undefined') {
            var new_id = '';
            if (data.mode == 'grid') {
                new_id = 'grid_' + data.id;
            } else if (data.mode == 'snapping') {
                new_id = 'snapping_' + data.id;
            }

            $('#new_element').prop('id', new_id);
        }

        _self.calculateLevels();
    };

    var _snapBlocks = function(block)
    {
        var snapping = {};
        var blocks = block.parent().find('.' + _init_params.block_class);
        
        blocks.each(function() {
            var _block = $(this);
            var id = _block.index();

            snapping[id] = {};

            snapping[id].grid_id =_block.parent().prop('id').replace('grid_', '');
            snapping[id].order = _block.index();
            
            if (_block.hasClass('base-block')) {
                snapping[id].block_id = _block.prop('id').replace('block_', '');
                snapping[id].action = 'add';

                _block.prop('id', 'new_element');
                _block.removeClass('base-block');

            } else {
                snapping[id].snapping_id = _block.prop('id').replace('snapping_', '');
                snapping[id].action = 'update';
            }
        });

        _self.sendRequest('snapping', '', {snappings: snapping});
    };

    var _executeAction = function(action)
    {
        // Init local variables
        var container_title, container, prop_container, href, max_width, container_id;

        // Determine element (container, grid, block)
        var element_type = _determineElementType().type;
        var execute_result = false;

        // Hide element control menu and execute "action"
        _hover_element.parent().find('.cm-popup-box').hide();

        switch (action) {
            case 'properties':
                if (element_type == 'block') {
                    href = 'block_manager.update_block?';
                        href += 'snapping_data[snapping_id]=' + _hover_element.prop('id').replace('snapping_', '');
                        href += '&content_data[grid_id]=' + _hover_element.parent().prop('id').replace('grid_', '');
                        href += '&selected_location=' + (typeof(selected_location) == 'undefined' ? 0 : selected_location);
                        href += '&dynamic_object[object_type]=' + (typeof(dynamic_object_type) == 'undefined' ? '' : dynamic_object_type);
                        href += '&dynamic_object[object_id]=' + (typeof(dynamic_object_id) == 'undefined' ? 0 : dynamic_object_id);

                    prop_container = 'prop_' + _hover_element.prop('id');

                    if ($('#' + prop_container).length == 0) {
                        // Create properties container
                        container_title = _hover_element.find('.block-header > h4').text();

                        if (container_title.length > 0) {
                            container_title = Tygh.tr('editing_block') + ': ' + container_title;
                        } else {
                            container_title = Tygh.tr('editing_block');
                        }
                        
                        container = $('<div id="' + prop_container + '" title="' + _escape(container_title) + '"></div>').appendTo('body');
                    }

                } else if (element_type == 'grid') {
                    var max_width = _self.getMaxWidth();
                    var min_width = _self.getMinWidth();
                    
                    prop_container = 'grid_properties_' + _hover_element.prop('id').replace('grid_', '');

                    href = 'block_manager.update_grid?' + 'grid_data[grid_id]=' + _hover_element.prop('id').replace('grid_', '');
                        href += '&grid_data[max_width]=' + max_width;
                        href += '&grid_data[min_width]=' + min_width;
                        href += '&grid_data[container_id]=' + _hover_element.parents('.container').prop('id').replace('container_', '');
                    
                    if ($('#' + prop_container).length == 0) {
                        // Create properties container

                        container = $('<div id="' + prop_container + '" title="' + _escape(Tygh.tr('editing_grid')) + '"></div>').appendTo('body');
                    }

                } else if (element_type == 'container') {
                    container_title = _hover_element.find('> .grid-control-menu > .grid-control-title').text();

                    href = 'block_manager.update_container?';
                        href += '&container_id=' + _hover_element.prop('id').replace('container_', '');

                    prop_container = 'container_properties_' + _hover_element.prop('id').replace('container_', '');

                    if ($('#' + prop_container).length == 0) {
                        // Create properties container

                        container = $('<div id="' + prop_container + '" title="' + _escape(Tygh.tr('editing_container')) + ': ' + container_title + '"></div>').appendTo('body');
                    }
                }

                $('#' + prop_container).ceDialog('open', {href: fn_url(href)});
                break;

            case 'add-grid':
                var max_width = _self.getMaxWidth(null, true);
                var min_width = _self.getMinWidth();
                
                prop_container = 'grid_properties_new';
                href = 'block_manager.update_grid?';
                href += 'grid_data[max_width]=' + max_width;
                href += 'grid_data[min_width]=' + min_width;

                if (element_type == 'container') {
                    container_id = _hover_element.prop('id').replace('container_', '');

                    href += '&grid_data[container_id]=' + container_id;
                    href += '&grid_data[parent_id]=0';

                    prop_container += '_' + container_id + '_0';

                } else {
                    container_id = _hover_element.parents('.container').prop('id').replace('container_', '');
                    var parent_id = _hover_element.prop('id').replace('grid_', '');

                    href += '&grid_data[container_id]=' + container_id;
                    href += '&grid_data[parent_id]=' + parent_id;

                    prop_container += '_' + container_id + '_' + parent_id;
                }

                if ($('#' + prop_container).length == 0) {
                    // Create properties container

                    container = $('<div id="' + prop_container + '" title="' + _escape(Tygh.tr('adding_grid')) + '"></div>').appendTo('body');
                }
                
                $('#' + prop_container).ceDialog('open', {href: fn_url(href)});
                break;

            case 'add-block':
                href = 'block_manager.block_selection?';
                href += '&grid_id=' + _hover_element.prop('id').replace('grid_', '');
                href += '&selected_location=' + (typeof(selected_location) == 'undefined' ? 0 : selected_location);

                container = $('#block_selection');
                if (!container.length) {
                    container = $('<div id="block_selection" title="' + _escape(Tygh.tr('adding_block_to_grid')) + '"></div>').appendTo('body');
                }

                container.ceDialog('open', {href: fn_url(href)});
                break;

            case 'delete':
                if (confirm(Tygh.tr('text_are_you_sure_to_proceed')) != false) {
                    if (element_type == 'grid') {
                        var data = {
                            snappings: _self.deleteStructure(_hover_element)
                        };

                        _self.sendRequest('grid', 'update', data);

                    } else if (element_type == 'block'){
                        var data = {
                            snappings: _self.deleteStructure(_hover_element)
                        };

                        _self.sendRequest('snapping', 'delete', data);

                        if ($('.bm-block-single-for-location', _hover_element).length) {
                            $('#block_selection').dialog('destroy').remove();
                        }
                    }
                }

                break;
            case 'manage-blocks':
                href = 'block_manager.block_selection?manage=Y';

                container = $('#block_managing');

                if (!container.length) {
                    container = $('<div id="block_managing" title="' + _escape(Tygh.tr('manage_blocks')) + '"></div>').appendTo('body');
                }

                $('#block_managing').ceDialog('open', {href: fn_url(href)});
                break;

            case 'switch':
                if (element_type == 'block') {
                    var button = $('.bm-action-switch', _hover_element);
                    var status = (button.hasClass('switch-off')) ? 'A' : 'D';
                    var dynamic_object = (button.hasClass('bm-dynamic-object')) ? button.data('caBmObjectId') : 0;

                    if (button.hasClass('bm-confirm')) {
                        var text = button.find(".confirm-message").text();

                        if (text == "" || text == 'undefined') {
                            text =Tygh.tr('text_are_you_sure_to_proceed');
                        }

                        if (confirm(text) == false) {
                            return false;
                        } else {
                            button.removeClass("bm-confirm");
                        }
                    }

                    var data = {
                        snapping_id: _hover_element.prop('id').replace('snapping_', ''),
                        object_id: dynamic_object,
                        object_type: dynamic_object_type,
                        status: status,
                        type: 'block'
                    };

                    $.ceAjax('request', fn_url('block_manager.update_status'), {
                        data: data,
                        callback: _parseResponse,
                        method: 'get'
                    });

                    if (status == 'A') {
                        button.removeClass('switch-off');
                        _hover_element.removeClass('block-off');
                        _hover_element.data('caStatus', 'active');
                    } else {
                        button.addClass('switch-off');
                        _hover_element.addClass('block-off');
                        _hover_element.data('caStatus', 'disabled');
                    }

                    execute_result = true;

                } else if (element_type == 'grid') {
                    var status = _hover_element.hasClass('grid-off') ? 'A' : 'D';
                    
                    var data = {
                        grid_id: _hover_element.prop('id').replace('grid_', ''),
                        status: status,
                        type: 'grid'
                    };

                    $.ceAjax('request', fn_url('block_manager.update_status'), {
                        data: data,
                        callback: _parseResponse,
                        method: 'get'
                    });

                    if (status == 'A') {
                        _hover_element.removeClass('grid-off');
                        _hover_element.data('caStatus', 'active');
                    } else {
                        _hover_element.addClass('grid-off');
                        _hover_element.data('caStatus', 'disabled');
                    }

                    _self.recheckBlockStatuses(_hover_element);

                    execute_result = true;
                } else if (element_type == 'container') {
                    var status = _hover_element.hasClass('container-off') ? 'A' : 'D';
                    
                    var data = {
                        container_id: _hover_element.prop('id').replace('container_', ''),
                        status: status,
                        type: 'container'
                    };

                    $.ceAjax('request', fn_url('block_manager.update_status'), {
                        data: data,
                        callback: _parseResponse,
                        method: 'get'
                    });

                    if (status == 'A') {
                        _hover_element.removeClass('container-off');
                        _hover_element.data('caStatus', 'active');
                    } else {
                        _hover_element.addClass('container-off');
                        _hover_element.data('caStatus', 'disabled');
                    }

                    _self.recheckBlockStatuses(_hover_element);

                    execute_result = true;
                }
                
                break;
            
            case 'control-menu':
                _hover_element.find('> .bm-control-menu .bm-drop-menu').show();
                break;

            default: break;
        }

        return execute_result;

    };
    
    var _escape = function(str)
    {
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    };

    // Public functions
    this.init = function(containers, params)
    {
        if (this.inited) {
            return true;
        }
        
        this.inited = true;
        $(containers).disableSelection();
        
        params.connectWith = '.cm-sortable-grid';
        params.tolerance = 'pointer';

        params.update = function(event, ui)
        {
            if (ui.sender == null) {
                _snapBlocks($(ui.item));
                _self.checkMenuItems($(ui.item).parent());
            }

            if (ui.sender) {
                var placeholder = $(this);
                ui.item.removeClass('pull-left').removeClass('pull-right');

                if (placeholder.hasClass('bm-left-align')) {
                    ui.item.addClass('pull-left');
                    if (ui.item.width() > ui.item.parent().width()) {
                        ui.item.width(ui.item.parent().width() - 10);
                    }
                    
                } else if (placeholder.hasClass('bm-right-align')) {
                    ui.item.addClass('pull-right');
                    if (ui.item.width() > ui.item.parent().width()) {
                        ui.item.width(ui.item.parent().width() - 10);
                    }
                } else {
                    ui.item.width(ui.item.parent().width() - 10);
                }

                if (placeholder.hasClass('grid-off') || placeholder.parents('.grid-off').length > 0 || placeholder.parents('.container-off').length > 0) {
                    ui.item.addClass('block-off');

                } else if (ui.item.data('caStatus') == 'active') {
                    ui.item.removeClass('block-off');
                }

                _self.checkMenuItems($(ui.sender));
            }

            _self.setBlockHeaderWidth(ui.item);
        };

        params.start = function(event, ui)
        {
            ui.item.addClass('ui-draggable-block');

            ui.item.css('width', _params.draggable_width + 'px');
            ui.item.find('.block-header-title').css('width','auto');

            fn_ui_update_placeholder(ui, $(this));
        };

        params.beforeStop = function(event, ui)
        {
            ui.item.removeClass('ui-draggable-block');
            ui.item.css('max-width', '');
        };

        params.over = function(event, ui)
        {
            fn_ui_update_placeholder(ui, $(this));
        };

        params.stop = function(event, ui)
        {
            _self.buildMenu(ui.item);
        };
        
        _init_params = params;
        _init_params.containers = containers;
        
        /*
            We have 2 function to parse actions:
                1) Block manager control elements, like "Add grid", "Properties", etc. 
                    Controls by class: cm-action

                2) When we click "Add block" or "Manage blocks". Process clicking on block in a new popup window with blocks. 
                    Controls by class: cm-add-block
        */

        $(Tygh.doc).on('click', '.cm-action', function(e) {
            jelm = $(e.currentTarget).parents('.bm-control-menu').parent();
            
            _hover_element = jelm;
            _setEvent(e);

            var action = $(e.currentTarget).prop('class').match(/bm-action-([0-9a-zA-Z-]+)/i)[1];

            return _executeAction(action);
        });

        $(Tygh.doc).on('click', '.cm-remove-block', function(e) {
            if (confirm(Tygh.tr('text_are_you_sure_to_proceed')) != false) {
                var block_id = $(this).parent().find('input[name="block_id"]').val();
    
                _self.sendRequest('block', 'delete', {block_id: block_id});

                $(this).parent().remove();
            }

            return false;
        });

        $(Tygh.doc).on('click', '.cm-add-block', function(e) {
            /*
                Adding new block functionality
            */
            var action = $(this).prop('class').match(/bm-action-([a-zA-Z0-9-_]+)/)[1];
            
            if (action == 'new-block') {
                var is_manage = $(this).hasClass('bm-manage');
                var block_type = $(this).find('input[name="block_data[type]"]').val();

                if (is_manage) {
                    var grid_id = 0;
                } else {
                    var grid_id = _hover_element.prop('id').replace('grid_', '');
                }

                var href = 'block_manager.update_block?';
                    href += 'block_data[type]=' + block_type;
                    href += '&snapping_data[grid_id]=' + grid_id;
                    href += '&selected_location=' + (typeof(selected_location) == 'undefined' ? 0 : selected_location);

                var prop_container = 'new_block_' + block_type + '_' + grid_id;
                
                if ($('#' + prop_container).length == 0) {
                    // Create properties container
                    var container = $('<div id="' + prop_container + '"></div>').appendTo('body');
                }

                $('#' + prop_container).ceDialog('open', {href: fn_url(href), title: Tygh.tr('add_block') + ': ' + $(this).find('strong').text()});

            } else if (action == 'existing-block') {
                var is_manage = $(this).hasClass('bm-manage');
                var block_id = $(this).find('input[name="block_id"]').val();
                var block_type = $(this).find('input[name="type"]').val();
                var grid_id = $(this).find('input[name="grid_id"]').val();
                var block_title = $(this).find('.select-block-description > strong').text();

                if (is_manage) {
                    var href = 'block_manager.update_block?';
                        href += 'block_data[type]=' + block_type;
                        href += '&block_data[block_id]=' + block_id;
                        href += '&selected_location=' + (typeof(selected_location) == 'undefined' ? 0 : selected_location);

                    var prop_container = 'new_block_' + block_type + '_block_' + block_id;
                    
                    if ($('#' + prop_container).length == 0) {
                        // Create properties container
                        var container = $('<div id="' + prop_container + '"></div>').appendTo('body');
                    }

                    $('#' + prop_container).ceDialog('open', {href: fn_url(href), title: Tygh.tr('editing_block') + ': ' + $(this).find('strong').text()});

                } else {
                    var elm = $('<div class="block base-block" data-block-id="' + block_id + '" id="block_' + block_id + '" title="' + block_title + '">' + $('.base-block').html() + '</div>');
                    $('.block-header-title', elm).text(block_title);
                    $('.block-header-icon', elm).addClass('bmicon-' + block_type.replace('_', '-'));
                    $('.block-header', elm).prop('title', block_title);

                    // Check if the same block already exists in the grid
                    var blocks = _hover_element.find('.' + _init_params.block_class);
                    var exists = false;

                    blocks.each(function(){
                        if ($(this).data('block-id') == block_id) {
                            exists = true;

                            return false;
                        }
                    });

                    if (exists) {
                        $.ceNotification('show', {
                            type: 'E',
                            title: _.tr('error'),
                            message: _.tr('block_already_exists_in_grid')
                        });

                        return false;
                    }


                    if (_hover_element.find('.' + _init_params.block_class + ':last').length) {
                        elm.insertAfter(_hover_element.find('>.' + _init_params.block_class + ':last'));
                    } else {
                        elm.prependTo(_hover_element);
                    }

                    _snapBlocks(elm);

                    if (_hover_element.hasClass('bm-right-align')) {
                        elm.addClass('pull-right');
                    } else if (_hover_element.hasClass('bm-left-align')) {
                        elm.addClass('pull-left');
                    }

                    _self.buildMenu(elm);
                    _self.checkMenuItems(elm.parent());
                    _self.setBlockHeaderWidth(elm);

                    var dlg = $.ceDialog('get_last');
                    dlg.ceDialog('close');

                    var is_single_for_location = $(this).hasClass('bm-block-single-for-location');
                    if (is_single_for_location) {
                        elm.find('.bm-action-delete').addClass('bm-block-single-for-location'); // mark remove button
                        dlg.dialog('destroy').remove();
                    }
                }
            }

            
        });

        // Init sortable zones
        _self.calculateLevels();

        // Correct control menues
        _self.checkMenuItems($('.' + params.grid_class, params.parent));

        $('.' + _init_params.block_class, _init_params.parent).each(function(){
            _self.setBlockHeaderWidth($(this));
        });

        // Disable/Enable blocks in depends on the Grid statuses
        $('.grid-off, .container-off    ').each(function(){
            _self.recheckBlockStatuses($(this));
        });
        
    };
    
    this.recalculateClearLines = function(parent)
    {
        // Re-create "clearfix" divs to make correct grid lines
        var clears_data = {
            containers: {},
            grids: {}
        };

        // Remove all "clear" div's
        $('.' + _init_params.container_class + ' div.clearfix', parent || _init_params.parent).remove();

        // We need only first element of each grid blocks
        $('.' + _init_params.grid_class + ':first-child', parent || _init_params.parent).each(function(){
            var jelm = $(this);
            var parent_type = _determineElementType(jelm.parent());

            if (parent_type.type == 'container') {
                var max_width = parseInt(jelm.parent().prop('class').match(/container_([0-9]+)/i)[1]);
            } else {
                var max_width = parseInt(jelm.parent().prop('class').match(/grid_([0-9]+)/i)[1]);
            }

            var current_width = 0;
            var last_grid = {};

            jelm.parent().find('>.' + _init_params.grid_class).each(function(){
                var grid = $(this);
                var grid_width = parseInt(grid.prop('class').match(/grid_([0-9]+)/i)[1]);
                var grid_prefix = grid.prop('class').match(/prefix_([0-9]+)/i);
                var grid_suffix = grid.prop('class').match(/suffix_([0-9]+)/i);
                
                grid_prefix = (grid_prefix == null) ? 0 : parseInt(grid_prefix[1]);
                grid_suffix = (grid_suffix == null) ? 0 : parseInt(grid_suffix[1]);

                grid_width += grid_prefix + grid_suffix;
                
                if (current_width + grid_width > max_width) {
                    if (grid.prev().length > 0) {
                        var clear_id = grid.prev().prop('id').replace('grid_', '');

                        if (clear_id != '') {
                            clears_data.grids[clear_id] = true;
                        }
                        $('<div class="clearfix"></div>').insertBefore(grid);
                    }

                    current_width = grid_width;

                } else {
                    current_width += grid_width;
                }

                last_grid = grid;
            });

            if (last_grid.length > 0) {
                var clear_id = last_grid.prop('id').replace('grid_', '');
                if (typeof(clears_data.grids[clear_id]) == 'undefined') {
                    clears_data.grids[clear_id] = true;
                    $('<div class="clearfix"></div>').insertAfter(last_grid);
                }
            }
            
        });

        $('.' + _init_params.container_class, _init_params.parent).each(function(){
            var container_id = $(this).prop('id').replace('container_', '');
            clears_data.containers[container_id] = true;
        });

        return clears_data;
    };

    this.sendRequest = function(mode, action, data)
    {
        if (mode == 'grid') {
            data.clears_data = _self.recalculateClearLines();
        }
        
        var controller = typeof(data['controller']) == 'undefined' ? 'block_manager.' : data['controller'] + '.';

        $.ceAjax('request', fn_url(controller + mode + '.' + action), {
            data: data,
            callback: _parseResponse,
            method: 'post'
        });
    };

    this.calculateLevels = function()
    {
        // Re-init sortable zones
        $(_init_params.containers + ',.cm-sortable-container').each(function(){
            if ($(this).hasClass('ui-sortable')) { //is inited
                $(this).sortable('destroy');
            }
        });

        $('.' + _init_params.grid_class, _init_params.parent).each(function(){
            var jelm = $(this);
            var level = _self.getLevel($(this));

            jelm.prop('class', jelm.prop('class').replace(/level-[0-9]+/, ''));
            jelm.addClass('level-' + level);

            if (jelm.find('.' + _init_params.grid_class).length == 0) {
                jelm.addClass('cm-sortable-grid');
            } else {
                jelm.removeClass('cm-sortable-grid');
            }
        });

        $('.' + _init_params.container_class + ',.' + _init_params.grid_class, _init_params.parent).each(function(){
            var jelm = $(this);

            _self.calculateAlphaOmega(jelm);
        });
        
        // Re-init droppable zone
        _init_params.cursorAt= { left: _params.draggable_cursor_pos };
        $('.cm-sortable-grid').sortable(_init_params);

        $('.' + _init_params.container_class + ',.' + _init_params.grid_class + ':not(.cm-sortable-grid)').sortable({
            items: '>.' + _init_params.grid_class,
            handle: ".bm-control-menu",
            tolerance: 'pointer',
            update: function(event, ui) {
                var grid = $(ui.item);
                var parent_container = $(ui.item).parent();
                var grid_id = grid.prop('id').replace('grid_', '');

                _self.calculateAlphaOmega(parent_container);

                var grids_snapping = BlockManager.snapGrid({grid_id: grid_id});
                _self.sendRequest('grid', 'update', grids_snapping);
            },
            start: function(event, ui) {
                var grid = $(ui.item);

                if (grid.hasClass('alpha')) {
                    grid.data('alpha', true);
                }
                if (grid.hasClass('omega')) {
                    grid.data('omage', true);
                }
                grid.removeClass('alpha').removeClass('omega');
                $('div.clearfix', grid.parent()).remove();
            },
            stop: function(event, ui) {
                var grid = $(ui.item);

                if (grid.data('alpha')) {
                    grid.addClass('alpha');
                    grid.data('alpha', false);
                }
                if (grid.data('omega')) {
                    grid.addClass('omega');
                    grid.data('omega', false);
                }
                
                var parent_container = grid.parent();
                _self.calculateAlphaOmega(parent_container);
            },
            change: function(event, ui) {
                var grid = $(ui.item);
                var parent_container = grid.parent();

                _self.calculateAlphaOmega(parent_container);
            }
        });
    };
    
    this.getLevel = function(elm)
    {
        var level = 1;
        while (!elm.parent().hasClass(_init_params.container_class)) {
            elm = elm.parent();
            level++;
        }
        
        return level;
    };
    
    this.calculateAlphaOmega = function(element)
    {
        var items = element.children('.' + _init_params.grid_class);

        if (element.hasClass(_init_params.container_class)) {
            var width = element.prop('class').match(/container_([0-9]+)/i)[1];
        } else {
            var width = element.prop('class').match(/grid_([0-9]+)/i)[1];
        }
        
        var line_width = 0;
        var index = 1;
        var alpha = false;
        var omega = false;
        var prev_elm = null;

        items.each(function(){
            var jelm = $(this);

            if (jelm.hasClass('ui-sortable-helper')) {
                return jelm;
            }

            var elm_width = parseInt(jelm.prop('class').match(/grid_([0-9]+)/i)[1]);
            var elm_prefix = jelm.prop('class').match(/prefix_([0-9]+)/i);
            var elm_suffix = jelm.prop('class').match(/suffix_([0-9]+)/i);
            
            elm_prefix = (elm_prefix == null) ? 0 : parseInt(elm_prefix[1]);
            elm_suffix = (elm_suffix == null) ? 0 : parseInt(elm_suffix[1]);
            
            elm_width += elm_prefix + elm_suffix;
            
            jelm.removeClass('alpha').removeClass('omega');
            
            if (alpha === false) {
                jelm.addClass('alpha');
                alpha = true;
            }
            
            if ((line_width + elm_width) == width) {
                jelm.addClass('omega');
                alpha = false;
                
                line_width = 0;

            } else if ((line_width + elm_width) > width) {
                jelm.addClass('alpha');

                if (prev_elm != null) {
                    prev_elm.addClass('omega');
                }

                if (elm_width != width) {
                    alpha = true;
                } else {
                    alpha = false;
                }

                line_width = elm_width;

            } else {
                line_width += elm_width;
            }

            if (index == items.length) {
                jelm.addClass('omega');
            }

            index++;
            prev_elm = jelm;
        });
    };
    
    this.recheckBlockStatuses = function(elm)
    {
        if (elm.hasClass('grid-off') || elm.hasClass('container-off')) {
            elm.find('.' + _init_params.block_class).addClass('block-off');
        } else {
            elm.find('.' + _init_params.block_class).each(function(){
                if ($(this).data('caStatus') == 'active') {
                    $(this).removeClass('block-off');
                }
            });
        }
    }

    this.getPropertyValue = function(property, elm)
    {
        var value = '';
        elm = elm || _hover_element;
        
        if (property == 'columns') {
            value = elm.prop('class').match(/container_/) ? parseInt(elm.prop('class').match(/container_([0-9]+)/i)[1]) : 0;
            
        } else if (property == 'width') {
            value = elm.prop('class').match(/grid_/) ? parseInt(elm.prop('class').match(/grid_([0-9]+)/i)[1]) : 0;
            
        } else if (property == 'alpha') {
            value = elm.prop('class').match(/alpha/i) ? '1' : '0';
            
        } else if (property == 'omega') {
            value = elm.prop('class').match(/omega/i) ? '1' : '0';
        
        } else if (property == 'prefix') {
            value = elm.prop('class').match(/prefix_/) ? parseInt(elm.prop('class').match(/prefix_([0-9]+)/i)[1]) : 0;
            
        } else if (property == 'suffix') {
            value = elm.prop('class').match(/suffix_/) ? parseInt(elm.prop('class').match(/suffix_([0-9]+)/i)[1]) : 0;
        }
        
        return value;
    };
    
    this.saveProperties = function(type, data)
    {
        switch (type) {
            case 'grid':
                if (!parseInt(data['grid_id'])) {
                    elm = $('<div class="grid" id="new_element">' + $('.base-grid').html() + '</div>');
                    if (_hover_element.find('.' + _init_params.grid_class + ':last').length) {
                        elm.insertAfter(_hover_element.find('>.' + _init_params.grid_class + ':last'));
                    } else {
                        elm.prependTo(_hover_element);
                    }
                } else {
                    elm = _hover_element;
                }

                for (var key in data) {
                    var value = data[key];
                    if (key == 'width') {
                        var elm_class = elm.prop('class').replace(/grid_[0-9]+/, ''); //Get element class without "grid_N" class
                        elm_class += ' grid_' + value;
                        elm.prop('class', elm_class);

                    } else if (key == 'prefix') {
                        var elm_class = elm.prop('class').replace(/prefix_[0-9]+/, ''); //Get element class without "prefix_N" class
                        if (value > 0) {
                            elm_class += ' prefix_' + value;
                        }
                        elm.prop('class', elm_class);

                    } else if (key == 'suffix') {
                        var elm_class = elm.prop('class').replace(/suffix_[0-9]+/, ''); //Get element class without "suffix_N" class
                        if (value > 0) {
                            elm_class += ' suffix_' + value;
                        }
                        elm.prop('class', elm_class);

                    } else if (key == 'content_align') {
                        blocks = elm.find('.' + _init_params.block_class);
                        if (value == 'LEFT') {
                            blocks.removeClass('pull-right').addClass('pull-left');

                        } else if (value == 'RIGHT') {
                            blocks.removeClass('pull-left').addClass('pull-right');

                        } else {
                            blocks.removeClass('pull-left').removeClass('pull-right');
                        }
                    }
                }

                // Rebuild menu for new element according to the new settings
                _self.buildMenu(elm);
                _self.checkMenuItems(elm.parent());

                break;

            case 'container':
                for (var key in data) {
                    var value = data[key];

                    if (key == 'container_data[width]') {
                        var elm_class = _hover_element.prop('class').replace(/container_[0-9]+/, ''); //Get element class without "container_N" class
                        elm_class += ' container_' + value;
                        _hover_element.prop('class', elm_class);
                    }
                }
                break;
            
            default: break;
        }

        _self.calculateLevels();
        _self.buildMenu(_hover_element);

        $('.' + _init_params.block_class, _hover_element).each(function(){
            _self.buildMenu($(this));
            _self.setBlockHeaderWidth($(this));
        });

        return data;
    };

    this.deleteStructure = function(element)
    {
        element = element || _hover_element;
        var elm_data = _determineElementType(element);

        if (elm_data.type == 'grid') {
            var snappings = {};
            var grids = $(element).parent().find('.' + _init_params.grid_class);

            grids.each(function(){
                jelm = $(this);
                var grid_id = jelm.prop('id').replace('grid_', '');
                var action = (grid_id == element.prop('id').replace('grid_', '')) ? 'delete' : 'update';

                snappings[grid_id] = {
                    action: action,
                    grid_data: {
                        grid_id: grid_id
                    }
                };


            });
            
            // Delete grid and recalculate levels and alpha/omega parameters
            var parent_grid = element.parent();

            element.remove();
            _self.calculateLevels();
            _self.checkMenuItems(parent_grid);

            for (var i in snappings) {
                if (snappings[i].action == 'delete') {
                    $('#grid_' + snappings[i].grid_data.grid_id).remove();
                } else {
                    jelm = $('#grid_' + snappings[i].grid_data.grid_id);
                    if (jelm.length > 0) {
                        // We can remove parent grid with other grids inside
                        snappings[i].grid_data.alpha = _self.getPropertyValue('alpha', jelm);
                        snappings[i].grid_data.omega = _self.getPropertyValue('omega', jelm);
                    }
                }
            }
            
            return snappings;

        } else if (elm_data.type == 'block') {
            var snappings = {
                0: {
                    action: 'delete',
                    snapping_id: element.prop('id').replace('snapping_', '')
                }
            };

            var parent_grid = $('#snapping_' + snappings[0].snapping_id).parent();
            $('#snapping_' + snappings[0].snapping_id).remove();
            
            _self.checkMenuItems(parent_grid);

            return snappings;
        }

        return false;
    };
    
    this.getMaxWidth = function(elm, is_new)
    {
        var width = 0;

        elm = elm || _hover_element;
        is_new = is_new || false;

        if (elm.hasClass(_init_params.block_class)) {
            elm = elm.parent();
        }
        
        if (elm.hasClass(_init_params.container_class)) {
            width = parseInt(elm.prop('class').match(/container_([0-9]+)/i)[1]);
            
        } else if (elm.hasClass(_init_params.grid_class)) {
            if (is_new) {
                width = parseInt(elm.prop('class').match(/grid_([0-9]+)/i)[1]);
            } else {
                if (elm.parent().hasClass('container')) {
                    width = parseInt(elm.parent().prop('class').match(/container_([0-9]+)/i)[1]);
                } else {
                    width = parseInt(elm.parent().prop('class').match(/grid_([0-9]+)/i)[1]);
                }
            }
        }
        
        return width;
    };

    this.getMinWidth = function(elm)
    {
        var width = 1;
        elm = elm || _hover_element;

        var grids = elm.find('.' + _init_params.grid_class);
        if (grids.length == 0) {
            return width;
        }

        grids.each(function(){
            var _width = parseInt($(this).prop('class').match(/grid_([0-9]+)/i)[1]);

            if (_width > width) {
                width = _width;
            }
        });

        return width;
    }


    this.snapGrid = function(grid)
    {
        if (parseInt(grid.grid_id)) {
            var selector = '#grid_' + grid.grid_id;
        } else {
            var selector = '#new_element';
        }

        var snapping = {};
        var grids = $(selector).parent().find('>.' + _init_params.grid_class);

        grids.each(function() {
            var _grid = $(this);
            var id = _grid.index();

            snapping[id] = {};
            snapping[id].grid_data = {};

            if (_grid.prop('id') == 'new_element') {
                snapping[id].action = 'add';
                for (var i in grid) {
                    snapping[id].grid_data[i] = grid[i];
                }
            } else {
                if (grid['grid_id'] == _grid.prop('id').replace('grid_', '')) {
                    // Move data from form to updating snapping data
                    snapping[id].grid_data = grid;
                }

                snapping[id].action = 'update';
                snapping[id].grid_data.grid_id = _grid.prop('id').replace('grid_', '');

            }
            
            snapping[id].grid_data.alpha = _self.getPropertyValue('alpha', _grid);
            snapping[id].grid_data.omega = _self.getPropertyValue('omega', _grid);
            snapping[id].grid_data.order = id;
        });

        return {snappings: snapping};
    };

    this.buildMenu = function(element)
    {
        // You must control this functionality when changing anything in control menu

        // Rebuild menu if width of element doesn't allow to use "full width" menu
        var type = _determineElementType(element).type;

        var width = 0;
        if (type == 'grid') {
            width = _self.getPropertyValue('width', element);

            // Change header title from "GRID X" to "GRID Y"
            var title = $('> .grid-control-menu > .grid-control-title', element).html();

            title = title.replace(/[0-9]+/, width);
            $('> .grid-control-menu > .grid-control-title', element).html(title);

        } else if (type == 'block') {
            width = _self.getPropertyValue('width', element.parent());
        }

        if (width >= 1 && width <= 2) {
            $('> .bm-full-menu', element).hide();
            $('> .bm-compact-menu', element).show();
            $('> .grid-control-menu > .grid-control-title', element).hide();
        } else if (width > 0) {
            $('> .bm-full-menu', element).show();
            $('> .bm-compact-menu', element).hide();
            $('> .grid-control-menu > .grid-control-title', element).show();
        }
        
        return true;
    };

    this.checkMenuItems = function(elements)
    {
        elements.each(function(){
            var jelm = $(this);

            var has_blocks = $('> .' + _init_params.block_class, jelm).length > 0 ? true : false;
            var has_grids = $('> .' + _init_params.grid_class, jelm).length > 0 ? true : false;
            
            $('> .bm-control-menu .bm-action-add-block, > .bm-control-menu .bm-action-add-grid', jelm).show();
            if (has_blocks) {
                $('> .bm-control-menu .bm-action-add-grid', jelm).hide();
            }

            if (has_grids) {
                $('> .bm-control-menu .bm-action-add-block', jelm).hide();
            }
        });
    };

    this.setBlockHeaderWidth = function(block)
    {
        var grid = block.parent().prop('class').match(/grid_([0-9]+)/i);
        if (grid !== null) {
            var width = parseInt(grid[1]);
            
            if (width == 1) {
                $('.block-header-icon,.block-header-title', block).hide();
            } else {
                $('.block-header-icon,.block-header-title', block).show();
            }

            $('.block-header-title', block).css('width', '');
            $('.block-header-title', block).css('width', block.width() - 55 + 'px');
        }
    };
}


(function(_, $) {

    $.ceEvent('on', 'ce.formpost_grid_update_form', function(frm, c_elm) {
        var form_data = frm.serializeObject();
        form_data = BlockManager.saveProperties('grid', form_data);
        
        var grids_snapping = BlockManager.snapGrid(form_data);

        BlockManager.sendRequest('grid', 'update', grids_snapping);

        return false;
    });
       
    $.ceEvent('on', 'ce.formpost_container_update_form', function(frm, c_elm) {
        var form_data = frm.serializeObject();
        form_data = BlockManager.saveProperties('container', form_data);

        BlockManager.sendRequest('container', 'update', form_data);

        return false;
    });
    
}(Tygh, Tygh.$));


function fn_ui_update_placeholder(ui, element)
{
    ui.placeholder.removeClass('pull-right').removeClass('pull-left');

    if (element.hasClass('bm-right-align')) {
        ui.placeholder.addClass('pull-right');
    } else if (element.hasClass('bm-left-align')) {
        ui.placeholder.addClass('pull-left');
    }

    return true;
}