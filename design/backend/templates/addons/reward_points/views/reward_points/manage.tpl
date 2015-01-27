{if "ULTIMATE"|fn_allowed_for}
    {if !$runtime.company_id}
        {assign var="show_update_for_all" value=true}
    {/if}
{/if}

{capture name="mainbox"}
    <div id="content_reward_points">
        <form action="{""|fn_url}" method="post" name="reward_points">
        
            <input type="hidden" name="selected_section" value="reward_points">
            <input type="hidden" name="redirect_url" value="{"reward_points.`$runtime.mode`"|fn_url}">
            <input type="hidden" name="object_type" value="{$object_type}">
            
            {include file="common/subheader.tpl" title=__("earned_points")}

            <table class="table table-middle">
            <thead class="cm-first-sibling">
                <tr>
                    <th width="20%">{__("usergroup")}</th>
                    <th width="40%">{__("amount")}</th>
                    <th width="40%">{__("amount")}&nbsp;{__("type")}</th>
                    {if $show_update_for_all}
                    <th></th>
                    {/if}
                </tr>
            </thead>
            <tbody>
            {foreach from=$reward_usergroups item=m}
                {assign var="m_id" value=$m.usergroup_id}
                {assign var="point" value=$reward_points.$m_id}
                <tr>
                    <td>
                        <input type="hidden" name="reward_points[{$m_id}][usergroup_id]" value="{$m_id}">
                        {$m.usergroup}</td>
                    <td>
                        <input type="text" id="earned_points_{$object_type}_{$m_id}" name="reward_points[{$m_id}][amount]" value="{$point.amount|default:"0"}" {if $show_update_for_all}disabled="disabled"{/if}></td>
                    <td>
                        <select name="reward_points[{$m_id}][amount_type]"  id="type_earned_points_{$object_type}_{$m_id}" class="expanded input-xlarge" {if $show_update_for_all}disabled="disabled"{/if}>
                            <option value="A" {if $point.amount_type == "A"}selected="selected"{/if}>{__("absolute")} ({__("points_lower")})</option>
                            <option value="P" {if $point.amount_type == "P"}selected="selected"{/if}>{__("percent")} (%)</option>
                        </select>
                    </td>
                    {if $show_update_for_all}
                    <td>
                        {include file="buttons/update_for_all.tpl" display=true name="reward_points[`$m_id`][update_all_vendors]" hide_element="earned_points_`$object_type`_`$m_id`" object_id=$m_id}
                    </td>
                    {/if}
                </tr>
            {/foreach}
            </tbody>
            </table>

            {capture name="buttons"}
                {include file="buttons/save.tpl" but_name="dispatch[reward_points.update]" but_role="submit-link" but_target_form="reward_points"}
            {/capture}
        </form>
    </div>
{/capture}

{include file="common/mainbox.tpl" title=__("reward_points") buttons=$smarty.capture.buttons content=$smarty.capture.mainbox}