{assign var="prefix_md5" value=$prefix|md5}

<input type="hidden" name="{$prefix}[fake]" value="" disabled="disabled" />

{capture name="set"}
{if $group.set == "any"}
{assign var="selected_name" value=__("promotions.cond_any")}
{else}
{assign var="selected_name" value=__("promotions.cond_all")}
{/if}
{include file="common/select_object.tpl" style="field" items=["all" => __("promotions.cond_all"), "any" => __("promotions.cond_any")] select_container_name="`$prefix`[set]" selected_key=$group.set selected_name=$selected_name}
{/capture}

{capture name="set_value"}
{if !$group || $group.set_value}
{assign var="selected_name" value=__("promotions.cond_true")}
{else}
{assign var="selected_name" value=__("promotions.cond_false")}
{/if}
{include file="common/select_object.tpl" style="field" items=["0" => __("promotions.cond_false"), "1" => __("promotions.cond_true")] select_container_name="`$prefix`[set_value]" selected_key=$group.set_value|default:1 selected_name=$selected_name}
{/capture}

<ul class="promotion-group cm-row-item">
    <li class="no-node{if $root}-root{/if}">
        {if !$root}
        <div class="pull-right">
            <a class="icon-trash cm-delete-row cm-tooltip promotion-remove" name="remove" id="{$item_id}" title="{__("remove_this_item")}"></a>
        </div>
        {/if}
        <div id="add_condition_{$prefix_md5}" class="btn-toolbar pull-right">
            {if !$hide_add_buttons}
                {include file="common/tools.tpl" hide_tools=true tool_onclick="fn_promotion_add(Tygh.$(this).parents('div[id^=add_condition_]').prop('id'), false, 'condition');" prefix="simple" link_text=__("add_condition")}
                {include file="common/tools.tpl" hide_tools=true tool_onclick="fn_promotion_add_group(Tygh.$(this).parents('div[id^=add_condition_]').prop('id'), '`$zone`');" prefix="simple" link_text=__("add_group")}
            {/if}
        </div>
        {__("text_promotions_group_condition", ["[set]" => $smarty.capture.set, "[set_value]" => $smarty.capture.set_value])}
    </li>

    <li class="no-node no-items {if $group.conditions}hidden{/if}">
        <p class="no-items">{__("no_items")}</p>
    </li>

    {foreach from=$group.conditions key="k" item="condition_data" name="conditions"}
    <li id="container_condition_{$prefix_md5}_{$k}" class="cm-row-item{if $smarty.foreach.conditions.last} cm-last-item{/if}">
        {if $condition_data.set} {* this is the group *}
            {include file="views/promotions/components/group.tpl" root=false group=$condition_data prefix="`$prefix`[conditions][`$k`]" elm_id="condition_`$prefix_md5`_`$k`"}
        {else}
            {include file="views/promotions/components/condition.tpl" condition_data=$condition_data prefix="`$prefix`[conditions][`$k`]" elm_id="condition_`$prefix_md5`_`$k`"}
        {/if}
    </li>
    {/foreach}

    <li id="container_add_condition_{$prefix_md5}" class="hidden cm-row-item">
        <div class="option">
        <select onchange="Tygh.$.ceAjax('request', '{"promotions.dynamic?zone=`$zone`&promotion_id=`$smarty.request.promotion_id`"|fn_url nofilter}&prefix=' + encodeURIComponent(this.name) + '&condition=' + this.value + '&elm_id=' + this.id, {$ldelim}result_ids: 'container_' + this.id{$rdelim})">
            <option value=""> -- </option>
            {foreach from=$schema.conditions key="_k" item="c"}
                {if $zone|in_array:$c.zones}
                    {assign var="l" value="promotion_cond_`$_k`"}
                    <option value="{$_k}">{__($l)}</option>
                {/if}
            {/foreach}
        </select>
        </div>
    </li>
</ul>
