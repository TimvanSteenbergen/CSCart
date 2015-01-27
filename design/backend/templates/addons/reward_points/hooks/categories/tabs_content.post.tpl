<div class="hidden" id="content_reward_points">
    <input type="hidden" name="object_type" value="{$object_type}" />

    {include file="common/subheader.tpl" title=__("earned_points") target="#reward_points_categories_hook"}
    <div id="reward_points_categories_hook" class="in collapse">
        <fieldset>
            <input type="hidden" name="category_data[is_op]" value="N">
            <label for="rp_is_op" class="checkbox">
                <input type="checkbox" name="category_data[is_op]" id="rp_is_op" value="Y" {if $category_data.is_op == "Y"}checked="checked"{/if} onclick="Tygh.$.disable_elms([{foreach from=$reward_usergroups item=m}'earned_points_{$object_type}_{$m.usergroup_id}',{/foreach}{foreach from=$reward_usergroups item=m}'points_type_{$object_type}_{$m.usergroup_id}',{/foreach}], !this.checked);">
                {__("override_g_points")}
            </label>
        <br>
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
            {assign var="m_id" value=$m.usergroup_id}
            {assign var="point" value=$reward_points.$m_id}
            <tr>
                <td>
                    <input type="hidden" name="category_data[reward_points][{$m_id}][usergroup_id]" value="{$m_id}">
                    {$m.usergroup}</td>
                <td>
                    <input type="text" id="earned_points_{$object_type}_{$m_id}" name="category_data[reward_points][{$m_id}][amount]" value="{$point.amount|default:"0"}" {if $category_data.is_op != "Y"}disabled="disabled"{/if}></td>
                <td>
                    <select id="points_type_{$object_type}_{$m_id}" name="category_data[reward_points][{$m_id}][amount_type]" {if $object_type == $smarty.const.CATEGORY_REWARD_POINTS && $category_data.is_op != 'Y'}disabled="disabled"{/if} class="input-xlarge">
                        <option value="A" {if $point.amount_type == "A"}selected{/if}>{__("absolute")} ({__("points_lower")})</option>
                        <option value="P" {if $point.amount_type == "P"}selected{/if}>{__("percent")} (%)</option>
                    </select></td>
        
            </tr>
        {/foreach}
        </tbody>
        </table>
        </fieldset>
    </div>
</div>