{capture name="mainbox"}

<form action="{""|fn_url}" method="post" target="" name="carts_list_form">

{include file="common/pagination.tpl" save_current_url=true}

{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}

{if $carts_list}
<table class="table table-sort table-middle">
<thead>
<tr>
    <th width="1%">
        {include file="common/check_items.tpl"}</th>
    <th width="20%">
        <span id="off_carts" alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" class="hidden hand cm-combinations-carts"/><span class="exicon-collapse"></span></span>
        <span id="on_carts" alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" class="cm-combinations-carts"><span class="exicon-expand"></span></span>
        <a class="cm-ajax{if $search.sort_by == "customer"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=customer&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("customer")}</a>
    </th>
    <th width="10%"><a class="cm-ajax{if $search.sort_by == "date"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=date&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("date")}</a></th>
    <th width="10%">{__("cart_content")}</th>
    <th width="10%">{__("ip")}</th>
    {hook name="cart:items_list_header"}
    {/hook}
</tr>
</thead>
{foreach from=$carts_list item="customer"}
<tr>
    <td>
        {if "ULTIMATE"|fn_allowed_for}
            <input type="checkbox" name="user_ids[{$customer.company_id}][]" value="{$customer.user_id}" class="cm-item" /></td>
        {/if}
        {if !"ULTIMATE"|fn_allowed_for}
            <input type="checkbox" name="user_ids[]" value="{$customer.user_id}" class="cm-item" /></td>
        {/if}
    <td>
        {if "ULTIMATE"|fn_allowed_for}
            <span alt="{__("expand_sublist_of_items")}" title="{__("expand_sublist_of_items")}" id="on_user_{$customer.user_id}_{$customer.company_id}" class="cm-combination-carts" onclick="Tygh.$.ceAjax('request', '{"cart.cart_list?user_id=`$customer.user_id`&c_company_id=`$customer.company_id`"|fn_url nofilter}', {$ldelim}result_ids: 'cart_products_{$customer.user_id}_{$customer.company_id},wishlist_products_{$customer.user_id}_{$customer.company_id}', caching: true{$rdelim});"><span class="exicon-expand"></span></span>
            <span alt="{__("collapse_sublist_of_items")}" title="{__("collapse_sublist_of_items")}" id="off_user_{$customer.user_id}_{$customer.company_id}" class="hidden cm-combination-carts"><span class="exicon-collapse"></span></span>
        {/if}

        {if !"ULTIMATE"|fn_allowed_for}
            <span alt="{__("expand_sublist_of_items")}" title="{__("expand_sublist_of_items")}" id="on_user_{$customer.user_id}" class="cm-combination-carts" onclick="Tygh.$.ceAjax('request', '{"cart.cart_list?user_id=`$customer.user_id`"|fn_url nofilter}', {$ldelim}result_ids: 'cart_products_{$customer.user_id},wishlist_products_{$customer.user_id}', caching: true{$rdelim});"><span class="exicon-expand"></span></span>
            <span alt="{__("collapse_sublist_of_items")}" title="{__("collapse_sublist_of_items")}" id="off_user_{$customer.user_id}" class="hidden cm-combination-carts"><span class="exicon-collapse"></span></span>
        {/if}

        {if $customer.user_data.email}<a href="{"profiles.update?user_id=`$customer.user_id`"|fn_url}" class="underlined">{if $customer.firstname || $customer.lastname}{$customer.lastname} {$customer.firstname}{else}{$customer.user_data.email}{/if}</a>{else}{__("unregistered_customer")}{/if}
        {include file="views/companies/components/company_name.tpl" object=$customer}
    </td>
    <td>
        {$customer.date|date_format:$settings.Appearance.date_format}
    </td>
    <td>{$customer.cart_products|default:"0"} {__("product_s")}</td>
    <td>{$customer.ip_address}</td>
    {hook name="cart:items_list"}
    {/hook}
