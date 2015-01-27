{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="promotion_form" class="{if ""|fn_check_form_permissions} cm-hide-inputs{/if}">

{include file="common/pagination.tpl" save_current_page=true save_current_url=true}

{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
{assign var="c_icon" value="<i class=\"exicon-`$search.sort_order_rev`\"></i>"}
{assign var="c_dummy" value="<i class=\"exicon-dummy\"></i>"}

{if $promotions}
<table class="table table-middle">
<thead>
<tr>
    <th width="1%">
        {include file="common/check_items.tpl"}
    </th>
    <th width="30%">
        <a class="cm-ajax" href="{"`$c_url`&sort_by=name&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("name")}{if $search.sort_by == "name"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th width="10%" class="nowrap center">
        <a class="cm-ajax" href="{"`$c_url`&sort_by=priority&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("priority")}{if $search.sort_by == "priority"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th width="10%">
        <a class="cm-ajax" href="{"`$c_url`&sort_by=zone&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("zone")}{if $search.sort_by == "zone"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th width="10%">&nbsp;</th>
    <th width="10%" class="right"><a class="cm-ajax" href="{"`$c_url`&sort_by=status&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("status")}{if $search.sort_by == "status"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
</tr>
</thead>

{foreach from=$promotions item=promotion}

    {assign var="allow_save" value=$promotion|fn_allow_save_object:"promotions"}

    {if $allow_save}
        {assign var="link_text" value=__("edit")}
        {assign var="additional_class" value="cm-no-hide-input"}
        {assign var="status_display" value=""}
    {else}
        {assign var="link_text" value=__("view")}
        {assign var="additional_class" value="cm-hide-inputs"}
        {assign var="status_display" value="text"}
    {/if}

<tr class="cm-row-status-{$promotion.status|lower} {$additional_class}">
    <td>
        <input name="promotion_ids[]" type="checkbox" value="{$promotion.promotion_id}" class="cm-item" /></td>
    <td>
        <a class="row-status" href="{"promotions.update?promotion_id=`$promotion.promotion_id`"|fn_url}">{$promotion.name}</a>
        {include file="views/companies/components/company_name.tpl" object=$promotion}
    <td class="center">
        <span>{$promotion.priority}</span>
    </td>
    <td>
        <span class="row-status">{__($promotion.zone)}</span>
    </td>
    <td class="right">
        <div class="hidden-tools">
        {capture name="tools_list"}
            <li>{btn type="list" text=$link_text href="promotions.update?promotion_id=`$promotion.promotion_id`"}</li>
            {if $allow_save}
                <li>{btn type="list" text=__("delete") class="cm-confirm" href="promotions.delete?promotion_id=`$promotion.promotion_id`"}</li>
            {/if}
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
    <td class="nowrap right">
        {include file="common/select_popup.tpl" popup_additional_class="dropleft" display=$status_display id=$promotion.promotion_id status=$promotion.status hidden=true object_id_name="promotion_id" table="promotions"}
    </td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl"}

{capture name="buttons"}
    {capture name="tools_list"}
        {hook name="promotions:manage_tools_list"}
            {if $promotions}
                <li>{btn type="delete_selected" dispatch="dispatch[promotions.m_delete]" form="promotion_form"}</li>
            {/if}
        {/hook}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}

{capture name="adv_buttons"}
    {capture name="tools_list"}
        <li>{btn type="list" text=__("add_catalog_promotion") href="promotions.add?zone=catalog"}</li>
        {if !"ULTIMATE:FREE"|fn_allowed_for}
            <li>{btn type="list" text=__("add_cart_promotion") href="promotions.add?zone=cart"}</li>
        {else}
            <li>{btn type="list" text=__("add_cart_promotion") class="cm-promo-popup"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list icon="icon-plus" no_caret=true placement="right"}
    {** Hook for the actions menu on the products manage page *}
{/capture}

</form>
{/capture}
{include file="common/mainbox.tpl" title=__("promotions") content=$smarty.capture.mainbox tools=$smarty.capture.tools select_languages=true buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}