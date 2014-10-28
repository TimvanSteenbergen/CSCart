{include file="views/profiles/components/profiles_scripts.tpl"}

{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="companies_form" id="companies_form">
<input type="hidden" name="fake" value="1" />

{include file="common/pagination.tpl" save_current_page=true save_current_url=true}

{assign var="return_current_url" value=$config.current_url|escape:url}
{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
{assign var="c_icon" value="<i class=\"exicon-`$search.sort_order_rev`\"></i>"}
{assign var="c_dummy" value="<i class=\"exicon-dummy\"></i>"}

{if $companies}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th width="1%" class="left">
        {include file="common/check_items.tpl"}</th>
    <th width="6%"><a class="cm-ajax" href="{"`$c_url`&sort_by=id&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("id")}{if $search.sort_by == "id"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th width="25%"><a class="cm-ajax" href="{"`$c_url`&sort_by=company&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("name")}{if $search.sort_by == "company"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    {if !"ULTIMATE"|fn_allowed_for}
        <th width="25%"><a class="cm-ajax" href="{"`$c_url`&sort_by=email&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("email")}{if $search.sort_by == "email"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    {/if}
    {if "ULTIMATE"|fn_allowed_for}
        <th width="25%"><a class="cm-ajax" href="{"`$c_url`&sort_by=storefront&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("storefront")}{if $search.sort_by == "storefront"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    {/if}
    <th width="20%"><a class="cm-ajax" href="{"`$c_url`&sort_by=date&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("registered")}{if $search.sort_by == "date"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th width="10%" class="nowrap">&nbsp;</th>
    {if !"ULTIMATE"|fn_allowed_for}
        <th width="10%" class="right"><a class="cm-ajax" href="{"`$c_url`&sort_by=status&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{if $search.sort_by == "status"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}{__("status")}</a></th>
    {/if}
</tr>
</thead>
{foreach from=$companies item=company}
<tr class="cm-row-status-{$company.status|lower}" data-ct-company-id="{$company.company_id}">
    <td class="left">
        <input type="checkbox" name="company_ids[]" value="{$company.company_id}" class="cm-item" /></td>
    <td class="row-status"><a href="{"companies.update?company_id=`$company.company_id`"|fn_url}">&nbsp;<span>{$company.company_id}</span>&nbsp;</a></td>
    <td class="row-status"><a href="{"companies.update?company_id=`$company.company_id`"|fn_url}">{$company.company}</a></td>
    {if !"ULTIMATE"|fn_allowed_for}
        <td class="row-status"><a href="mailto:{$company.email}">{$company.email}</a></td>
    {/if}
    {if "ULTIMATE"|fn_allowed_for}
        <td><a href="http://{$company.storefront}">{$company.storefront|unpuny}</a></td>
    {/if}
    <td class="row-status">{$company.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
    <td class="nowrap">
        {capture name="tools_items"}
        {hook name="companies:list_extra_links"}
            <li>{btn type="list" href="products.manage?company_id=`$company.company_id`" text=__("view_vendor_products")}</li>
            <li>{btn type="list" href="profiles.manage?company_id=`$company.company_id`" text=__("view_vendor_users")}</li>
            <li>{btn type="list" href="orders.manage?company_id=`$company.company_id`" text=__("view_vendor_orders")}</li>
            {if !"ULTIMATE"|fn_allowed_for && !$runtime.company_id}
                <li>{btn type="list" href="companies.merge?company_id=`$company.company_id`" text=__("merge")}</li>
            {/if}
            {if !$runtime.company_id && fn_check_view_permissions("companies.update", "POST")}
                <li class="divider"></li>
                <li>{btn type="list" href="companies.update?company_id=`$company.company_id`" text=__("edit")}</li>
                <li>{btn type="list" class="cm-confirm" href="companies.delete?company_id=`$company.company_id`&redirect_url=`$return_current_url`" text=__("delete")}</li>
            {/if}
        {/hook}
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_items}
        </div>
    </td>
    {if !"ULTIMATE"|fn_allowed_for}
        <td class="right nowrap">
            {assign var="notify" value=true}
            {include file="common/select_popup.tpl" id=$company.company_id status=$company.status object_id_name="company_id" hide_for_vendor=$runtime.company_id update_controller="companies" notify=$notify notify_text=__("notify_vendor")}
        </td>
    {/if}
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{if $companies}
    {if !$runtime.company_id}
    {capture name="activate_selected"}
        {include file="views/companies/components/reason_container.tpl" type="activate"}
        <div class="buttons-container">
            {include file="buttons/save_cancel.tpl" but_text=__("proceed") but_name="dispatch[companies.m_activate]" cancel_action="close" but_meta="cm-process-items"}
        </div>
    {/capture}
    {include file="common/popupbox.tpl" id="activate_selected" text=__("activate_selected") content=$smarty.capture.activate_selected link_text=__("activate_selected")}

    {capture name="disable_selected"}
        {include file="views/companies/components/reason_container.tpl" type="disable"}
        <div class="buttons-container">
            {include file="buttons/save_cancel.tpl" but_text=__("proceed") but_name="dispatch[companies.m_disable]" cancel_action="close" but_meta="cm-process-items"}
        </div>
    {/capture}
    {include file="common/popupbox.tpl" id="disable_selected" text=__("disable_selected") content=$smarty.capture.disable_selected link_text=__("disable_selected")}
    {/if}
{/if}

{include file="common/pagination.tpl"}
</form>
{/capture}
{capture name="buttons"}
    {capture name="tools_items"}
        {hook name="companies:manage_tools_list"}
            {if !$runtime.company_id && fn_check_view_permissions("companies.update", "POST")}
                <li>{btn type="delete_selected" dispatch="dispatch[companies.m_delete]" form="companies_form"}</li>
            {/if}
        {/hook}
    {/capture}
    {dropdown content=$smarty.capture.tools_items}

    {if $companies && !$runtime.company_id}
        {if !"ULTIMATE"|fn_allowed_for}
            {assign var="but_class" value="cm-process-items cm-dialog-opener btn-primary"}
            {include file="buttons/button.tpl" but_target_id="content_activate_selected" but_target_form="companies_form" but_text=__("activate_selected") but_meta=$but_class but_role="button_main" but_name=$but_name}
            {include file="buttons/button.tpl" but_target_id="content_disable_selected" but_target_form="companies_form" but_text=__("disable_selected") but_meta=$but_class but_role="button_main" but_name=$but_name}
        {/if}
    {/if}
{/capture}

{capture name="adv_buttons"}
    {include file="common/tools.tpl" tool_href="companies.add" prefix="top" hide_tools=true title=__("add_vendor") icon="icon-plus"}

{/capture}

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="companies.manage" view_type="companies"}
    {include file="views/companies/components/companies_search_form.tpl" dispatch="companies.manage"}
{/capture}

{include file="common/mainbox.tpl" title=__("vendors") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar}
