{if !$hide_add_buttons}
    <div class="buttons-container pull-right">
        {include file="common/tools.tpl" hide_tools=true tool_onclick="fn_promotion_add(this.id, false, 'bonus');" tool_id="add_bonus" link_text=__("add_bonus") prefix="bottom"}
    </div>
{/if}
<ul class="promotion-group clear">
    <li class="no-node no-items {if $group}hidden{/if}">
        {__("no_items")}
    </li>

    {foreach from=$group key="k" item="bonus_data" name="bonuses"}
    <li id="container_bonus_{$k}" class="cm-row-item{if $smarty.foreach.bonuses.last} cm-last-item{/if}">
        {include file="views/promotions/components/bonus.tpl" bonus_data=$bonus_data elm_id="bonus_`$k`" prefix="promotion_data[bonuses][`$k`]"}
    </li>
    {/foreach}
    
    <li id="container_add_bonus" class="clear hidden cm-row-item">
        <div class="option">
            <select onchange="Tygh.$.ceAjax('request', fn_url('promotions.dynamic?prefix=' + encodeURIComponent(this.name) + '&bonus=' + this.value + '&elm_id=' + this.id), {$ldelim}result_ids: 'container_' + this.id{$rdelim})">
                <option value=""> -- </option>
                {foreach from=$schema.bonuses key="_k" item="b"}
                    {if $zone|in_array:$b.zones}
                        {assign var="l" value="promotion_bonus_`$_k`"}
                        <option value="{$_k}">{__($l)}</option>
                    {/if}
                {/foreach}
            </select>
        </div>
    </li>
</ul>