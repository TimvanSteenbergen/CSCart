{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="manage_shipments_form">

{include file="common/pagination.tpl" save_current_page=true save_current_url=true}

{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
{assign var="c_icon" value="<i class=\"exicon-`$search.sort_order_rev`\"></i>"}
{assign var="c_dummy" value="<i class=\"exicon-dummy\"></i>"}

{if $shipments}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th class="center" width="5%">
        {include file="common/check_items.tpl"}
    </th>
    <th width="15%">
        <a class="cm-ajax" href="{"`$c_url`&sort_by=id&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("shipment_id")}{if $search.sort_by == "id"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>
    </th>
    <th width="15%">
        <a class="cm-ajax" href="{"`$c_url`&sort_by=order_id&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("order_id")}{if $search.sort_by == "order_id"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>
    </th>
    <th width="15%">
        <a class="cm-ajax" href="{"`$c_url`&sort_by=shipment_date&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("shipment_date")}{if $search.sort_by == "shipment_date"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>
    </th>
    <th width="15%">
        <a class="cm-ajax" href="{"`$c_url`&sort_by=order_date&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("order_date")}{if $search.sort_by == "order_date"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>
    </th>
    <th width="40%">
        <a class="cm-ajax" href="{"`$c_url`&sort_by=customer&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("customer")}{if $search.sort_by == "customer"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>
    </th>
    
    <th>&nbsp;</th>
</tr>
</thead>
{foreach from=$shipments item=shipment}
<tr>
    <td class="center">
           <input type="checkbox" name="shipment_ids[]" value="{$shipment.shipment_id}" class=" cm-item" />
       </td>
    <td>
        <a class="underlined" href="{"shipments.details?shipment_id=`$shipment.shipment_id`"|fn_url}"><span>#{$shipment.shipment_id}</span></a>
    </td>
    <td>
        <a class="underlined" href="{"orders.details?order_id=`$shipment.order_id`"|fn_url}"><span>#{$shipment.order_id}</span></a>
    </td>
    <td>
        {if $shipment.shipment_timestamp}{$shipment.shipment_timestamp|date_format:"`$settings.Appearance.date_format`"}{else}--{/if}
    </td>
    <td>
        {if $shipment.order_timestamp}{$shipment.order_timestamp|date_format:"`$settings.Appearance.date_format`"}{else}--{/if}
    </td>
    <td>
        {if $shipment.user_id}<a href="{"profiles.update?user_id=`$shipment.user_id`"|fn_url}">{/if}{$shipment.s_lastname} {$shipment.s_firstname}{if $shipment.user_id}</a>{/if}
    </td>
    
    <td class="nowrap">

        <div class="hidden-tools">
            {assign var="return_current_url" value=$config.current_url|escape:url}
            {capture name="tools_list"}
                {hook name="shipments:list_extra_links"}
                    <li>{btn type="list" text=__("view") href="shipments.details?shipment_id=`$shipment.shipment_id`"}</li>
                    <li>{btn type="list" text=__("print_slip") class="cm-new-window" href="shipments.packing_slip?shipment_ids[]=`$shipment.shipment_id`"}</li>
                    <li>{btn type="list" text=__("print_pdf_packing_slip") class="cm-new-window" href="shipments.packing_slip?shipment_ids[]=`$shipment.shipment_id`&format=pdf"}</li>
                    <li class="divider"></li>
                    <li>{btn type="list" text=__("delete") class="cm-confirm" href="shipments.delete?shipment_ids[]=`$shipment.shipment_id`&redirect_url=`$return_current_url`"}</li>
                {/hook}
            {/capture}
            {dropdown content=$smarty.capture.tools_list}
        </div>

    </td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl"}
</form>
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {hook name="shipments:list_tools"}
        {if $shipments}
            <li>{btn type="list" text=__("bulk_print_packing_slip") class="cm-new-window" dispatch="dispatch[shipments.packing_slip]" form="manage_shipments_form"}</li>
        {/if}
        {/hook}
        {if $shipments}
            <li class="divider"></li>
            <li>{btn type="delete_selected" dispatch="dispatch[shipments.m_delete]" form="manage_shipments_form"}</li>
        {/if}
    {/capture}
    {if $smarty.capture.tools_list|trim}
        {dropdown content=$smarty.capture.tools_list}
    {/if}
{/capture}

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="shipments.manage" view_type="shipments"}
    {include file="views/shipments/components/shipments_search_form.tpl" dispatch="shipments.manage"}
{/capture}

{capture name="title"}
    {strip}
    {__("shipments")}
    {if $smarty.request.order_id}
        &nbsp;({__("order")}&nbsp;#{$smarty.request.order_id})
    {/if}
    {/strip}
{/capture}
{include file="common/mainbox.tpl" title=$smarty.capture.title content=$smarty.capture.mainbox sidebar=$smarty.capture.sidebar buttons=$smarty.capture.buttons}