{if $in_popup}
    <div class="adv-search">
    <div class="group">
{else}
    <div class="sidebar-row">
    <h6>{__("search")}</h6>
{/if}

{if $page_part}
    {assign var="_page_part" value="#`$page_part`"}
{/if}

<form action="{""|fn_url}{$_page_part}" name="{$product_search_form_prefix}search_form" method="get" class="cm-disable-empty {$form_meta}">
<input type="hidden" name="type" value="{$search_type|default:"simple"}" />
{if $smarty.request.redirect_url}
    <input type="hidden" name="redirect_url" value="{$smarty.request.redirect_url}" />
{/if}
{if $selected_section != ""}
    <input type="hidden" id="selected_section" name="selected_section" value="{$selected_section}" />
{/if}

{if $put_request_vars}
{foreach from=$smarty.request key="k" item="v"}
{if $v && $k != "callback"}
<input type="hidden" name="{$k}" value="{$v}" />
{/if}
{/foreach}
{/if}

{$extra nofilter}

{capture name="simple_search"}
    {hook name="products:simple_search"}
    <div class="sidebar-field">
        <label>{__("find_results_with")}</label>
        <input type="text" name="q" size="20" value="{$search.q}" />
    </div>

    <div class="sidebar-field">
        <label>{__("price")}&nbsp;({$currencies.$primary_currency.symbol nofilter})</label>
        <input type="text" name="price_from" size="1" value="{$search.price_from}" onfocus="this.select();" class="input-small" /> - <input type="text" size="1" name="price_to" value="{$search.price_to}" onfocus="this.select();" class="input-small" />
    </div>

    <div class="sidebar-field">
        <label>{__("search_in_category")}</label>
        {if "categories"|fn_show_picker:$smarty.const.CATEGORY_THRESHOLD}
            {if $search.cid}
                {assign var="s_cid" value=$search.cid}
            {else}
                {assign var="s_cid" value="0"}
            {/if}
            {include file="pickers/categories/picker.tpl" company_ids=$picker_selected_companies data_id="location_category" input_name="cid" item_ids=$s_cid hide_link=true hide_delete_button=true default_name=__("all_categories") extra=""}
        {else}
            {if $runtime.mode == "picker"}
                {assign var="trunc" value="38"}
            {else}
                {assign var="trunc" value="25"}
            {/if}
            <select name="cid">
                <option value="0" {if $category_data.parent_id == "0"}selected="selected"{/if}>- {__("all_categories")} -</option>
                {foreach from=0|fn_get_plain_categories_tree:false:$smarty.const.CART_LANGUAGE:$picker_selected_companies item="search_cat" name=search_cat}
                {if $search_cat.store}
                {if !$smarty.foreach.search_cat.first}
                    </optgroup>
                {/if}

                <optgroup label="{$search_cat.category}">
                    {assign var="close_optgroup" value=true}
                    {else}
                    <option value="{$search_cat.category_id}" {if $search_cat.disabled}disabled="disabled"{/if} {if $search.cid == $search_cat.category_id}selected="selected"{/if} title="{$search_cat.category}">{$search_cat.category|escape|truncate:$trunc:"...":true|indent:$search_cat.level:"&#166;&nbsp;&nbsp;&nbsp;&nbsp;":"&#166;--&nbsp;" nofilter}</option>
                    {/if}
                    {/foreach}
                    {if $close_optgroup}
                </optgroup>
                {/if}
            </select>
        {/if}
    </div>
    {/hook}
{/capture}

{capture name="advanced_search"}
{** Products advanced search form hook *}
{hook name="products:advanced_search"}
    <div class="group form-horizontal">
    <div class="control-group">
    <label>{__("search_in")}</label>
    <table width="100%">
        <tr class="nowrap">
            <td><label for="pname" class="checkbox inline"><input type="checkbox" value="Y" {if $search.pname == "Y"}checked="checked"{/if} name="pname" id="pname" />{__("product_name")}</label></td>
            <td><label for="pshort" class="checkbox inline"><input type="checkbox" value="Y" {if $search.pshort == "Y"}checked="checked"{/if} name="pshort" id="pshort"  />{__("short_description")}</label></td>
            <td><label for="pfull" class="checkbox  inline"><input type="checkbox" value="Y" {if $search.pfull == "Y"}checked="checked"{/if} name="pfull" id="pfull" />{__("full_description")}</label></td>
            <td><label for="pkeywords" class="checkbox  inline"><input type="checkbox" value="Y" {if $search.pkeywords == "Y"}checked="checked"{/if} name="pkeywords" id="pkeywords"  />{__("keywords")}</label></td>
        </tr>
    </table>
    </div>
