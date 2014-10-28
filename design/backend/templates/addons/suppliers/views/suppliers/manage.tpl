{include file="views/profiles/components/profiles_scripts.tpl"}

{capture name="mainbox"}

    <form action="{""|fn_url}" method="post" name="suppliers_list_form" id="suppliers_list_form">
        <input type="hidden" name="fake" value="1" />

        {assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
        {assign var="c_icon" value="<i class=\"exicon-`$search.sort_order_rev`\"></i>"}
        {assign var="c_dummy" value="<i class=\"exicon-dummy\"></i>"}

        {include file="common/pagination.tpl" save_current_page=true save_current_url=true}

        {if $suppliers}
        <table width="100%" class="table table-middle">
            <thead>
                <tr>
                    <th width="1%" class="left">
                        {include file="common/check_items.tpl"}
                    </th>
                    <th width="5%"><a class="cm-ajax" href="{"`$c_url`&sort_by=id&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("id")}{if $search.sort_by == "id"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                    <th width="25%"><a class="cm-ajax" href="{"`$c_url`&sort_by=name&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("name")}{if $search.sort_by == "name"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                    <th width="20%"><a class="cm-ajax" href="{"`$c_url`&sort_by=email&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("email")}{if $search.sort_by == "email"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                    <th width="15%"><a class="cm-ajax" href="{"`$c_url`&sort_by=date&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("registered")}{if $search.sort_by == "date"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>

                    {hook name="suppliers:manage_header"}{/hook}

                    <th width="15%" class="">&nbsp;</th>
                    <th width="10%" class="right"><a class="cm-ajax" href="{"`$c_url`&sort_by=status&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("status")}{if $search.sort_by == "status"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                </tr>
            </thead>

            {foreach from=$suppliers item=supplier}

                {assign var="allow_save" value=$supplier|fn_allow_save_object:"suppliers"}
                {if !$allow_save && !"RESTRICTED_ADMIN"|defined && $auth.is_root != 'Y'}
                    {assign var="link_text" value=__("view")}
                    {assign var="popup_additional_class" value=""}
                {elseif $allow_save || "RESTRICTED_ADMIN"|defined || $auth.is_root == 'Y'}
                    {assign var="link_text" value="edit"}
                    {assign var="popup_additional_class" value="cm-no-hide-input"}
                {else}
                    {assign var="popup_additional_class" value=""}
                    {assign var="link_text" value="edit"}
                {/if}

                <tr class="cm-row-status-{$supplier.status|lower}">
                    <td class="center {$no_hide_input}">
                        <input type="checkbox" name="supplier_ids[]" value="{$supplier.supplier_id}" class="checkbox cm-item" />
                    </td>
                    <td><a class="row-status" href="{"suppliers.update?supplier_id=`$supplier.supplier_id`"|fn_url}">{$supplier.supplier_id}</a></td>
                    <td class="row-status">{if $supplier.name}<a href="{"suppliers.update?supplier_id=`$supplier.supplier_id`"|fn_url}">{$supplier.name}</a>{else}-{/if}{include file="views/companies/components/company_name.tpl" object=$supplier}</td>
                    <td width="25%"><a class="row-status" href="mailto:{$supplier.email|escape:url}">{$supplier.email}</a></td>
                    <td class="row-status">{$supplier.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>

                    {hook name="suppliers:manage_data"}{/hook}

                    <td class="center">
                        <div class="hidden-tools">
                        {capture name="tools_list"}
                            {hook name="companies:list_extra_links"}
                                {assign var="return_current_url" value=$config.current_url|escape:url}
                                <li>{btn type="list" text=__("view_supplier_products") href="products.manage?supplier_id=`$supplier.supplier_id`"}</li>
                                <li class="divider"></li>
                                <li>{btn type="list" text=__($link_text) href="suppliers.update?supplier_id=`$supplier.supplier_id`"}</li>
                                {assign var="return_current_url" value=$config.current_url|escape:url}
                                <li>{btn type="list" class="cm-confirm" text=__("delete") href="suppliers.delete?supplier_id=`$supplier.supplier_id`&redirect_url=`$return_current_url`"}</li>
                            {/hook}
                        {/capture}
                        {dropdown content=$smarty.capture.tools_list}
                        </div>
                    </td>
                    <td class="right">
                        {include file="common/select_popup.tpl" id=$supplier.supplier_id status=$supplier.status hidden="" update_controller="suppliers" notify=true notify_text=__("notify_supplier") popup_additional_class="`$popup_additional_class` dropleft"}
                    </td>
                </tr>
            {/foreach}
        </table>
        {else}
            <p class="no-items">{__("no_data")}</p>
        {/if}

        {include file="common/pagination.tpl"}

        {capture name="buttons"}
            {capture name="tools_items"}
                {if $suppliers}
                    <li>{btn type="delete_selected" dispatch="dispatch[suppliers.m_delete]" form="suppliers_list_form"}</li>
                {/if}
            {/capture}
            {dropdown content=$smarty.capture.tools_items}
        {/capture}
    </form>
{/capture}

{capture name="adv_buttons"}
    {btn type="add" title=__("add_supplier") href="suppliers.add"}
{/capture}

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="suppliers.manage" view_type="suppliers"}
    {include file="views/profiles/components/users_search_form.tpl" dispatch="suppliers.manage"}
{/capture}

{include file="common/mainbox.tpl" title=__("suppliers") content=$smarty.capture.mainbox sidebar=$smarty.capture.sidebar adv_buttons=$smarty.capture.adv_buttons buttons=$smarty.capture.buttons}