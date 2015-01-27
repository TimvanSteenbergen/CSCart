{capture name="mainbox"}

<form action="{""|fn_url}" method="post" enctype="multipart/form-data" name="gift_cert_list_form">

{include file="common/pagination.tpl" save_current_url=true}

{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
{assign var="c_icon" value="<i class=\"exicon-`$search.sort_order_rev`\"></i>"}
{assign var="c_dummy" value="<i class=\"exicon-dummy\"></i>"}

{if $gift_certificates}
<table class="table table-middle sortable">
<thead>
    <tr>
        <th class="center" width="1%">
            {include file="common/check_items.tpl"}</th>
        <th width="15%"><a class="cm-ajax{if $search.sort_by == "gift_cert_code"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=gift_cert_code&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("code")}{if $search.sort_by == "gift_cert_code"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
        <th width="10%"><a class="cm-ajax{if $search.sort_by == "sender"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=sender&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("gift_cert_from")}{if $search.sort_by == "sender"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
        <th width="15%"><a class="cm-ajax{if $search.sort_by == "recipient"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=recipient&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("gift_cert_to")}{if $search.sort_by == "recipient"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
        <th width="10%"><a class="cm-ajax{if $search.sort_by == "send_via"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=send_via&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("type")}{if $search.sort_by == "send_via"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
        <th width="15%"><a class="cm-ajax{if $search.sort_by == "timestamp"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=timestamp&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("date")}{if $search.sort_by == "timestamp"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
        <th width="15%">{__("current_amount")}</th>
        <th width="5%">&nbsp;</th>
        <th width="10%" class="right"><a class="cm-ajax{if $search.sort_by == "status"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=status&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("status")}{if $search.sort_by == "status"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    </tr>
</thead>
{assign var="gift_status_descr" value=$smarty.const.STATUSES_GIFT_CERTIFICATE|fn_get_simple_statuses}
<tbody>
{foreach from=$gift_certificates item="gift"}
<tr class="cm-row-status-{$gift.status|lower}">
    <td class="left" width="1%">
        <input type="checkbox" name="gift_cert_ids[]" value="{$gift.gift_cert_id}" class="checkbox cm-item" /></td>
    <td>
        <a href="{"gift_certificates.update?gift_cert_id=`$gift.gift_cert_id`"|fn_url}" class="nowrap row-status">{$gift.gift_cert_code}</a>
        {include file="views/companies/components/company_name.tpl" object=$gift}
    </td>
    <td class="row-status">{$gift.sender}</td>
    <td class="row-status">{$gift.recipient}</td>
    <td class="row-status"><span class="nowrap">{if $gift.send_via == "P"}{__("mail")}{else}{__("email")}</span><br>({$gift.email}){/if}</td>
    <td class="row-status"><a href="{"gift_certificates.update?gift_cert_id=`$gift.gift_cert_id`"|fn_url}" class="underlined">{$gift.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</a></td>
    <td class="row-status">{include file="common/price.tpl" value=$gift.debit}</td>
    <td class="nowrap">
        <div class="hidden-tools">
            {capture name="tools_list"}
                <li>{btn type="list" text=__("edit") href="gift_certificates.update?gift_cert_id=`$gift.gift_cert_id`"}</li>
                <li>{btn type="list" class="cm-confirm" text=__("delete") href="gift_certificates.delete?gift_cert_id=`$gift.gift_cert_id`"}</li>
            {/capture}
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
    <td class="right nowrap">
        {include file="common/select_popup.tpl" id=$gift.gift_cert_id status=$gift.status items_status=$gift_status_descr update_controller="gift_certificates" notify=true statuses=$gift_statuses popup_additional_class="dropleft"}
    </td>
</tr>
{/foreach}
</tbody>
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl"}

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="gift_certificates.manage" view_type="gift_certs"}
    {include file="addons/gift_certificates/views/gift_certificates/components/gift_certificates_search_form.tpl"}
{/capture}

{capture name="buttons"}
    {if $gift_certificates}
        {capture name="tools_list"}
            <li>{btn type="delete_selected" dispatch="dispatch[gift_certificates.m_delete]" form="gift_cert_list_form"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
    {/if}
{/capture}

{capture name="adv_buttons"}
    {include file="common/tools.tpl" tool_href="gift_certificates.add" prefix="top" hide_tools=true title=__("add_gift_certificate") icon="icon-plus"}
{/capture}

</form>

{/capture}
{include file="common/mainbox.tpl" title=__("gift_certificates") content=$smarty.capture.mainbox title_extra=$smarty.capture.title_extra tools=$smarty.capture.tools sidebar=$smarty.capture.sidebar adv_buttons=$smarty.capture.adv_buttons buttons=$smarty.capture.buttons}