</div>

<div class="group form-horizontal">
{if !"ULTIMATE:FREE"|fn_allowed_for && $filter_items}
<div class="control-group">

    <a href="#" class="search-link cm-combination open cm-save-state" id="sw_filter">
    <span id="on_filter" class="exicon-expand cm-save-state {if $smarty.cookies.filter}hidden{/if}"> </span>
    <span id="off_filter" class="exicon-collapse cm-save-state {if !$smarty.cookies.filter}hidden{/if}"></span>
    {__("search_by_product_filters")}</a>

    <div class="controls">
        <div id="filter"{if !$smarty.cookies.filter} class="hidden"{/if}>
            {include file="views/products/components/advanced_search_form.tpl" filter_features=$filter_items prefix="filter_"}
        </div>
    </div>
</div>
{/if}
</div>

{if $feature_items}
<div class="group form-horizontal">
    <div class="control-group">

        <a class="search-link cm-combination nowrap open cm-save-state" id="sw_feature"><span id="on_feature" class="cm-combination cm-save-state {if $smarty.cookies.feature}hidden{/if}"><span class="exicon-expand"></span></span><span id="off_feature" class="cm-combination cm-save-state {if !$smarty.cookies.feature}hidden{/if}"><span class="exicon-collapse"></span></span>{__("search_by_product_features")}</a>

        <div class="controls">
        <div id="feature"{if !$smarty.cookies.feature} class="hidden"{/if}>
            <input type="hidden" name="advanced_filter" value="Y" />
            {include file="views/products/components/advanced_search_form.tpl" filter_features=$feature_items prefix="feature_"}
        </div>
        </div>
    </div>
</div>
{/if}

<div class="row-fluid">
<div class="group span6">
    <div class="form-horizontal">
        <div class="control-group">
            <label for="pcode" class="control-label">{__("search_by_sku")}</label>
            <div class="controls">
                <input type="text" name="pcode" id="pcode" value="{$search.pcode}" onfocus="this.select();"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="popularity_from">{__("popularity")}</label>
            <div class="controls">
                <input type="text" name="popularity_from" id="popularity_from" value="{$search.popularity_from}" onfocus="this.select();" class="input-mini" /> - <input type="text" name="popularity_to" value="{$search.popularity_to}" onfocus="this.select();" class="input-mini" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="subcats">{__("subcategories")}</label>
            <div class="controls">
                <input type="hidden" name="subcats" value="N" />
                <input type="checkbox" value="Y"{if $search.subcats == "Y" || !$search.subcats} checked="checked"{/if} name="subcats"  id="subcats" />
            </div>
        </div>
    </div>
</div>

