{capture name="mainbox"}

{if $return_info}
<form action="{""|fn_url}" method="post" name="return_info_form" class="form-horizontal form-edit" />
<input type="hidden" name="change_return_status[order_id]" value="{$return_info.order_id}" />
<input type="hidden" name="change_return_status[action]" value="{$return_info.action}" />
<input type="hidden" name="change_return_status[status_from]" value="{$return_info.status}" />
<input type="hidden" name="change_return_status[return_id]" value="{$smarty.request.return_id}" />
<input type="hidden" name="total_amount" value="{$return_info.total_amount}" />

{capture name="sidebar"}
<div class="sidebar-row">
    {__("rma_return")}&nbsp;&nbsp;<span>#{$return_info.return_id}</span>&nbsp;
    {__("by")}&nbsp;&nbsp;<span>{if $return_info.user_id}<a href="{"profiles.update?user_id=`$return_info.user_id`"|fn_url}">{/if}{$return_info.user_id|fn_get_user_name}{if $return_info.user_id}</a>{/if}</span>&nbsp;
        {assign var="time_from" value=$return_info.timestamp|date_format:$settings.Appearance.date_format|escape:url}
        {assign var="time_to" value=$return_info.timestamp|date_format:$settings.Appearance.date_format|escape:url}
        {__("on")}&nbsp;<a href="{"rma.returns?period=C&time_from=`$time_from`&time_to=`$time_to`"|fn_url}">{$return_info.timestamp|date_format:"`$settings.Appearance.date_format`"}</a>,&nbsp;&nbsp;{$return_info.timestamp|date_format:"`$settings.Appearance.time_format`"}
</div>
{/capture}

{hook name="rma:items_content"}
{/hook}

{assign var="check_disabled" value=false}
{if $return_info.status != $smarty.const.RMA_DEFAULT_STATUS}
    {assign var="check_disabled" value=true}
{/if}

{capture name="tabsbox"}
{** RETURN PRODUCTS SECTION **}
    <div id="content_return_products">
        {if $return_info.items[$smarty.const.RETURN_PRODUCT_ACCEPTED]}
        <div class="clearfix">
            <div class="buttons-container pull-right">
            {hook name="rma:return_tools"}
            {if $return_info.status == $smarty.const.RMA_DEFAULT_STATUS}
                {include file="buttons/button.tpl" but_text=__("decline_products") but_name="dispatch[rma.decline_products]" but_role="button_main" but_meta="cm-process-items"}
            {/if}
            {/hook}
            </div>
        </div>

        <table width="100%" class="table table-middle">
        <thead>
        <tr>
            <th width="1%" class="center">
                {hook name="rma:returned_items_header"}
                {include file="common/check_items.tpl" check_disabled=$check_disabled}
                {/hook}
            </th>
            <th width="100%">{__("product")}</th>
            <th>{__("price")}</th>
            <th class="nowrap">{__("quantity")}</th>
            <th>{__("reason")}</th>
        </tr>
        </thead>
        {foreach from=$return_info.items[$smarty.const.RETURN_PRODUCT_ACCEPTED] item="ri" key="key"}
        <tr>
            <td width="1%" class="center">
                {hook name="rma:returned_items_content"}
                <input type="checkbox" name="accepted[{$ri.item_id}][chosen]" value="Y"{if $return_info.status != $smarty.const.RMA_DEFAULT_STATUS} disabled="disabled"{/if} class="cm-item" />
                {/hook}
            </td>
            <td>{if !$ri.deleted_product}<a href="{"products.update?product_id=`$ri.product_id`"|fn_url}">{/if}{$ri.product|default:$ri.product nofilter}{if !$ri.deleted_product}</a>{/if}
            {if $ri.product_options}<div class="options-info">&nbsp;{include file="common/options_info.tpl" product_options=$ri.product_options}</div>{/if}
            </td>
            <td width="10%" class="nowrap">
                {if !$ri.price}{__("free")}{else}{include file="common/price.tpl" value=$ri.price}{/if}</td>
            <td>
                <input type="hidden" name="accepted[{$ri.item_id}][previous_amount]" value="{$ri.amount}" />
                {hook name="rma:returned_items_amount"}
                <select name="accepted[{$ri.item_id}][amount]"{if $return_info.status != $smarty.const.RMA_DEFAULT_STATUS} disabled="disabled"{/if} class="input-mini">
                {/hook}
                {section name=$key loop=$ri.amount+1 start="1" step="1"}
                        <option value="{$smarty.section.$key.index}" {if $smarty.section.$key.index == $ri.amount}selected="selected"{/if}>{$smarty.section.$key.index}</option>
                {/section}
                </select></td>
            <td class="nowrap">
                {assign var="reason_id" value=$ri.reason}
                &nbsp;{$reasons.$reason_id.property}&nbsp;</td>
        </tr>
        {/foreach}
        </table>
        {else}
            <p class="no-items">{__("no_data")}</p>
        {/if}
    </div>
{** /RETURN PRODUCTS SECTION **}

