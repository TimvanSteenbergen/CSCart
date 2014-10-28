{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="update_campaign_form_{$id}" class="">

{include file="common/pagination.tpl" save_current_page=true save_current_url=true}

{if $campaigns}
<table class="table table-middle" width="100%">
<thead>
<tr>
    <th class="center" width="1%">
        {include file="common/check_items.tpl"}</th>
    <th width="70%">{__("name")}</th>
    <th width="5%" class="center">&nbsp;</th>
    <th width="10%" class="right">{__("status")}</th>
</tr>
</thead>
<tbody>
{foreach from=$campaigns item="c"}
<tr class="cm-row-status-{$c.status|lower}">
    <td class="left" width="1%">
        <input type="checkbox" name="campaign_ids[]" value="{$c.campaign_id}" class="cm-item" /></td>
    <td>
        <input type="text" name="campaigns[{$c.campaign_id}][name]" value="{$c.object}" class="input-large input-hidden" /></td>
    <td class="nowrap">
        {capture name="tools_list"}
            <li>{btn type="dialog" text=__("campaign_stats") title=__("campaign_stats") href="newsletters.campaign_stats?campaign_id=`$c.campaign_id`"}</li>
            <li class="divider"></li>
            <li>{btn type="list" class="cm-confirm" text=__("delete") href="newsletters.delete_campaign?campaign_id=`$c.campaign_id`"}</li>
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
    <td class="nowrap right">
        {include file="common/select_popup.tpl" id=$c.campaign_id status=$c.status hidden=false object_id_name="campaign_id" table="newsletter_campaigns"}</td>
</tr>
{/foreach}
</tbody>
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl"}
</form>

{capture name="buttons"}
    {if $campaigns}
        {capture name="tools_list"}
            <li>{btn type="delete_selected" dispatch="dispatch[newsletters.m_delete_campaigns]" form="update_campaign_form_`$id`"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
        {include file="buttons/save.tpl" but_name="dispatch[newsletters.m_update_campaigns]" but_target_form="update_campaign_form_`$id`" but_role="submit-link"}
    {/if}
{/capture}

{capture name="adv_buttons"}
    {capture name="add_new_picker"}
        <form action="{""|fn_url}" method="post" class="form-horizontal form-edit" name="add_campaign_form">
            <div class="tabs cm-j-tabs">
                <ul class="nav nav-tabs">
                    <li id="tab_steps_new" class="cm-js active"><a>{__("general")}</a></li>
                </ul>
            </div>

            <div class="cm-tabs-content" id="content_tab_steps_new">
                <fieldset>
                    <div class="control-group">
                        <label class="control-label cm-required" for="c_name">{__("name")}</label>
                        <div class="controls">
                            <input class="span9" type="text" id="c_name" name="campaign_data[name]" value="" size="60" />
                        </div>
                    </div>

                    {include file="common/select_status.tpl" input_name="campaign_data[status]" id="c_status"}

                </fieldset>
            </div>

            <div class="buttons-container">
                {include file="buttons/save_cancel.tpl" but_name="dispatch[newsletters.add_campaign]" cancel_action="close" text=__("add_campaign")}
            </div>
        </form>
    {/capture}
    {include file="common/popupbox.tpl" id="add_new_campaign" text=__("new_campaign") title=__("add_campaign") act="general" content=$smarty.capture.add_new_picker icon="icon-plus"}
{/capture}

{/capture}
{include file="common/mainbox.tpl" title=__("newsletters") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons select_languages=true}