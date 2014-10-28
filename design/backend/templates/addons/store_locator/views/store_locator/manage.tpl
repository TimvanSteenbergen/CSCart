{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="store_locator_form" class="cm-hide-inputs">
<input type="hidden" name="fake" value="1" />

{include file="common/pagination.tpl" save_current_page=true}


<div class="items-container" id="store_locations">
    {if $store_locations}
    <table class="table table-middle">

    <thead>
    <tr>
        <th width="1%">
            {include file="common/check_items.tpl" class="cm-no-hide-input"}
        </th>
        <th width="40%" class="shift-left">{__("store_locator")}</th>
        <th width="5%">&nbsp;</th>
        <th class="right" width="10%">{__("status")}</th>
    </tr>
    </thead>

        {foreach from=$store_locations item=loc}
        <tbody>
        <tr class="cm-row-status-{$loc.status|lower}" valign="top" >

            {assign var="allow_save" value=$loc|fn_allow_save_object:"store_locations"}
            {if $allow_save}
                {assign var="no_hide_input" value="cm-no-hide-input"}
                {assign var="display" value=""}
            {else}
                {assign var="no_hide_input" value=""}
                {assign var="display" value="text"}
            {/if}

            <td class="left {$no_hide_input}">
                <input type="checkbox" name="store_locator_ids[]" value="{$loc.store_location_id}" class="cm-item" /></td>

            <td class="{$no_hide_input}">
                <a class="row-status" href="{"store_locator.update?store_location_id=`$loc.store_location_id`"|fn_url}">{$loc.name}</a>
                {include file="views/companies/components/company_name.tpl" object=$loc}
            </td>

            <td class="center nowrap">
                {capture name="tools_list"}
                    {if $allow_save}
                        <li>{btn type="list" text=__("edit") href="store_locator.update?store_location_id=`$loc.store_location_id`"}</li>
                        <li>{btn type="list" class="cm-confirm" text=__("delete") href="store_locator.delete?store_location_id=`$loc.store_location_id`"}</li>
                    {/if}
                {/capture}
                <div class="hidden-tools right">
                    {dropdown content=$smarty.capture.tools_list}
                </div>
            </td>
            <td class="right nowrap">
                {include file="common/select_popup.tpl" id=$loc.store_location_id status=$loc.status hidden="" object_id_name="store_location_id" table="store_locations" popup_additional_class="`$no_hide_input`" display=$display}
            </td>

        </tr>
        </tbody>
        {/foreach}
    </table>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}
<!--store_locations--></div>


    {include file="common/pagination.tpl" save_current_page=true}

    {capture name="adv_buttons"}
        {include file="common/tools.tpl" tool_href="store_locator.add" prefix="top" title=__("add_store_location") hide_tools=true}
    {/capture}

{/capture}
{include file="common/mainbox.tpl" title=__("store_locator") content=$smarty.capture.mainbox adv_buttons=$smarty.capture.adv_buttons select_languages=true buttons=$smarty.capture.buttons}