{** DECLINED PRODUCTS SECTION **}
    <div id="content_declined_products" class="hidden">
       {if $return_info.items[$smarty.const.RETURN_PRODUCT_DECLINED] && $return_info.status == $smarty.const.RMA_DEFAULT_STATUS}
        <div class="clearfix">
            <div class="pull-right">
                {include file="buttons/button.tpl" but_text=__("accept_products") but_name="dispatch[rma.accept_products]" but_role="button_main" but_meta="cm-process-items"}
            </div>
        </div>
        {/if}

        {if $return_info.items[$smarty.const.RETURN_PRODUCT_DECLINED]}
        <table width="100%" class="table table-middle">
        <thead>
        <tr>
            <th>
                {include file="common/check_items.tpl" check_disabled=$check_disabled}
            <th width="100%">{__("product")}</th>
            <th>{__("price")}</th>
            <th class="nowrap">{__("quantity")}</th>
            <th>{__("reason")}</th>
        </tr>
        </thead>
        {foreach from=$return_info.items[$smarty.const.RETURN_PRODUCT_DECLINED] item="ri" key="key"}
        <tr>
            <td width="1%" class="center">
                <input type="checkbox" name="declined[{$ri.item_id}][chosen]" value="Y" {if $return_info.status != $smarty.const.RMA_DEFAULT_STATUS}disabled="disabled"{/if} class="checkbox cm-item" /></td>
            <td>{if !$ri.deleted_product}<a href="{"products.update?product_id=`$ri.product_id`"|fn_url}">{/if}{$ri.product nofilter}{if !$ri.deleted_product}</a>{/if}
            {if $ri.product_options}<div class="options-info">&nbsp;{include file="common/options_info.tpl" product_options=$ri.product_options}</div>{/if}
            </td>
            <td class="nowrap">
                {if !$ri.price}{__("free")}{else}{include file="common/price.tpl" value=$ri.price}{/if}</td>
            <td>
                <input type="hidden" name="declined[{$ri.item_id}][previous_amount]" value="{$ri.amount}" />
                <select name="declined[{$ri.item_id}][amount]" {if $return_info.status != $smarty.const.RMA_DEFAULT_STATUS}disabled="disabled"{/if} class="input-mini">
                {section name=$key loop=$ri.amount+1 start="1" step="1"}
                        <option value="{$smarty.section.$key.index}" {if $smarty.section.$key.index == $ri.amount}selected="selected"{/if}>{$smarty.section.$key.index}</option>
                {/section}
                </select></td>
            <td class="nowrap">
                {assign var="reason_id" value=$ri.reason}
                &nbsp;{$reasons.$reason_id.property}&nbsp;</td>
        </tr>
        {/foreach}
        </table>
        {else}
            <p class="no-items">{__("no_data")}</p>
        {/if}
    </div>
{** /DECLINED PRODUCTS SECTION **}


<div id="content_comments" class="cm-hide-save-button hidden">
<fieldset>
    <div class="control-group">
    <label class="control-label">{__("comments")}</label>
        <div class="controls">
            <textarea name="comment" cols="55" rows="8" class="input-large">{$return_info.comment}</textarea>
        </div>
    </div>
