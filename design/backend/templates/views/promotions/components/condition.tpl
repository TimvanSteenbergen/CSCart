{assign var="l" value="promotion_cond_`$condition_data.condition`"}

<div class="option clearfix">
    <div class="pull-right">
        <a class="icon-trash cm-tooltip cm-delete-row" name="remove" id="{$item_id}" title="{__("remove")}"></a>
    </div>
    <label>{__($l)}&nbsp;</label>

    {if $schema.conditions[$condition_data.condition].type == "mixed"}
    {assign var="p_md" value=$prefix|md5}
    {assign var="condition_element" value=$condition_data.condition_element}
    <select name="{$prefix}[condition_element]" id="mixed_condition_element_{$p_md}">
    {assign var="items" value=$schema.conditions[$condition_data.condition].conditions_function|fn_get_promotion_variants}
    {foreach from=$items key="_k" item="v"}
    {if $v.is_group}
    <optgroup label="{$v.group}">
    {foreach from=$v.items key="__k" item="__v"}
        {if !$condition_element}
        {assign var="condition_element" value=$__k}
        {/if}
        <option value="{$__k}" {if $__k == $condition_data.condition_element}selected="selected"{/if}>{$__v.value}</option>
    {/foreach}
    </optgroup>
    {else}
    {if !$condition_element}
    {assign var="condition_element" value=$_k}
    {/if}
    <option value="{$_k}" {if $_k == $condition_data.condition_element}selected="selected"{/if}>{$v.value}</option>
    {/if}
    {/foreach}
    </select>

    <script type="text/javascript">
    (function(_, $) {
        $(document).ready(function(){
            $('#mixed_condition_element_{$p_md}').on('change', function() {
                fn_promotion_rebuild_mixed_data({$items|json_encode nofilter}, $(this).val(), '{$p_md}', '{$condition_data.condition_element}', '{$condition_data.value}', '{$condition_data.value_name}');
            }).trigger('change');
        });
    }(Tygh, Tygh.$));
    </script>

    {/if}

    {if $schema.conditions[$condition_data.condition].type != "list" && $schema.conditions[$condition_data.condition].type != "statement"}
    <select name="{$prefix}[operator]">
    {foreach from=$schema.conditions[$condition_data.condition].operators item="op"}
    {assign var="l" value="promotion_op_`$op`"}
    <option value="{$op}" {if $op == $condition_data.operator}selected="selected"{/if}>{__($l)}</option>
    {/foreach}
    </select>
    {/if}

    <input type="hidden" name="{$prefix}[condition]" value="{$condition_data.condition}" />

    {if $schema.conditions[$condition_data.condition].type == "input"}
        <input type="text" name="{$prefix}[value]" value="{$condition_data.value}" class="input-medium" />

    {elseif $schema.conditions[$condition_data.condition].type == "select"}
    <select name="{$prefix}[value]">
    {foreach from=$schema.conditions[$condition_data.condition].variants|default:$schema.conditions[$condition_data.condition].variants_function|fn_get_promotion_variants key="_k" item="v"}
    <option value="{$_k}" {if $_k == $condition_data.value}selected="selected"{/if}>{if $schema.conditions[$condition_data.condition].variants_function}{$v}{else}{__($v)}{/if}</option>
    {/foreach}
    </select>

    {elseif $schema.conditions[$condition_data.condition].type == "picker"}
    
        {assign var="_z" value="params_$zone"}
        {if $schema.conditions[$condition_data.condition].picker_props.$_z}
            {assign var="params" value=$schema.conditions[$condition_data.condition].picker_props.$_z}
        {else}
            {assign var="params" value=$schema.conditions[$condition_data.condition].picker_props.params}        
        {/if}

        {include_ext file=$schema.conditions[$condition_data.condition].picker_props.picker company_ids=$picker_selected_companies data_id="objects_`$elm_id`" input_name="`$prefix`[value]" item_ids=$condition_data.value params_array=$params owner_company_id=$promotion_data.company_id but_meta="btn"}

    {elseif $schema.conditions[$condition_data.condition].type == "list"}
        <input type="hidden" name="{$prefix}[operator]" value="in" />
        <input type="hidden" name="{$prefix}[value]" value="{$condition_data.value}" />

        {$condition_data.value|default:__("no_data")}

    {elseif $schema.conditions[$condition_data.condition].type == "statement"}
        <input type="hidden" name="{$prefix}[operator]" value="eq" />
        <input type="hidden" name="{$prefix}[value]" value="Y" />

        {__("yes")}
    
    {elseif $schema.conditions[$condition_data.condition].type == "mixed"}
        <select id="mixed_select_{$p_md}" name="{$prefix}[value]" class="input-medium hidden"></select>
        <div class="cm-ajax-select-object shift-input shift-left">
            {include file="common/ajax_select_object.tpl" data_url="" text="" result_elm="mixed_input_`$p_md`" id="mixed_ajax_select_`$p_md`" js_action="$('#mixed_input_`$p_md`').toggleBy(($('#mixed_input_`$p_md`').val() != 'disable_select')); if ($('#mixed_input_`$p_md`').val() == 'disable_select') $('#mixed_input_`$p_md`').val('');"}
        </div>
        <input id="mixed_input_{$p_md}" type="text" name="{$prefix}[value]" value="{$condition_data.value}" class="hidden input-medium" />
        <input id="mixed_input_{$p_md}_name" type="text" name="{$prefix}[value_name]" value="{$condition_data.value_name}" class="hidden input-medium" />
    {/if}
</div>
