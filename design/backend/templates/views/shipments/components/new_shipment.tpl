<script type="text/javascript">
    var packages = [];
</script>

<form action="{""|fn_url}" method="post" name="shipments_form" class="form-horizontal form-edit">
<input type="hidden" name="shipment_data[order_id]" value="{$order_info.order_id}" />

{foreach from=$order_info.shipping key="shipping_id" item="shipping"}
    {if $shipping.packages_info.packages}
        {assign var="has_packages" value=true}
    {/if}
{/foreach}

{if $has_packages}
    <div class="tabs cm-j-tabs">
        <ul>
            <li id="tab_general" class="cm-js active"><a>{__("general")}</a></li>
            <li id="tab_packages_info" class="cm-js"><a>{__("packages")}</a></li>
        </ul>
    </div>
{/if}

<div class="cm-tabs-content" id="tabs_content">
    <div id="content_tab_general">

        <table class="table table-middle">
        <thead>
            <tr>
                <th>{__("product")}</th>
                <th width="5%">{__("quantity")}</th>
            </tr>
        </thead>

        {assign var="shipment_products" value=false}

        {foreach from=$order_info.products item="product" key="key"}
            {if $product.shipment_amount > 0 && (!isset($product.extra.group_key) || $product.extra.group_key == $group_key)}
            {assign var="shipment_products" value=true}

            <tr>
                <td>
                    {assign var=may_display_product_update_link value="products.update"|fn_check_view_permissions}
                    {if $may_display_product_update_link && !$product.deleted_product}<a href="{"products.update?product_id=`$product.product_id`"|fn_url}">{/if}{$product.product|default:__("deleted_product") nofilter}{if $may_display_product_update_link}</a>{/if}
                    {if $product.product_code}<p>{__("sku")}:&nbsp;{$product.product_code}</p>{/if}
                    {if $product.product_options}<div class="options-info">{include file="common/options_info.tpl" product_options=$product.product_options}</div>{/if}
                </td>
                <td class="center" nowrap="nowrap">
                        {math equation="amount + 1" amount=$product.shipment_amount assign="loop_amount"}
                        {if $loop_amount <= 100}
                            <select id="shipment_data_{$key}" class="input-small cm-shipments-product" name="shipment_data[products][{$key}]">
                                <option value="0">0</option>
                            {section name=amount start=1 loop=$loop_amount}
                                <option value="{$smarty.section.amount.index}" {if $smarty.section.amount.last}selected="selected"{/if}>{$smarty.section.amount.index}</option>
                            {/section}
                            </select>
                        {else}
                            <input id="shipment_data_{$key}" type="text" class="input-text" size="3" name="shipment_data[products][{$key}]" value="{$product.shipment_amount}" />&nbsp;of&nbsp;{$product.shipment_amount}
                        {/if}
                </td>
            </tr>
            {/if}
        {/foreach}

        {if !$shipment_products}
            <tr>
                <td colspan="2">{__("no_products_for_shipment")}</td>
            </tr>
        {/if}

        </table>

        {include file="common/subheader.tpl" title=__("options")}

        <fieldset>
            <div class="control-group">
                <label class="control-label" for="shipping_name">{__("shipping_method")}</label>
                <div class="controls">
                    <select name="shipment_data[shipping_id]" id="shipping_name">
                        {foreach from=$shippings item="shipping"}
                            <option    value="{$shipping.shipping_id}">{$shipping.shipping}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="tracking_number">{__("tracking_number")}</label>
                <div class="controls">
                    <input type="text" name="shipment_data[tracking_number]" id="tracking_number" size="10" value="" />
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="carrier_key">{__("carrier")}</label>
                <div class="controls">
                    {include file="common/carriers.tpl" id="carrier_key" name="shipment_data[carrier]"}
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="shipment_comments">{__("comments")}</label>
                <div class="controls">
                    <textarea id="shipmentcomments" name="shipment_data[comments]" cols="55" rows="8" class="span9"></textarea>
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="order_status">{__("order_status")}</label>
                <div class="controls">
                    <select id="order_status" name="shipment_data[order_status]">
                        <option value="">{__("do_not_change")}</option>
                        {foreach from=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses key="key" item="status"}
                            <option value="{$key}">{$status}</option>
                        {/foreach}
                    </select>
                    <p class="description">
                        {__("text_order_status_notification")}
                    </p>
                </div>
            </div>
        </fieldset>

        <div class="cm-toggle-button">
            <div class="control-group select-field notify-customer">
                <div class="controls">
                    <label for="shipment_notify_user" class="checkbox">
                    <input type="checkbox" name="notify_user" id="shipment_notify_user" value="Y" />
                    {__("send_shipment_notification_to_customer")}</label>
                </div>
            </div>
        </div>
    </div>
    
    {if $has_packages}
        <div id="content_tab_packages_info">
            <span class="packages-info">{__("text_shipping_packages_info")}</span>
            {assign var="package_num" value="1"}

            {foreach from=$order_info.shipping key="shipping_id" item="shipping"}
                {foreach from=$shipping.packages_info.packages key="package_id" item="package"}
                    {assign var="allowed" value=true}
                    
                    {capture name="package_container"}
                    <div class="package-container">
                        {* Uncomment the line below and the label tag to activate the distribution of packages functionality *}
                        {*<input type="checkbox" class="cm-shipments-package" id="package_{$shipping_id}{$package_id}" value="Y" />*}
                        
                        <script type="text/javascript">
                            packages['package_{$shipping_id}{$package_id}'] = [];
                        </script>
                        <h3>
                        {*<label for="package_{$shipping_id}{$package_id}">*}{__("package")} {$package_num} {if $package.shipping_params}({$package.shipping_params.box_length} x {$package.shipping_params.box_width} x {$package.shipping_params.box_height}){/if}{*</label>*}
                        </h3>
                        <ul>
                        {foreach from=$package.products key="cart_id" item="amount"}
                            <script type="text/javascript">
                                packages['package_{$shipping_id}{$package_id}']['{$cart_id}'] = '{$amount}';
                            </script>
                            {if $order_info.products.$cart_id}
                                <li><span>{$amount}</span> x {$order_info.products.$cart_id.product} {if $order_info.products.$cart_id.product_options}({include file="common/options_info.tpl" product_options=$order_info.products.$cart_id.product_options}){/if}</li>
                            {else}
                                {assign var="allowed" value=false}
                            {/if}
                        {/foreach}
                        </ul>
                        <span class="strong">{__("weight")}:</span> {$package.weight}<br />
                        <span class="strong">{__("shipping_method")}:</span> {$shipping.shipping}
                    </div>
                                        {/capture}
                    
                    {if $allowed}
                        {$smarty.capture.package_container nofilter}
                    {/if}
                    
                    {math equation="num + 1" num=$package_num assign="package_num"}
                {/foreach}
            {/foreach}
        </div>
    {/if}
</div>

<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_name="dispatch[shipments.add]" cancel_action="close"}
</div>


</form>

{literal}
<script type="text/javascript">
    function fn_calculate_packages()
    {
        var products = [];
        
        Tygh.$('.cm-shipments-package:checked').each(function(id, elm) {
            jelm = Tygh.$(elm);
            id = jelm.prop('id');
            
            for (var i in packages[id]) {
                if (typeof(products[i]) == 'undefined') {
                    products[i] = parseInt(packages[id][i]);
                } else {
                    products[i] += parseInt(packages[id][i]);
                }
            }
        });
        
        // Set the values of the ship products to 0. We will change the values to the correct variants after
        Tygh.$('.cm-shipments-product').each(function() {
            Tygh.$(this).val(0);
        });
        
        if (products.length > 0) {
            for (var i in products) {
                Tygh.$('#shipment_data_' + i).val(products[i]);
            }
        }
    }
    Tygh.$(document).ready(function() {
        Tygh.$('.cm-shipments-package').on('change', fn_calculate_packages);
    });
</script>
{/literal}
