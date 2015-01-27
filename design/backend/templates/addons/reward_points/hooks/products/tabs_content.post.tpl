{assign var="data" value=$product_data}

<div id="content_reward_points" class="hidden">
    {include file="common/subheader.tpl" title=__("price_in_points") target="#reward_points_products_hook"}
    <div id="reward_points_products_hook" class="in collapse">
        <fieldset>
        {assign var="is_auto" value=$addons.reward_points.auto_price_in_points}
            <div class="control-group">
                <label class="control-label" for="pd_is_pbp">{__("pay_by_points")}</label>
                <div class="controls">
                    <input type="hidden" name="product_data[is_pbp]" value="N" />
                    <input type="checkbox" name="product_data[is_pbp]" id="pd_is_pbp" value="Y" {if $data.is_pbp == "Y" || $runtime.mode == "add"}checked="checked"{/if} onclick="{if $is_auto != 'Y'}Tygh.$.disable_elms(['price_in_points'], !this.checked);{else}Tygh.$.disable_elms(['is_oper'], !this.checked); Tygh.$.disable_elms(['price_in_points'], !this.checked || !Tygh.$('#is_oper').prop('checked'));{/if}">
                </div>
            </div>

            {if $is_auto == "Y"}
            <div class="control-group">
                <label class="control-label" for="is_oper">{__("override_per")}</label>
                <div class="controls">
                    {math equation="x*y" x=$data.price|default:"0" y=$addons.reward_points.point_rate assign="rate_pip"}
                    <input type="hidden" id="price_in_points_exchange" value="{$rate_pip|ceil}" />
                    <input type="hidden" name="product_data[is_oper]" value="N" />
                    <input type="checkbox" id="is_oper" name="product_data[is_oper]" value="Y" {if $data.is_oper == "Y"}checked="checked"{/if} onclick="Tygh.$.disable_elms(['price_in_points'], !this.checked);" {if $data.is_pbp != "Y"} disabled="disabled"{/if}>
                </div>
            </div>
            {/if}

            <div class="control-group">
                <label class="control-label" for="price_in_points">{__("price_in_points")}</label>
                <div class="controls">
                    <input type="text" id="price_in_points" name="product_data[point_price]" value="{$data.point_price|default:0}" size="10"  {if $data.is_pbp != "Y" || ($is_auto == "Y" && $data.is_oper != "Y")}disabled="disabled"{/if}>
                </div>
            </div>
        </fieldset>
    </div>

    <input type="hidden" name="object_type" value="{$object_type}">
            
    {include file="common/subheader.tpl" title=__("earned_points") target="#reward_points_products_earned_hook"}
    <div id="reward_points_products_earned_hook" class="in collapse">
        <fieldset>
            <input type="hidden" name="product_data[is_op]" value="N">
            <label for="rp_is_op" class="checkbox">
                <input type="checkbox" name="product_data[is_op]" id="rp_is_op" value="Y" {if $data.is_op == "Y"}checked="checked"{/if} onclick="Tygh.$.disable_elms([{foreach from=$reward_usergroups item=m}'earned_points_{$object_type}_{$m.usergroup_id}',{/foreach}{foreach from=$reward_usergroups item=m}'points_type_{$object_type}_{$m.usergroup_id}',{/foreach}], !this.checked);">
                {__("override_gc_points")}
            </label>
            

            <table class="table table-middle">
            <thead class="cm-first-sibling">
                <tr>
                    <th width="20%">{__("usergroup")}</th>
                    <th width="40%">{__("amount")}</th>
                    <th width="40%">{__("amount_type")}</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$reward_usergroups item=m}
                <tr>
                    <td>
                        <input type="hidden" name="product_data[reward_points][{$m.usergroup_id}][usergroup_id]" value="{$m.usergroup_id}">
                        {$m.usergroup}</td>
                    <td>
                        <input type="text" id="earned_points_{$object_type}_{$m.usergroup_id}" name="product_data[reward_points][{$m.usergroup_id}][amount]" value="{$reward_points[$m.usergroup_id].amount|default:"0"}" {if $data.is_op != "Y"}disabled="disabled"{/if}></td>
                    <td>
                        <select id="points_type_{$object_type}_{$m.usergroup_id}" name="product_data[reward_points][{$m.usergroup_id}][amount_type]" {if $object_type == $smarty.const.PRODUCT_REWARD_POINTS && $data.is_op != 'Y'}disabled="disabled"{/if}>
                            <option value="A" {if $reward_points[$m.usergroup_id].amount_type == "A"}selected{/if}>{__("absolute")} ({__("points_lower")})</option>
                            <option value="P" {if $reward_points[$m.usergroup_id].amount_type == "P"}selected{/if}>{__("percent")} (%)</option>
                        </select>
                    </td>
                </tr>
            {/foreach}
            </tbody>
            </table>
        </fieldset>
    </div>
</div>