<div class="group span6 form-horizontal">
    <div class="control-group">
        <label class="control-label" for="shipping_freight_from">{__("shipping_freight")}&nbsp;({$currencies.$primary_currency.symbol nofilter})</label>
        <div class="controls">
            <input type="text" name="shipping_freight_from" id="shipping_freight_from" value="{$search.shipping_freight_from}" onfocus="this.select();" class="input-mini" /> - <input type="text" name="shipping_freight_to" value="{$search.shipping_freight_to}" onfocus="this.select();" class="input-mini" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="weight_from">{__("weight")}&nbsp;({$settings.General.weight_symbol})</label>
        <div class="controls">
            <input type="text" name="weight_from" id="weight_from" value="{$search.weight_from}" onfocus="this.select();" class="input-mini" /> - <input type="text" name="weight_to" value="{$search.weight_to}" onfocus="this.select();" class="input-mini" />
        </div>
    </div>

    {assign var="have_amount_filter" value=0}
    {if !"ULTIMATE:FREE"|fn_allowed_for}
        {foreach from=$filter_items item="ff"}
            {if $ff.field_type eq "A"}
                {assign var="have_amount_filter" value=1}
            {/if}
        {/foreach}
    {/if}
    {if !$have_amount_filter}
    <div class="control-group">
        <label class="control-label" for="amount_from">{__("quantity")}:</label>
        <div class="controls">
            <input type="text" name="amount_from" id="amount_from" value="{$search.amount_from}" onfocus="this.select();" class="input-mini" /> - <input type="text" name="amount_to" value="{$search.amount_to}" onfocus="this.select();" class="input-mini" />
        </div>
    </div>
    {/if}

    {hook name="companies:products_advanced_search"}
    {if $picker_selected_company|fn_string_not_empty}
        <input type="hidden" name="company_id" value="{$picker_selected_company}" />
    {else}
        {include file="common/select_vendor.tpl"}
    {/if}
    {/hook}

</div>
</div>

<div class="row-fluid">
    <div class="group span6 form-horizontal">
        <div class="control-group">
            <label class="control-label" for="free_shipping">{__("free_shipping")}</label>
            <div class="controls">
            <select name="free_shipping" id="free_shipping">
                <option value="">--</option>
                <option value="Y" {if $search.free_shipping == "Y"}selected="selected"{/if}>{__("yes")}</option>
                <option value="N" {if $search.free_shipping == "N"}selected="selected"{/if}>{__("no")}</option>
            </select>
            </div>
        </div>

        <div class="control-group">
            <label for="status" class="control-label">{__("status")}</label>
            <div class="controls">
            <select name="status" id="status">
                <option value="">--</option>
                <option value="A" {if $search.status == "A"}selected="selected"{/if}>{__("active")}</option>
                <option value="H" {if $search.status == "H"}selected="selected"{/if}>{__("hidden")}</option>
                <option value="D" {if $search.status == "D"}selected="selected"{/if}>{__("disabled")}</option>
            </select>
            </div>
        </div>
                {** Hook for additional fields in the products search form *}
        {hook name="products:search_form"}{/hook}
    </div>

    <div class="group span6 form-horizontal">
        {** The 'Search in orders' field hook *}
        {hook name="products:search_in_orders"}
        <div class="control-group">
            <label class="control-label" for="popularity_from">{__("purchased_in_orders")}</label>
            <div class="controls">
                {include file="pickers/orders/picker.tpl" item_ids=$search.order_ids no_item_text=__("no_items") data_id="order_ids" input_name="order_ids" view_mode="simple"}
            </div>
        </div>
        {/hook}
        <div class="control-group">
            <label class="control-label" for="sort_by">{__("sort_by")}</label>
            <div class="controls">
            <select class="input-mini" name="sort_by" id="sort_by">
                <option {if $search.sort_by == "list_price"}selected="selected"{/if} value="list_price">{__("list_price")}</option>
                <option {if $search.sort_by == "product"}selected="selected"{/if} value="product">{__("name")}</option>
                <option {if $search.sort_by == "price"}selected="selected"{/if} value="price">{__("price")}</option>
                <option {if $search.sort_by == "code"}selected="selected"{/if} value="code">{__("sku")}</option>
                <option {if $search.sort_by == "amount"}selected="selected"{/if} value="amount">{__("quantity")}</option>
                <option {if $search.sort_by == "status"}selected="selected"{/if} value="status">{__("status")}</option>
            </select> -
            <select class="input-mini" name="sort_order" id="sort_order">
                <option {if $search.sort_order_rev == "asc"}selected="selected"{/if} value="desc">{__("desc")}</option>
                <option {if $search.sort_order_rev == "desc"}selected="selected"{/if} value="asc">{__("asc")}</option>
            </select>
            </div>
        </div>
    </div>
</div>
{/hook}
{/capture}

{include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search=$smarty.capture.advanced_search dispatch=$dispatch view_type="products" in_popup=$in_popup}

</form>
{if $in_popup}
    </div></div>
{else}
    </div><hr>
{/if}