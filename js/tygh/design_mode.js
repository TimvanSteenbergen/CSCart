function fn_get_offset(elem, skip_relative)
{
    var $ = Tygh.$;
    var w = elem.offsetWidth;
    var h = elem.offsetHeight;

    var l = 0;
    var t = 0;

    while (elem) {
        if (skip_relative && $(elem).css('position') == 'relative') {
            break;
        }
        l += elem.offsetLeft;
        t += elem.offsetTop;
        elem = elem.offsetParent;
    }

    return {"left": l, "top": t, "width": w, "height": h};
}

function fn_set_overlay(template)
{
    var $ = Tygh.$;
    var offset = 20;
    template.each(function(){
        $(this).css('display', 'block');
        var template_offset = fn_get_offset(this);
        $(this).css('display', '');

        var w = template_offset.width - offset;
        var h = template_offset.height;
        var x = template_offset.left + offset;
        var y = template_offset.top;
        $(this).contents().add($(this)).each(function(){
            if (this.nodeName.indexOf('#') == -1) {
                var dimens = fn_get_offset(this);
                if (dimens.width > w) {
                    w = dimens.width - offset;
                }
                if (dimens.height > h) {
                    h = dimens.height;
                }
                if (dimens.left != 0 && dimens.left < x) {
                    x = dimens.left + offset;
                }
                if (dimens.top != 0 && dimens.top < y) {
                    y = dimens.top;
                }
            }
        });

        var template_over = $('<div class="cm-template-over template-over"></div>').appendTo(Tygh.body);
        template_over.css({'opacity': 0.5, 'left': x, 'top': y, 'height': h + 'px', 'width': w + 'px'});
        template_over.fadeIn('fast');
    });
}

function fn_build_tree(box, dest_obj)
{
    var $ = Tygh.$;
    var dest = $(dest_obj);
    dest.empty();
    var pad = 10;
    var level = box.parents('.cm-template-box').length - 1;
    box.parents('.cm-template-box').each(function(i){
        dest.prepend('<li' + (i == level ? ' class="first"' : '') + ' style="padding-left: ' + ((level - i) * pad) + 'px">' + $(this).data('caTeTemplate') + '</li>');
    });
    level++;
    dest.append('<li style="padding-left: ' + (level * pad) + 'px" class="cm-cur-template active">' + box.data('caTeTemplate') + '</li>');
    var inners = [box.data('caTeTemplate')];
    fn_build_branch(box, dest, inners, level, pad, true);
}

function fn_build_branch(obj, dest, exist_array, level, margin, increase)
{
    var $ = Tygh.$;
    if (increase) {
        level++;
    }
    obj.children().each(function(){
        if ($(this).hasClass('cm-template-box')) {
            var tm_name = $(this).data('caTeTemplate');
            if ($.inArray(tm_name, exist_array) == -1) {
                exist_array.push(tm_name);
                $('li:contains("' + $(this).parents('.cm-template-box').eq(0).data('caTeTemplate') + '")', dest).after('<li style="padding-left: ' + (level * margin) + 'px">' + tm_name + '</li>');
            }
            fn_build_branch($(this), dest, exist_array, level, margin, true);
        } else {
            fn_build_branch($(this), dest, exist_array, level, margin, false);
        }
    });
}

function fn_show_template_editor(data, params)
{
    var $ = Tygh.$;

    $('#template_editor_content').ceDialog('open', {'height': 'auto'});

    $('#template_text').ceCodeEditor('set_value', data.content);
}

function fn_save_template()
{
    var $ = Tygh.$;
    if ($('#template_text').hasClass('cm-item-modified')) {
        $('#template_text').removeClass('cm-item-modified');

        var cur_template = $('.cm-cur-template').eq(0).text();
        var result_ids = [];
        $('span[data-ca-te-template="' + cur_template + '"]').each(function(){
            result_ids.push(this.id);
        });
        $.ceAjax('request', fn_url('design_mode.save_template'), {data: {file: cur_template, current_url: Tygh.current_url, content: $('#template_text').ceCodeEditor('value')}, method: 'post', result_ids: result_ids.join(','), callback: fn_save_template_callback, full_render: true});
    }
}

function fn_save_template_callback()
{
    var $ = Tygh.$;
    $('.cm-template-box').each(function(){
        var elm = $(this);
        var icon = $('.cm-template-icon', elm).eq(0);
        var _id = elm.prop('id');
        
        icon.data('caTeTemplateOwner', _id);
        $('#' + _id).css('display', 'block');
        var template_offset = fn_get_offset($('#' + _id).get(0), true);
        $('#' + _id).css('display', '');
        icon.css({'left': template_offset.left, 'top': template_offset.top});
        if (!icon.parents('#template_editor_content').length) {
            icon.removeClass('hidden');
        }
    });
}

function fn_restore_template()
{
    var $ = Tygh.$;
    if (confirm(Tygh.tr('text_restore_question'))) {
        var cur_template = $('.cm-cur-template').eq(0).text();
        var result_ids = [];
        $('span[data-ca-te-template="' + cur_template + '"]').each(function(){
            result_ids.push(this.id);
        });
        $.ceAjax('request', fn_url('design_mode.restore_template'), {data: {file: cur_template, current_url: Tygh.current_url}, result_ids: result_ids.join(','), caching: false});
    }
}

