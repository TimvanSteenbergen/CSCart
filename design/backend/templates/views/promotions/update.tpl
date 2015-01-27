{if $promotion_data}
    {assign var="id" value=$promotion_data.promotion_id}
{else}
    {assign var="id" value=0}
{/if}

{assign var="allow_save" value=true}
{if "ULTIMATE"|fn_allowed_for}
    {assign var="allow_save" value=$promotion_data|fn_allow_save_object:"promotions"}
{/if}

{script src="js/tygh/node_cloning.js"}
<script type="text/javascript">
function fn_promotion_add(id, skip_select, type)
{
    var $ = Tygh.$,
        new_group = false,
        new_id = $('#container_' + id).cloneNode(0, true, true).str_replace('container_', ''),
        $new_container = $('#container_' + new_id),
        $input = null;

    skip_select = skip_select || false;

    // Iterate through all previous elements
    $new_container.prevAll('[id^="container_"]').each(function() {
        var $this = $(this);
        $input = $('input[name^=promotion_data]:first', $this).clone();
        if ($input.length == 0) {
            $input = $('input[data-ca-input-name^=promotion_data]:first', $this).clone();
        }

        if ($input.length == 0) {

        } else {
            if ($input.val() != 'undefined' && $input.val() != '') {
                $input.val('');
            }

            return false;
        }
    });

    // We added new group, so we need to get input from parent element or this is the new condition
    if ($input === null || !$input.get(0)) {
        $input = $('input[name^=promotion_data]:first', $new_container.parents('li:first')).clone(); // for group

        $('.no-node.no-items', $new_container.parents('ul:first')).hide(); // hide conainer with "no items" text

        // new condition
        if (!$input.get(0)) {
            var n = (type == 'condition') ? "promotion_data[conditions][conditions][0][condition]" : "promotion_data[bonuses][0][bonus]";
            $input = $('<input type="hidden" name="'+ n +'" value="" />');
        } else {
            new_group = true;
        }
    }

    var _name = $input.prop('name').length > 0 ? $input.prop('name') : $input.data('caInputName');
    var val = parseInt(_name.match(/(.*)\[(\d+)\]/)[2]);
    var name = new_group ? _name : _name.replace(/(.*)\[(\d+)\]/, '$1[' + (val + 1) +']');

    $input.attr('name', name);
    $new_container.append($input);
    name = name.replace(/\[(\w+)\]$/, '');

    if (new_group) {
        name += '[conditions][1]';
    }

    $new_container.prev().removeClass('cm-last-item'); // remove tree node closure from previous element
    $new_container.addClass('cm-last-item').show(); // add tree node closure to new element
    // Update selector with name with new index
    if (skip_select == false) {
        $('#container_' + new_id + ' select').prop('id', new_id).prop('name', name);

    // Or just return id and name (for group)
    } else {
        $new_container.empty(); // clear node contents
        return {
            new_id: new_id,
            name: name
        };
    }
}

function fn_promotion_add_group(id, zone)
{
    var $ = Tygh.$;
    var res = fn_promotion_add(id, true, 'condition');
    $.ceAjax('request', fn_url('promotions.dynamic?promotion_id={$id}&zone=' + zone + '&prefix=' + encodeURIComponent(res.name) + '&group=new&elm_id=' + res.new_id), {
        result_ids: 'container_' + res.new_id
    });
}

function fn_promotion_rebuild_mixed_data(items, value, id, element_id, condition_value, condition_value_name)
{
    var $ = Tygh.$;
    var opts = '';
    var first_variant = '';

    for (var k in items) {
        if (items[k]['is_group']) {
            for (var l in items[k]['items']) {
                first_variant = '';
                if (l == value) {
                    if (items[k]['items'][l]['variants']) {
                        var count = 0;
                        for (var m in items[k]['items'][l]['variants']) {
                            if (!first_variant) {
                                first_variant = m;
                            }
                            opts += '<option value="' + m + '"' + (m == condition_value ? ' selected="selected"' : '') + '>' + items[k]['items'][l]['variants'][m] + '</option>';
                            count++;
                        }
                        if (count < {$smarty.const.PRODUCT_FEATURE_VARIANTS_THRESHOLD}) {
                            $('#mixed_ajax_select_' + id).parents('.cm-ajax-select-object').hide();
                            $('#mixed_select_' + id).html(opts).show().prop('disabled', false);
                            $('#mixed_input_' + id).hide().prop('disabled', true);
                            $('#mixed_input_' + id + '_name').hide().prop('disabled', true);
                        } else {
                            $('#mixed_ajax_select_' + id).data('ajax_content', null);
                            $('#mixed_select_' + id).hide().prop('disabled', true);
                            $('#mixed_ajax_select_' + id).html('');
                            $('#mixed_ajax_select_' + id).parents('.cm-ajax-select-object').show();
                            $('.cm-ajax-content-more', $('#scroller_mixed_ajax_select_' + id)).show();
                            $('#content_loader_mixed_ajax_select_' + id).attr('data-ca-target-url', fn_url('product_features.get_feature_variants_list?enter_other=N&feature_id=' + l));
                            $('#sw_mixed_ajax_select_' + id + '_wrap_').html(items[k]['items'][l]['variants'][first_variant]);
                            $('#mixed_input_' + id + '_name').hide().prop('disabled', false);
                            $('#mixed_input_' + id + '_name').val(items[k]['items'][l]['variants'][first_variant]);
                            $('#mixed_input_' + id).hide().prop('disabled', false);
                            $('#mixed_input_' + id).val(first_variant);
                            if (condition_value && element_id == l) {
                                $('#sw_mixed_ajax_select_' + id + '_wrap_').html(condition_value_name);
                                $('#mixed_input_' + id + '_name').val(condition_value_name);
                                $('#mixed_input_' + id).val(condition_value);
                            }
                        }
                    } else {
                        $('#mixed_input_' + id).val(element_id == l ? condition_value : '').show().prop('disabled', false);
                        $('#mixed_select_' + id).hide().prop('disabled', true);
                        $('#mixed_ajax_select_' + id).parents('.cm-ajax-select-object').hide();
                        $('#mixed_input_' + id + '_name').val('').hide().prop('disabled', true);
                    }
                }
            }
        } else {
            if (k == value) {
                if (items[k]['variants']) {
                    var count = 0;
                    for (var m in items[k]['variants']) {
                        if (!first_variant) {
                            first_variant = m;
                        }
                        opts += '<option value="' + m + '"' + (m == condition_value ? ' selected="selected"' : '') + '>' + items[k]['variants'][m] + '</option>';
                        count++;
                    }
                    if (count < {$smarty.const.PRODUCT_FEATURE_VARIANTS_THRESHOLD}) {
                        $('#mixed_ajax_select_' + id).parents('.cm-ajax-select-object').hide();
                        $('#mixed_select_' + id).html(opts).show().prop('disabled', false);
                        $('#mixed_input_' + id).hide().prop('disabled', true);
                        $('#mixed_input_' + id + '_name').hide().prop('disabled', true);
                    } else {
                        $('#mixed_ajax_select_' + id).data('ajax_content', null);
                        $('#mixed_select_' + id).hide().prop('disabled', true);
                        $('#mixed_ajax_select_' + id).html('');
                        $('#mixed_ajax_select_' + id).parents('.cm-ajax-select-object').show();
                        $('.cm-ajax-content-more', $('#scroller_mixed_ajax_select_' + id)).show();
                        $('#content_loader_mixed_ajax_select_' + id).attr('data-ca-target-url', fn_url('product_features.get_feature_variants_list?enter_other=N&feature_id=' + k));
                        $('#sw_mixed_ajax_select_' + id + '_wrap_').html(items[k]['variants'][first_variant]);
                        $('#mixed_input_' + id + '_name').hide().prop('disabled', false);
                        $('#mixed_input_' + id + '_name').val(items[k]['variants'][first_variant]);
                        $('#mixed_input_' + id).hide().prop('disabled', false);
                        $('#mixed_input_' + id).val(first_variant);
                        if (condition_value && element_id == k) {
                            $('#sw_mixed_ajax_select_' + id + '_wrap_').html(condition_value_name);
                            $('#mixed_input_' + id + '_name').val(condition_value_name);
                            $('#mixed_input_' + id).val(condition_value);
                        }
                    }
                } else {
                    $('#mixed_input_' + id).val(element_id == l ? condition_value : '').show().prop('disabled', false).removeClass('hidden');
                    $('#mixed_select_' + id).hide().prop('disabled', true);
                    $('#mixed_ajax_select_' + id).parents('.cm-ajax-select-object').hide();
                    $('#mixed_input_' + id + '_name').val('').hide().prop('disabled', true);
                }
            }
        }
    }
}
</script>

