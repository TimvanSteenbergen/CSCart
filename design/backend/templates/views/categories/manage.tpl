{capture name="mainbox"}
{$hide_inputs = ""|fn_check_form_permissions}
<form action="{""|fn_url}" method="post" name="category_tree_form" class="{if $hide_inputs}cm-hide-inputs{/if}">
    <div class="items-container">
    {if $categories_tree}
        {include file="views/categories/components/categories_tree.tpl" header="1" parent_id=$category_id st_result_ids="categories_stats" st_return_url=$config.current_url}
    {else}
        <p class="no-items">{__("no_items")}</p>
    {/if}
</div>

{capture name="select_fields_to_edit"}

    <p>{__("text_select_fields2edit_note")}</p>
    {include file="views/categories/components/categories_select_fields.tpl"}

    <div class="buttons-container">
        {include file="buttons/save_cancel.tpl" but_text=__("modify_selected") but_meta="cm-process-items" but_name="dispatch[categories.store_selection]" cancel_action="close"}
    </div>
{/capture}

{include file="common/popupbox.tpl" id="select_fields_to_edit" text=__("select_fields_to_edit") content=$smarty.capture.select_fields_to_edit}

{capture name="buttons"}
    {capture name="tools_list"}
        <li>{btn type="list" text=__("bulk_category_addition") href="categories.m_add"}</li>
        {if $categories_tree}
            {if !$hide_inputs}
            <li class="divider"></li>
            <li>{btn type="dialog" class="cm-process-items" text=__("edit_selected") target_id="content_select_fields_to_edit" form="category_tree_form"}</li>
            {/if}
            <li>{btn type="delete_selected" dispatch="dispatch[categories.m_delete]" form="category_tree_form" data=["data-ca-confirm-text" => "{__("bulk_category_deletion_side_effects")}"]}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}

    {if $categories_tree}
        {include file="buttons/save.tpl" but_name="dispatch[categories.m_update]" but_role="submit-link" but_target_form="category_tree_form"}
    {/if}
{/capture}

{capture name="adv_buttons"}
    {include file="common/tools.tpl" tool_href="categories.add" prefix="top" hide_tools="true" title=__("add_category") icon="icon-plus"}
{/capture}

{capture name="sidebar"}
    <div class="sidebar-row" id="categories_stats">
        <h6>{__("total")}</h6>
        <ul class="unstyled sidebar-stat">
            <li>{__("categories")} <span>{$categories_stats.categories_total}</span></li>
            <li>{__("products")} <span>{$categories_stats.products_total}</span></li>
            <li>{__("active_categories")} <span>{$categories_stats.categories_active}</span></li>
            <li>{__("hidden_categories")} <span>{$categories_stats.categories_hidden}</span></li>
            <li>{__("disabled_categories")} <span>{$categories_stats.categories_disabled}</span></li>
        </ul>
    <!--categories_stats--></div>
{/capture}

</form>
{/capture}
{include file="common/mainbox.tpl" title=__("categories") content=$smarty.capture.mainbox  buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar adv_buttons=$smarty.capture.adv_buttons select_languages=true}