</fieldset>
{* Customer info *}
<div class="buttons-container">
    <div class="controls">
        {include file="buttons/button.tpl" but_text=__("customer_information") but_onclick="Tygh.$('#customer_info').toggle();" but_role="text" but_meta="text-button"}
    </div>
</div>
<div id="customer_info" class="hidden">
    <div class="controls">
        {include file="views/profiles/components/profiles_info.tpl" user_data=$order_info location="I"}
    </div>
</div>
{* /Customer info *}

<div class="control-group">
    <div class="controls">
        {include file="buttons/save.tpl" but_name="dispatch[rma.update_details]" but_role="button_main"}
    </div>
</div>
</div>

<div id="content_actions" class="cm-hide-save-button hidden">

<fieldset>
<div class="control-group">
    <label class="control-label" for="elm_status">{__("status")}:</label>
    <div class="controls">
        {include file="common/status.tpl" select_id="elm_status" status=$return_info.status display="select" name="change_return_status[status_to]" status_type=$smarty.const.STATUSES_RETURN}
    </div>
</div>

<div class="control-group">
    <label for="elm_recalc_order" class="control-label">{__("recalculate_order")}</label>
    <div class="controls">
        <label class="radio">
            <input id="elm_recalc_order" type="radio" name="change_return_status[recalculate_order]" value="R" />
        </label>
    </div>
</div>

{if $is_refund == "Y"}
<div class="control-group">
    <label for="elm_recalc_manually" class="control-label">{__("manually_recalculate_order")}</label>
    <div class="controls">
        <label class="radio">
            <input id="elm_recalc_manually" type="radio" name="change_return_status[recalculate_order]" value="M" />
        </label>
    </div>
</div>
{/if}

<div class="control-group">
    <label class="control-label" for="elm_recalc_skip">{__("dont_recalculate_order")}</label>
    <div class="controls">
        <label class="radio">
            <input id="elm_recalc_skip" type="radio" name="change_return_status[recalculate_order]" value="D" checked="checked" />
        </label>
    </div>
</div>

<div class="control-group notify-customer">
    <label class="control-label" for="notify_user">{__("notify_customer")}</label>
    <div class="controls">
        <label class="checkbox">
            <input type="checkbox" name="change_return_status[notify_user]" id="notify_user" value="Y"/>
        </label>
    </div>
</div>

<div class="control-group notify-department">
    <label class="control-label" for="notify_department">{__("notify_orders_department")}</label>
    <div class="controls">
        <label class="checkbox">
            <input type="checkbox" name="change_return_status[notify_department]" id="notify_department" value="Y"/>
        </label>
    </div>
</div>

{if "MULTIVENDOR"|fn_allowed_for}
<div class="control-group notify-department">
    <label class="control-label" for="notify_vendor">{__("notify_vendor")}</label>
    <div class="controls">
        <label class="checkbox">
            <input type="checkbox" name="change_return_status[notify_vendor]" id="notify_vendor" value="Y" />
        </label>
    </div>
</div>
{/if}
</fieldset>

<div class="control-group">
    <div class="controls">
        {include file="buttons/save.tpl" but_name="dispatch[rma.update_details]" but_role="button_main"}
    </div>
</div>
</div>

{hook name="rma:tabs_content"}{/hook}

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox}


</form>
{/if}

{/capture}

{capture name="buttons"}
    {include file="common/view_tools.tpl" url="rma.details?return_id="}
    {capture name="tools_list"}
        <li>{btn type="list" text=__("print_slip") href="rma.print_slip?return_id=`$return_info.return_id`" class="cm-new-window"}</li>
        <li>{btn type="list" text=__("delete_this_return") class="cm-confirm" href="rma.delete?return_id=`$return_info.return_id`"}</li>
    {/capture}
    {dropdown content=$smarty.capture.tools_list}

    {hook name="rma:details_tools"}
    {include file="buttons/button.tpl" but_text=__("related_order") but_href="orders.details?order_id=`$return_info.order_id`" but_role="tool"}
    {/hook}
{/capture}

{include file="common/mainbox.tpl" title=__("return_info") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar}
