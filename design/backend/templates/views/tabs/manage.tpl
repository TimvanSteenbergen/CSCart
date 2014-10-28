{script src="js/tygh/tabs.js"}

{capture name="mainbox"}

{assign var="r_url" value=$config.current_url|escape:url}
{if "tabs.update"|fn_check_view_permissions}
    {assign var="non_editable" value=false}
{else}
    {assign var="non_editable" value=true}
{/if}
<div class="items-container {if !$dynamic_object}cm-sortable{/if}" data-ca-sortable-table="product_tabs" data-ca-sortable-id-name="tab_id"  id="manage_tabs_list">

<table width="100%" class="table table-middle table-objects">
    <tbody>
{foreach from=$product_tabs item="tab"}
    {if $tab.is_primary == "Y" || $dynamic_object || $non_editable}
        {assign var="_href_delete" value=""}
    {else}
        {assign var="_href_delete" value="tabs.delete?tab_id=`$tab.tab_id`"}
    {/if}

    {if $dynamic_object}
        {assign var="dynamic_object_href" value="&dynamic_object[object_type]=`$dynamic_object.object_type`&dynamic_object[object_id]=`$dynamic_object.object_id`&selected_location=`$location.location_id`&hide_status=1"}
        {assign var="r_url" value="products.update?product_id=`$dynamic_object.object_id`&selected_section=product_tabs"|urlencode}
        {assign var="additional_class" value=""}
        {assign var="draggable" value=false}
        {assign var="_href_update" value=""}
    {else}
        {assign var="dynamic_object_href" value=""}
        {assign var="r_url" value="tabs.manage"}
        {assign var="additional_class" value="cm-sortable-row cm-sortable-id-`$tab.tab_id`"}
        {assign var="draggable" value=true}
        {assign var="_href_update" value="tabs.update?tab_data[tab_id]=`$tab.tab_id`&return_url=`$r_url`"}
    {/if}

    {if $tab.product_ids}
        {assign var="confirm" value=true}
    {else}
        {assign var="confirm" value=""}
    {/if}

    {capture name = "tool_items"}{strip}
        {if $tab.tab_type == "B"}
            <span class="small-note lowercase">{strip}(
                {if $tab.block_id && $dynamic_object}
                    {include file="common/popupbox.tpl"
                        id="edit_block_properties_`$tab.block_id`_tab_`$tab.tab_id`"
                        text=__("block_settings")
                        link_text=__("block_settings")
                        act="link"
                        href="block_manager.update_block?block_data[block_id]=`$tab.block_id`&r_url=`$r_url`&html_id=tab_`$tab.tab_id``$dynamic_object_href`"
                        action="block_manager.update_block"
                        opener_ajax_class="cm-ajax"
                        link_class="cm-ajax-force"
                        content=""
                    }
                {else}
                    {__("block")}
                {/if}
            ){/strip}</span>
        {/if}
    {/strip}{/capture}
    {include
        file="common/object_group.tpl"
        id=$tab.tab_id
        text=$tab.name
        href=$_href_update
        href_delete=$_href_delete
        delete_target_id="pagination_contents"
        header_text="{__("editing_tab")}: `$tab.name`"
        table="product_tabs"
        object_id_name="tab_id"
        draggable=$draggable
        update_controller='tabs'
        dynamic_object=$dynamic_object_href
        status=$tab.status
        additional_class=$additional_class
        href_desc=$smarty.capture.tool_items
        non_editable=$dynamic_object
        no_table=true
        can_change_status=true
    }
{foreachelse}

    <p class="no-items">{__("no_data")}</p>

{/foreach}
    </tbody>
</table>
<!--manage_tabs_list--></div>

<div class="buttons-container">
    {capture name="extra_tools"}
        {hook name="currencies:import_rates"}{/hook}
    {/capture}
</div>

{if !$dynamic_object}
    {capture name="adv_buttons"}
        {include file="common/popupbox.tpl"
            act="general"
            id="add_tab"
            text=__("new_tab")
            title=__("add_tab")
            icon="icon-plus"
            act="general"
            href="tabs.update"
            action="tabs.update"
            opener_ajax_class="cm-ajax"
            link_class="cm-ajax-force"
            content=""}
    {/capture}
{/if}

{/capture}

{if !$dynamic_object}
    {include file="common/mainbox.tpl" title=__("product_tabs") content=$smarty.capture.mainbox adv_buttons=$smarty.capture.adv_buttons select_languages=true}
{else}
    {$smarty.capture.mainbox nofilter}
{/if}

