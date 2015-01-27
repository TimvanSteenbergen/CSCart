{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="rma_properties_form">
<input type="hidden" name="property_type" value="{$smarty.request.property_type|default:$smarty.const.RMA_REASON}" />

{if $properties}
<table width="100%" class="table table-middle">
<thead>
<tr>
    {if $smarty.request.property_type == $smarty.const.RMA_REASON}
    <th width="1%" class="center">
        {include file="common/check_items.tpl"}</th>
    {/if}
    <th width="7%">{__("position")}</th>
    <th width="35%">{if $smarty.request.property_type == "R"}{__("reason")}{else}{__("action")}{/if}</th>
    {if $smarty.request.property_type != $smarty.const.RMA_REASON}
    <th width="30%" class="center">{__("update_totals_and_inventory")}</th>
    {/if}
    <th width="10%">&nbsp;</th>
    <th width="10%" class="right">{__("status")}</th>
</tr>
</thead>
{foreach from=$properties item=property}
<tr class="cm-row-status-{$property.status|lower}">
    {if $smarty.request.property_type == $smarty.const.RMA_REASON}
    <td class="center">
        <input type="checkbox" name="property_ids[]" value="{$property.property_id}" class="checkbox cm-item" /></td>
    {/if}
    <td>
        <input type="text" name="property_data[{$property.property_id}][position]" size="7" value="{$property.position}" class="input-hidden input-micro" /></td>
    <td>
        <input type="text" name="property_data[{$property.property_id}][property]" size="35" value="{$property.property}" class="input-hidden input-xlarge" /></td>
    {if $smarty.request.property_type != $smarty.const.RMA_REASON}
    <td class="center">
        <input type="checkbox" value="{$property.update_totals_and_inventory}" {if $property.update_totals_and_inventory == "Y"}checked="checked"{/if} disabled="disabled" class="checkbox" /></td>
    {/if}
    <td class="nowrap right">
        {capture name="tools_list"}
        {if $smarty.request.property_type == $smarty.const.RMA_REASON}
            {assign var="property_type" value=$smarty.request.property_type|default:$smarty.const.RMA_REASON}
            <li>{btn type="list" class="cm-confirm" text=__("delete") href="rma.delete_property?property_id=`$property.property_id`&property_type=`$property_type`"}</li>
        {else}
            <li class="disabled"><a class="undeleted-element cm-tooltip" title="{__("delete")}">{__("delete")}</a></li>
        {/if}
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
    <td class="right nowrap">
        {include file="common/select_popup.tpl" id=$property.property_id status=$property.status hidden="" object_id_name="property_id" table="rma_properties"}
    </td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{capture name="buttons"}
    {capture name="tools_list"}
        {if $properties && $smarty.request.property_type == $smarty.const.RMA_REASON}
            <li>{btn type="delete_selected" dispatch="dispatch[rma.m_delete_properties]" form="rma_properties_form"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}

    {if $properties}
        {include file="buttons/save.tpl" but_name="dispatch[rma.update_properties]" but_role="submit-link" but_target_form="rma_properties_form"}
    {/if}
{/capture}

{capture name="adv_buttons"}
    {if $smarty.request.property_type == $smarty.const.RMA_REASON}
        {capture name="add_new_picker"}
            <form action="{""|fn_url}" method="post" name="add_rma_properties_form" class="form-horizontal form-edit ">
                <input type="hidden" name="property_type" value="{$smarty.request.property_type|default:$smarty.const.RMA_REASON}" />

                <div class="tabs cm-j-tabs">
                    <ul class="nav nav-tabs">
                        <li id="tab_rma_new" class="cm-js active"><a>{__("general")}</a></li>
                    </ul>
                </div>

                <div class="cm-tabs-content" id="content_tab_rma_new">
                    <div class="control-group">
                        <label class="control-label cm-required" for="add_property_data">{if $smarty.request.property_type == $smarty.const.RMA_REASON}{__("reason")}{else}{__("action")}{/if}</label>
                        <div class="controls">
                            <input type="text" name="add_property_data[0][property]" id="add_property_data" size="35" value="" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="add_property_position">{__("position")}</label>
                        <div class="controls">
                            <input type="text" name="add_property_data[0][position]" id="add_property_position" size="7" value="" />
                        </div>
                    </div>
                    {include file="common/select_status.tpl" input_name="add_property_data[0][status]" id="add_property_data"}
                </div>

                <div class="buttons-container">
                    {include file="buttons/save_cancel.tpl" but_name="dispatch[rma.add_properties]" cancel_action="close"}
                </div>

            </form>
        {/capture}
        {include file="common/popupbox.tpl" id="add_new_reasons" text=__("new_reason") content=$smarty.capture.add_new_picker title=__("add_reason") act="general" icon="icon-plus"}
    {/if}
{/capture}

</form>

{/capture}
{if $smarty.request.property_type == $smarty.const.RMA_REASON}
    {include file="common/mainbox.tpl" title=__("rma_reasons") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons select_languages=true}
{else}
    {include file="common/mainbox.tpl" title=__("rma_actions") content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons select_languages=true}
{/if}