</tr>
{assign var="user_js_id" value="user_`$customer.user_id`"}
{if "ULTIMATE"|fn_allowed_for}
    {assign var="user_js_id" value="`$user_js_id`_`$customer.company_id`"}
{/if}
<tbody id="{$user_js_id}" class="hidden row-more">
<tr class="no-border">
    <td>&nbsp;</td>
    <td colspan="3" class="row-more-body top row-gray">
        {assign var="cart_products_js_id" value="cart_products_`$customer.user_id`"}
        {if "ULTIMATE"|fn_allowed_for}
            {assign var="cart_products_js_id" value="`$cart_products_js_id`_`$customer.company_id`"}
        {/if}
        <span id="{$cart_products_js_id}">
        {if $customer.user_id == $sl_user_id}
            {assign var="products" value=$cart_products}
            {assign var="show_price" value=true}
            {if $cart_products}
            <table class="table table-condensed">
            <thead>
            <tr class="no-hover">
                <th>{__("product")}</th>
                <th class="center">{__("quantity")}</th>
                <th class="right">{__("price")}</th>
            </tr>
            </thead>
            {foreach from=$cart_products item="product" name="products"}
            {hook name="cart:product_row"}
            {if !$product.extra.extra.parent}
            <tr>
                <td>
                {if $product.item_type == "P"}
                    {if $product.product}
                    <a href="{"products.update?product_id=`$product.product_id`"|fn_url}">{$product.product nofilter}</a>
                    {else}
                    {__("deleted_product")}
                    {/if}
                {/if}
                {hook name="cart:products_list"}
                {/hook}
                </td>
                <td class="center">{$product.amount}</td>
                <td class="right">{include file="common/price.tpl" value=$product.price span_id="c_`$customer.user_id`_$product.item_id"}</td>
            </tr>
            {/if}
            {/hook}
            {/foreach}
            <tr>
                <td class="right"><span>{__("total")}:</span></td>
                <td class="center"><span>{$customer.cart_all_products}</span></td>
                <td class="right"><span>{include file="common/price.tpl" value=$customer.total span_id="u_$customer.user_id"}</span></td>
            </tr>
            </table>
            {/if}
        {/if}
        <!--{$cart_products_js_id}--></span>
        {if $customer.user_data}
            {assign var="user_data" value=$customer.user_data}
            {assign var="user_info_js_id" value="user_info_`$customer.user_id`"}
            {if "ULTIMATE"|fn_allowed_for}
                {assign var="user_info_js_id" value="`$user_info_js_id`_`$customer.company_id`"}
            {/if}
            <div id="{$user_info_js_id}">

                <h4>{__("user_info")}</h4>
                <dl>
                    <dt>{__("email")}</dt>
                    <dd>{$user_data.email}</dd>            
                    <dt>{__("first_name")}</dt>
                    <dd>{$user_data.firstname}</dd>
                    <dt>{__("last_name")}</dt>
                    <dd>{$user_data.lastname}</dd>
                </dl>

                <h4>{__("billing_address")}</h4>
                <dl>
                    <dt>{__("first_name")}</dt>
                    <dd>{$user_data.b_firstname}</dd>            
                    <dt>{__("last_name")}</dt>
                    <dd>{$user_data.b_lastname}</dd>
                    <dt>{__("address")}</dt>
                    <dd>{$user_data.b_address}</dd>
                    <dt>{__("address_2")}</dt>
                    <dd>{$user_data.b_address_2}</dd>
                    <dt>{__("city")}</dt>
                    <dd>{$user_data.b_city}</dd>
                    <dt>{__("state")}</dt>
                    <dd>{$user_data.b_state_descr}</dd>
                    <dt>{__("country")}</dt>
                    <dd>{$user_data.b_country_descr}</dd>
                    <dt>{__("zip_postal_code")}</dt>
                    <dd>{$user_data.b_zipcode}</dd>
                </dl>

                <h4>{__("shipping_address")}</h4>
                <dl>
                    <dt>{__("first_name")}</dt>
                    <dd>{$user_data.s_firstname}</dd>            
                    <dt>{__("last_name")}</dt>
                    <dd>{$user_data.s_lastname}</dd>
                    <dt>{__("address")}</dt>
                    <dd>{$user_data.s_address}</dd>
                    <dt>{__("address_2")}</dt>
                    <dd>{$user_data.s_address_2}</dd>
                    <dt>{__("city")}</dt>
                    <dd>{$user_data.s_city}</dd>
                    <dt>{__("state")}</dt>
                    <dd>{$user_data.s_state_descr}</dd>
                    <dt>{__("country")}</dt>
                    <dd>{$user_data.s_country_descr}</dd>
                    <dt>{__("zip_postal_code")}</dt>
                    <dd>{$user_data.s_zipcode}</dd>
                </dl>

            <!--{$user_info_js_id}--></div>
        {/if}
    </td>
    {hook name="cart:items_list_row"}
    {/hook}
</tr>
</tbody>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl"}
</form>
{/capture}

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="cart.cart_list" view_type="carts"}
    {include file="views/cart/components/carts_search_form.tpl" dispatch="cart.cart_list"}
{/capture}

{capture name="buttons"}
    {if $carts_list}
        {capture name="tools_list"}
            <li>{btn type="delete" text=__("delete_all_found") dispatch="dispatch[cart.m_delete_all]" form="carts_list_form" class="cm-confirm cm-submit"}</li>
            <li>{btn type="delete_selected" dispatch="dispatch[cart.m_delete]" form="carts_list_form"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
    {/if}
{/capture}

{include file="common/mainbox.tpl" title=__("users_carts") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar}
