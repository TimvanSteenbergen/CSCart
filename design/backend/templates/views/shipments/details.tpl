{capture name="mainbox"}

{capture name="tabsbox"}
<div id="content_general">
    {* Customer info *}
    {include file="views/profiles/components/profiles_info.tpl" user_data=$order_info location="I"}
    {* /Customer info *}

    <table width="100%" class="table table-middle">
    <thead>
        <tr>
            <th>{__("product")}</th>
            <th width="5%">{__("quantity")}</th>
        </tr>
    </thead>
    {foreach from=$order_info.products item="oi" key="key"}
    {if $oi.amount > 0}
    <tr>
        <td>
            {if !$oi.deleted_product}<a href="{"products.update?product_id=`$oi.product_id`"|fn_url}">{/if}{$oi.product nofilter}{if !$oi.deleted_product}</a>{/if}
            {hook name="shipments:product_info"}
            {if $oi.product_code}</p>{__("sku")}:&nbsp;{$oi.product_code}</p>{/if}
            {/hook}
            {if $oi.product_options}<div class="options-info">{include file="common/options_info.tpl" product_options=$oi.product_options}</div>{/if}
        </td>
        <td class="center">
            &nbsp;{$oi.amount}<br />
        </td>
    </tr>
    {/if}
    {/foreach}
    </table>
    <div class="row-fluid">
        <h3><label for="notes">{__("comments")}:</label></h3>
        <textarea class="input-xxlarge" cols="40" rows="5" readonly="readonly">{$shipment.comments}</textarea>
    </div>
    
<!--content_general--></div>
{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section track=true}

{/capture}

{capture name="sidebar"}
    <div class="sidebar-row">
        <h6>{__("shipment_info")}</h6>
        {include file="common/carriers.tpl" capture=true carrier=$shipment.carrier}

        <p>{__("shipment")} #{$shipment.shipment_id}
        {__("on")} {$shipment.shipment_timestamp|date_format:"`$settings.Appearance.date_format`"} <br />
        {__("by")} {$shipment.shipping} <br />{if $shipment.tracking_number} ({$shipment.tracking_number}){/if}{if $shipment.carrier} ({$smarty.capture.carrier_name|trim nofilter}){/if}</p>
        {hook name="shipments:customer_shot_info"}
        {/hook}
    </div>
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {hook name="shipments:details_tools"}
            <li>{btn type="list" text="{__("order")} #`$order_info.order_id`" href="orders.details?order_id=`$order_info.order_id`"}</li>
            <li>{btn type="list" text=__("print_packing_slip") href="shipments.packing_slip?shipment_ids[]=`$shipment.shipment_id`" class="cm-new-window"}</li>
            <li>{btn type="list" text=__("print_pdf_packing_slip") href="shipments.packing_slip?shipment_ids[]=`$shipment.shipment_id`&format=pdf" class="cm-new-window"}</li>
            <li class="divider"></li>
            <li>{btn type="list" text=__("delete") class="cm-confirm" href="shipments.delete?shipment_ids[]=`$shipment.shipment_id`"}</li>
        {/hook}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}

{include file="common/mainbox.tpl" title=__("shipment_details") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar}