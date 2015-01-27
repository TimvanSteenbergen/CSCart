<div class="rma">
    <div class="rma-actions clearfix">
        <span><i class="icon-print"></i>{include file="buttons/button.tpl" but_text=__("print_slip") but_href="rma.print_slip?return_id=`$return_info.return_id`" but_role="text" but_meta="cm-new-window"}</span>
        <span><i class="icon-arrow-left"></i>{include file="buttons/button.tpl" but_text=__("related_order") but_href="orders.details?order_id=`$return_info.order_id`" but_role="text" but_meta="related"}</span>
    </div>
<div class="clear"></div>
{if $return_info}
<form action="{""|fn_url}" method="post" name="return_info_form" />
<input type="hidden" name="return_id" value="{$smarty.request.return_id}" />
<input type="hidden" name="order_id" value="{$return_info.order_id}" />
<input type="hidden" name="total_amount" value="{$return_info.total_amount}" />
<input type="hidden" name="return_status" value="{$return_info.status}" />
{capture name="tabsbox"}
{** RETURN PRODUCTS SECTION **}
    <div id="content_return_products">
        <table class="table rma-return-table table-width">
            <thead>
            <tr>
                <th class="products">{__("product")}</th>
                <th class="price right">{__("price")}</th>
                <th class="qty">{__("quantity")}</th>
                <th class="reason left">{__("reason")}</th>
            </tr>
        </thead>
        {foreach from=$return_info.items[$smarty.const.RETURN_PRODUCT_ACCEPTED] item="ri" key="key"}
        <tr {cycle values=",class=\"table-row\""}>
            <td>{if !$ri.deleted_product}<a href="{"products.view?product_id=`$ri.product_id`"|fn_url}">{/if}{$ri.product nofilter}{if !$ri.deleted_product}</a>{/if}
                {if $ri.product_options}
                    {include file="common/options_info.tpl" product_options=$ri.product_options}
                {/if}</td>
            <td class="right nowrap">
                {if !$ri.price}{__("free")}{else}{include file="common/price.tpl" value=$ri.price}{/if}</td>
            <td class="center">{$ri.amount}</td>
            <td class="nowrap">
                {assign var="reason_id" value=$ri.reason}
                {$reasons.$reason_id.property}</td>
        </tr>
        {foreachelse}
        <tr>
            <td colspan="6"><p class="no-items">{__("text_no_products_found")}</p></td>
        </tr>
        {/foreach}
        </table>
    </div>
{** /RETURN PRODUCTS SECTION **}

{** DECLINED PRODUCTS SECTION **}
    <div id="content_declined_products" class="hidden">
        <table class="table table-width">
        <thead>
        <tr>
                <th style="width: 100%">{__("product")}</th>
                <th>{__("price")}</th>
                <th>{__("quantity")}</th>
                <th>{__("reason")}</th>
            </tr>
        </thead>
        {foreach from=$return_info.items[$smarty.const.RETURN_PRODUCT_DECLINED] item="ri" key="key"}
        <tr {cycle values=",class=\"table-row\""}>
            <td>
                {if !$ri.deleted_product}<a href="{"products.view?product_id=`$ri.product_id`"|fn_url}">{/if}{$ri.product nofilter}{if !$ri.deleted_product}</a>{/if}
                {if $ri.product_options}
                    {include file="common/options_info.tpl" product_options=$ri.product_options}
                {/if}</td>
            <td class="right nowrap">
                {if !$ri.price}{__("free")}{else}{include file="common/price.tpl" value=$ri.price}{/if}</td>
            <td class="center">{$ri.amount}</td>
            <td class="nowrap">
                {assign var="reason_id" value=$ri.reason}
                {$reasons.$reason_id.property}</td>
        </tr>
        {foreachelse}
        <tr>
            <td colspan="6"><p class="no-items">{__("text_no_products_found")}</p></td>
        </tr>
        {/foreach}
        </table>
    </div>
{** /DECLINED PRODUCTS SECTION **}

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section}
{if $return_info.comment}
    <div class="rma-comments">
        {include file="common/subheader.tpl" title=__("comments")}
        <div class="rma-comments-body">
            <span class="caret"> <span class="caret-outer"></span> <span class="caret-inner"></span></span>
            {$return_info.comment|nl2br nofilter}
        </div>
    </div>
{/if}
</form>
{/if}

{capture name="mainbox_title"}
    <div class="rma-status">
        <em>{__("status")}: {include file="common/status.tpl" status=$return_info.status display="view" name="update_return[status]" status_type=$smarty.const.STATUSES_RETURN}</em>
        <em>{__("action")}: {assign var="action_id" value=$return_info.action}{$actions.$action_id.property}</em>
    </div>
        {__("return_info")}&nbsp;#{$return_info.return_id}
    <em class="rma-date">
        ({$return_info.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"})
    </em>
{/capture}
</div>