{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="promotion_form" class="promotion form-horizontal form-edit  {if !$allow_save}cm-hide-inputs{/if}" >
<input type="hidden" class="cm-no-hide-input" name="promotion_id" value="{$id}" />
<input type="hidden" class="cm-no-hide-input" name="selected_section" value="{$smarty.request.selected_section}" />
<input type="hidden" class="cm-no-hide-input" name="promotion_data[zone]" value="{$promotion_data.zone|default:$zone}" />

{capture name="tabsbox"}
<div id="content_details">
<fieldset>
    <div class="control-group">
        <label for="elm_promotion_name" class="control-label cm-required">{__("name")}:</label>
        <div class="controls">
            <input type="text" name="promotion_data[name]" id="elm_promotion_name" size="25" value="{$promotion_data.name}" class="input-large" />
        </div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="elm_promotion_det_descr">{__("detailed_description")}:</label>
        <div class="controls">
        <textarea id="elm_promotion_det_descr" name="promotion_data[detailed_description]" cols="55" rows="8" class="cm-wysiwyg input-large">{$promotion_data.detailed_description}</textarea>
        </div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="elm_promotion_sht_descr">{__("short_description")}:</label>
        <div class="controls">
            <textarea id="elm_promotion_sht_descr" name="promotion_data[short_description]" cols="55" rows="8" class="cm-wysiwyg input-large">{$promotion_data.short_description}</textarea>
        </div>
    </div>
    
    {if "ULTIMATE"|fn_allowed_for}
        {include file="views/companies/components/company_field.tpl"
            name="promotion_data[company_id]"
            id="elm_promotion_data_`$id`"
            selected=$promotion_data.company_id
        }
    {/if}
        
    <div class="control-group">
        <label class="control-label" for="elm_use_avail_period">{__("use_avail_period")}:</label>
        <div class="controls">
            <input type="checkbox" name="avail_period" id="elm_use_avail_period" {if $promotion_data.from_date || $promotion_data.to_date}checked="checked"{/if} value="Y" onclick="fn_activate_calendar(this);"/>
        </div>
    </div>

    {capture name="calendar_disable"}{if !$promotion_data.from_date && !$promotion_data.to_date}disabled="disabled"{/if}{/capture}
    
    <div class="control-group">
        <label class="control-label" for="elm_date_holder_from">{__("avail_from")}:</label>
        <div class="controls">
        <input type="hidden" name="promotion_data[from_date]" value="0" />
        {include file="common/calendar.tpl" date_id="elm_date_holder_from" date_name="promotion_data[from_date]" date_val=$promotion_data.from_date|default:$smarty.const.TIME start_year=$settings.Company.company_start_year extra=$smarty.capture.calendar_disable}
        </div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="elm_date_holder_to">{__("avail_till")}:</label>
        <div class="controls">
        <input type="hidden" name="promotion_data[to_date]" value="0" />
        {include file="common/calendar.tpl" date_id="elm_date_holder_to" date_name="promotion_data[to_date]" date_val=$promotion_data.to_date|default:$smarty.const.TIME start_year=$settings.Company.company_start_year extra=$smarty.capture.calendar_disable}
        </div>
    </div>

    <script language="javascript">
    function fn_activate_calendar(el)
    {
        var $ = Tygh.$;
        var jelm = $(el);
        var checked = jelm.prop('checked');

        $('#elm_date_holder_from,#elm_date_holder_to').prop('disabled', !checked);
    }

    fn_activate_calendar(Tygh.$('#elm_use_avail_period'));
    </script>

    <div class="control-group">
        <label class="control-label" for="elm_promotion_priority">{__("priority")}</label>
        <div class="controls">
        <input type="text" name="promotion_data[priority]" id="elm_promotion_priority" size="25" value="{$promotion_data.priority}" />
        </div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="elm_promotion_stop">{__("stop_other_rules")}</label>
        <div class="controls">
        <input type="hidden" name="promotion_data[stop]" value="N" />
        <input type="checkbox" name="promotion_data[stop]" id="elm_promotion_stop" value="Y" {if $promotion_data.stop == "Y"}checked="checked"{/if}/>
        </div>
    </div>
    
    {include file="common/select_status.tpl" input_name="promotion_data[status]" id="elm_promotion_status" obj=$promotion_data hidden=true}

</fieldset>
<!--content_details--></div>

<div id="content_conditions">

{include file="views/promotions/components/group.tpl" prefix="promotion_data[conditions]" group=$promotion_data.conditions root=true no_ids=true zone=$promotion_data.zone|default:$zone hide_add_buttons=!$allow_save}

<!--content_conditions--></div>

<div id="content_bonuses">

{include file="views/promotions/components/bonuses_group.tpl" prefix="promotion_data[bonuses]" group=$promotion_data.bonuses zone=$promotion_data.zone|default:$zone hide_add_buttons=!$allow_save}

<!--content_bonuses--></div>

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section track=true}

{capture name="buttons"}

{if "ULTIMATE"|fn_allowed_for && !$allow_save}
    {assign var="hide_first_button" value=true}
    {assign var="hide_second_button" value=true}
{/if}

{include file="buttons/save_cancel.tpl" but_name="dispatch[promotions.update]" hide_first_button=$hide_first_button hide_second_button=$hide_second_button but_target_form="promotion_form" save=$id}
{/capture}

</form>
{/capture}

{if !$id}
    {assign var="title" value=__("new_promotion")}
{else}
    {assign var="title" value="{__("editing_promotion")}:&nbsp;`$promotion_data.name`"}
{/if}
{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons}
