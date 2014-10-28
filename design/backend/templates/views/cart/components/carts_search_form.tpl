<div class="sidebar-row">
<h6>{__("search")}</h6>

<form action="{""|fn_url}" name="carts_search_form" method="get">
{capture name="simple_search"}

<div class="sidebar-field">
    <label for="cname">{__("customer")}</label>
    <input type="text" name="cname" id="cname" value="{$search.cname}" size="30" />
</div>

<div class="sidebar-field">
    <label for="email">{__("email")}</label>
    <input type="text" name="email" id="email" value="{$search.email}" size="30" />
</div>

<div class="sidebar-field">
    <label for="total_from">{__("total")}&nbsp;({$currencies.$primary_currency.symbol nofilter})</label>
    <input class="input-small" type="text" name="total_from" id="total_from" value="{$search.total_from}" size="3"/>&nbsp;-&nbsp;<input class="input-small" type="text" name="total_to" value="{$search.total_to}" size="3" />
</div>
{/capture}

{capture name="advanced_search"}
<div class="group">
    <div class="control-group">
        {include file="common/period_selector.tpl" period=$search.period form_name="carts_search_form"}
    </div>
</div>

<div class="row-fluid">
    <div class="group span6 form-horizontal">
        <div class="control-group">
            <label class="control-label">{__("content")}</label>
            <div class="controls checkbox-list">
                {if $search.product_type_c != "Y" && $search.product_type_w != "Y"}
                    {assign var="check_all" value=true}
                {/if}
                {hook name="cart:search_form"}
                <label for="cb_product_type_c">
                    <input type="checkbox" value="Y" {if $search.product_type_c == "Y" || $check_all}checked="checked"{/if} name="product_type_c" id="cb_product_type_c" onclick="if (!this.checked) document.getElementById('cb_product_type_w').checked = true;" disabled="disabled" />
                    {__("cart")}
                </label>
                {/hook}
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="with_info_only">{__("with_contact_information")}</label>
            <div class="controls">
                <input type="checkbox" id="with_info_only" name="with_info_only" value="Y" {if $search.with_info_only}checked="checked"{/if} />
            </div>
        </div>

	{if "ULTIMATE"|fn_allowed_for && !$runtime.company_id}
        <div class="control-group">
            <label class="control-label" for="online_only">{__("online_only")}</label>
            <div class="controls">
                <input type="checkbox" id="online_only" name="online_only" value="Y" class="checkbox" {if $search.online_only}checked="checked"{/if} />
            </div>
        </div>
	{/if}
    </div>

    <div class="group span6 form-horizontal">
        <div class="control-group">
            <label class="control-label" for="users_type">{__("user_type")}</label>
            <div class="controls">
                <select name="users_type" id="users_type">
                    <option value="A" {if $search.users_type == "A"}selected="selected"{/if}>{__("any")}</option>
                    <option value="R" {if $search.users_type == "R"}selected="selected"{/if}>{__("usergroup_registered")}</option>
                    <option value="G" {if $search.users_type == "G"}selected="selected"{/if}>{__("guest")}</option>
                </select>
            </div>
        </div>
        {if "ULTIMATE"|fn_allowed_for && !$runtime.company_id}
            {include file="common/select_vendor.tpl"}
        {/if}
    </div>
</div>

<div class="group">
    <div class="control-group">
        <label class='control-label'>{__("products_in_cart")}</label>
        <div class="controls">
            {include file="common/products_to_search.tpl" placement="right"}
        </div>
    </div>
</div>

{/capture}

{include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search=$smarty.capture.advanced_search dispatch="cart.cart_list" view_type="carts"}

</form>
</div>
<hr>
