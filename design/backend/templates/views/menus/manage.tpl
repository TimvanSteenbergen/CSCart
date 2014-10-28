{script src="js/tygh/tabs.js"}

{capture name="mainbox"}

{assign var="r_url" value=$config.current_url|escape:url}

<div class="items-container" id="manage_tabs_list">

{if $menus}
<table class="table table-middle table-objects">
    {foreach from=$menus item="menu"}
        {assign var="_href_delete" value="menus.delete?menu_id=`$menu.menu_id`"}        
        {assign var="dialog_name" value="{__("editing_menu")}: `$menu.name`"}
        {assign var="name" value=$menu.name}
        {assign var="edit_link" value="menus.update?menu_data[menu_id]=`$menu.menu_id`&return_url=$r_url"}
        {capture name = "items_link"}            
            <li>{btn type="list" text=__("manage_items") href="static_data.manage?section=A&menu_id=`$menu.menu_id`"}</li>
            <li class="divider"></li>
        {/capture}
        {include file="common/object_group.tpl" id=$menu.menu_id text=$name href=$edit_link href_delete=$_href_delete delete_target_id="manage_tabs_list" header_text=$dialog_name table="menus" object_id_name="menu_id" status=$menu.status tool_items=$smarty.capture.items_link no_table=true}
    {/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

<!--manage_tabs_list--></div>

<div class="buttons-container">
    {capture name="extra_tools"}
        {hook name="currencies:import_rates"}{/hook}
    {/capture}
</div>

{capture name="adv_buttons"}
    {include file="common/popupbox.tpl"
        act="general"
        id="add_menu"
        text=__("new_menu")
        title=__("add_menu")
        act="general"
        href="menus.update"
        opener_ajax_class="cm-ajax"
        icon="icon-plus"
        content=""}
{/capture}

{/capture}

{include file="common/mainbox.tpl" title=__("menus") content=$smarty.capture.mainbox select_languages=true adv_buttons=$smarty.capture.adv_buttons}