(function(_, $) {

    $.extend({
        dispatch_design_mode_event: function(e)
        {
            var jelm = $(e.target);

            if (e.type == 'click' && $.browser.mozilla && e.which != 1) {
                return true;
            }

            if (e.type == 'mouseover') {
                if (jelm.parents('#template_list_menu').length && jelm.parent('ul').length) {
                    clearTimeout(window['template_timer_id']);
                    var main_template = $('#' + $('#template_list_menu ul').data('caTeTemplateOwner'));
                    var dest_template = main_template.data('caTeTemplate') == jelm.text() ? main_template : $('span[data-ca-te-template="' + jelm.text() + '"]', main_template);
                    fn_set_overlay(dest_template);
                } else if (jelm.hasClass('cm-template-icon')) {
                    var template = $('#' + jelm.data('caTeTemplateOwner'));
                    fn_set_overlay(template);

                    var w = $.getWindowSizes();
                    var dest = $('#template_list_menu ul').eq(0);
                    dest.data('caTeTemplateOwner', jelm.data('caTeTemplateOwner'));
                    dest.empty();
                    dest.append('<li>' + template.data('caTeTemplate') + '</li>');
                    var inners = [template.data('caTeTemplate')];
                    fn_build_branch(template, dest, inners, 0, 10, true);
                    var icon_offset = fn_get_offset(jelm.get(0));
                    var l = icon_offset.left + icon_offset.width + $('#template_list_menu').width() + 12 > w.offset_x + w.view_width ? icon_offset.left - $('#template_list_menu').width() - 12 : icon_offset.left + icon_offset.width;
                    var t = icon_offset.top + $('#template_list_menu').height() + 12 > w.offset_y + w.view_height ? icon_offset.top - $('#template_list_menu').height() - 12 : icon_offset.top;
                    $('#template_list_menu').css({'left': l, 'top': t});
                    clearTimeout(window['template_timer_id']);
                    $('#template_list_menu').hide();
                    window['template_timer_id'] = setTimeout(function() {
                        Tygh.$('#template_list_menu').fadeIn('fast');
                    }, 300);

                } else if (jelm.prop('id') == 'template_list_menu' || jelm.parents('#template_list_menu').length) {
                    clearTimeout(window['template_timer_id']);
                }
                return true;

            } else if (e.type == 'click') {
                if (!jelm.hasClass('cm-cur-template') && jelm.parent('#template_list').length) {
                    if ($('#template_text').hasClass('cm-item-modified')) {
                        if (!confirm(_.tr('text_page_changed'))) {
                            return false;
                        }
                    }
                    $.ceAjax('request', fn_url('design_mode.get_content'), {data: {file: jelm.text()}, callback: function(data) {
                        $('#template_text').ceCodeEditor('set_value', data.content);
                    }});

                    $('#template_list li').removeClass('cm-cur-template active');
                    jelm.addClass('cm-cur-template active');
                } else if (jelm.parents('#template_list_menu').length && jelm.parent('ul').length) {
                    var main_template = $('#' + $('#template_list_menu ul').data('caTeTemplateOwner'));
                    var dest_template = main_template.data('caTeTemplate') == jelm.text() ? main_template : $('span[data-ca-te-template="' + jelm.text() + '"]', main_template).eq(0);
                    fn_build_tree(dest_template, $('#template_list'));
                    $('#template_list_menu').fadeOut('fast');
                    $.ceAjax('request', fn_url('design_mode.get_content'), {data: {file: jelm.text()}, callback: fn_show_template_editor});
                    return false;
                } else if (jelm.hasClass('cm-popup-switch')) {
                    if ($('#template_text').hasClass('cm-item-modified')) {
                        if (confirm(_.tr('text_template_changed'))) {
                            fn_save_template();
                        }
                    }
                }

            } else if (e.type == 'mouseout') {
                if ($('.cm-template-icon').length && jelm.hasClass('cm-template-icon') || jelm.prop('id') == 'template_list_menu' || jelm.parents('#template_list_menu').length && jelm.parent('ul').length) {
                    $('.cm-template-over').fadeOut('fast', function() {
                        $(this).remove();
                    });
                    clearTimeout(window['template_timer_id']);
                    window['template_timer_id'] = setTimeout(function() {
                        Tygh.$('#template_list_menu').fadeOut('fast');
                    }, 300);
                }
            }
        },

        init_design_mode: function(content)
        {
    
            $(content).on('click', function(e) {
                return $.dispatch_design_mode_event(e);
            });

            $(content).on('mouseover', function(e) {
                return $.dispatch_design_mode_event(e);
            });

            $(content).on('mouseout', function(e) {
                return $.dispatch_design_mode_event(e);
            });

            if ($('.cm-template-box').length) {
                $('.cm-hidden-wrapper.hidden').removeClass('hidden');

                $('.cm-template-box').each(function() {
                    var elm = $(this);
                    var icon = $('.cm-template-icon', elm).eq(0);
                    var _id = elm.prop('id');
                    icon.data('caTeTemplateOwner', _id);

                    $('#' + _id).css('display', 'block');
                    var template_offset = fn_get_offset($('#' + _id).get(0), true);
                    $('#' + _id).css('display', '');
                    icon.css({'left': template_offset.left, 'top': template_offset.top});
                    if (!icon.parents('#template_editor_content').length) {
                        icon.removeClass('hidden');
                    }
                });
            }
        }
    });

    $.ceEvent('on', 'ce.commoninit', function(content) {
        $.init_design_mode(content);
    });

    $.ceEvent('on', 'ce.ajaxdone', function(content) {
        $.init_design_mode(content);
    });

}(Tygh, Tygh.$));
