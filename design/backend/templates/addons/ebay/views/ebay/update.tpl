{if $template_data.template_id}
    {assign var="id" value=$template_data.template_id}
{else}
    {assign var="id" value=0}
{/if}

{assign var="allow_save" value=$template_data|fn_allow_save_object:"ebay_templates"}
{$show_save_btn = $allow_save scope = root}


{capture name="mainbox"}

    {capture name="tabsbox"}

        <form action="{""|fn_url}" method="post" name="template_update_form" class="form-horizontal form-edit {if !$allow_save} cm-hide-inputs{/if}" enctype="multipart/form-data">
            <input type="hidden" name="fake" value="1" />
            <input type="hidden" name="template_id" value="{$id}" />

            <div class="product-manage" id="content_detailed">
                {if "MULTIVENDOR"|fn_allowed_for && $mode != "add"}
                    {assign var="reload_form" value=true}
                {/if}
                
                {if "ULTIMATE"|fn_allowed_for}
                    {assign var="companies_tooltip" value=__("text_ult_ebay_template_store_field_tooltip")}
                {/if}
                {include file="views/companies/components/company_field.tpl"
                    name="template_data[company_id]"
                    id="elm_template_company_id"
                    selected=$template_data.company_id
                    tooltip=$companies_tooltip
                    reload_form=$reload_form
                }
                <div class="control-group">
                    <label for="elm_template_name" class="control-label cm-required">{__("name")}:</label>
                    <div class="controls">
                        <input type="text" name="template_data[name]" id="elm_template_name" size="55" value="{$template_data.name}" class="input-large" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label cm-required" for="elm_site_id">{__("list_products_on")}{include file="common/tooltip.tpl" tooltip={__("list_product_on_tooltip")}}:</label>
                    <div class="controls">
                    {assign var="c_url" value=$config.current_url|fn_query_remove:'site_id'}
                    <select id="elm_site_id" name="template_data[site_id]" onchange="Tygh.$.redirect('{$c_url}&amp;site_id=' + this.value);">
                        {foreach from=""|fn_get_ebay_sites item="site" key="site_id"}
                            <option {if $template_data.site_id == $site_id}selected="selected"{/if} value="{$site_id}">{$site}</option>
                        {/foreach}
                    </select>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="elm_use_as_default">{__("use_as_default")}{include file="common/tooltip.tpl" tooltip={__("use_as_default_tooltip")}}:</label>
                    <div class="controls">
                    <input type="hidden" value="N" name="template_data[use_as_default]"/>
                    <input type="checkbox" class="cm-toggle-checkbox" value="Y" name="template_data[use_as_default]" id="elm_use_as_default"{if $template_data.use_as_default == 'Y'} checked="checked"{/if} />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label cm-required" for="elm_root_ebay_category">{__("ebay_root_category")}{include file="common/tooltip.tpl" tooltip={__("ebay_root_category_tooltip")}}:</label>
                    <div class="controls">
                    <select id="elm_root_ebay_category" name="template_data[root_category]" onchange="Tygh.$.ceAjax('request', fn_url('ebay.get_subcategories?data_id=category&required_field=1&parent_id=' + this.value), {$ldelim}result_ids: 'box_ebay_category', caching: true{$rdelim});">
                        <option value="">{__("select")}</option>
                        {foreach from=$ebay_root_categories item="item"}
                            <option {if $template_data.root_category == $item.category_id}selected="selected"{/if} value="{$item.category_id}">{$item.name}</option>
                        {/foreach}
                    </select>
                    </div>
                </div>

                {include file="addons/ebay/views/ebay/components/ebay_categories.tpl" data_id="category" required_field=true selected_ebay_category=$template_data.category ebay_categories=$template_data.root_category|fn_get_ebay_categories:true}
                
                <div class="control-group">
                    <label class="control-label" for="elm_root_ebay_sec_category">{__("ebay_root_sec_category")}{include file="common/tooltip.tpl" tooltip={__("ebay_secondary_root_category_tooltip")}}:</label>
                    <div class="controls">
                    <select id="elm_root_ebay_sec_category" name="template_data[root_sec_category]" onchange="Tygh.$.ceAjax('request', fn_url('ebay.get_subcategories?data_id=sec_category&required_field=0&parent_id=' + this.value), {$ldelim}result_ids: 'box_ebay_sec_category', caching: true{$rdelim});">
                        <option value="">{__("select")}</option>
                        {foreach from=$ebay_root_categories item="item"}
                            <option {if $template_data.root_sec_category == $item.category_id}selected="selected"{/if} value="{$item.category_id}">{$item.name}</option>
                        {/foreach}
                    </select>
                    </div>
                </div>

                {include file="addons/ebay/views/ebay/components/ebay_categories.tpl" data_id="sec_category" selected_ebay_category=$template_data.sec_category ebay_categories=$template_data.root_sec_category|fn_get_ebay_categories:true}

            <!--content_detailed--></div>
            
            <div id="content_shippings" class="hidden clearfix">

                {assign var="shipping_type" value=$shipping_type|default:$template_data.shipping_type}
                <div class="control-group">
                    <label class="control-label cm-required" for="elm_shipping_type">{__("shipping_type")}{include file="common/tooltip.tpl" tooltip={__("shipping_type_tooltip")}}:</label>
                    <div class="controls">
                    <select id="elm_shipping_type" name="template_data[shipping_type]" onchange="Tygh.$.ceAjax('request', fn_url('ebay.get_shippings?template_id={$template_data.template_id}&shipping_type=' + this.value), {$ldelim}result_ids: 'box_ebay_shippings', caching: true{$rdelim});">
                            <option {if $shipping_type == 'C' || !$shipping_type}selected="selected"{assign var="service_type" value="Calculated"}{/if} value="C">{__('calculated')}</option>
                            <option {if $shipping_type == 'F'}selected="selected"{assign var="service_type" value="Flat"}{/if} value="F">{__('flat')}</option>
                    </select>
                    </div>
                </div>
                
                <div id="box_ebay_shippings">
                    <div class="control-group">
                        <label class="control-label cm-required" for="elm_shipping_service">{__("domestic_shipping_service")}{include file="common/tooltip.tpl" tooltip={__("domestic_shipping_service_tooltip")}}:</label>
                        <div class="controls">
                        <select id="elm_shipping_service" name="template_data[shippings]">
                            <option value="">{__('select')}</option>
                            {foreach from=$service_type|fn_get_ebay_shippings item="shippings" key="shipping_category"}
                                <optgroup label="{$shipping_category}">
                                    {foreach from=$shippings item="shipping"}
                                    <option {if $template_data.shippings == $shipping.name}selected="selected"{/if} value="{$shipping.name}">{$shipping.description}</option>
                                    {/foreach}
                                </optgroup>
                            {/foreach}
                        </select>
                        </div>
                    </div>
                    {if $shipping_type == 'F'}
                        <div class="control-group" id="free_sipping" >
                            <label for="elm_free_shipping" class="control-label">{__("free_shipping")}{include file="common/tooltip.tpl" tooltip={__("free_shipping_tooltip")}}:</label>
                            <div class="controls">
                                <input type="hidden" value="N" name="template_data[free_shipping]"/>
                                <input type="checkbox" onclick="freeShipping()" id="elm_free_shipping" name="template_data[free_shipping]" class="cm-toggle-checkbox" {if $template_data.free_shipping == 'Y'} checked="checked"{/if} value="Y" />
                            </div>
                        </div>
                    
                            <div class="control-group" id="shipping_cost" {if $template_data.free_shipping == 'Y'} style="display:none" {/if}>
                                <label class="control-label cm-required" id="shipping_cost_req" for="elm_shipping_cost">{__("shipping_cost")}{include file="common/tooltip.tpl" tooltip={__("shipping_cost_tooltip")}}:</label>
                                <div class="controls">
                                    <input type="text" id="elm_shipping_cost" name="template_data[shipping_cost]" class="input" size="5" value="{$template_data.shipping_cost}" />
                                </div>
                            </div>

                            <div class="control-group" id="shipping_cost_additional">
                                <label class="control-label" for="elm_shipping_cost_additional">{__("shipping_cost_additional")}{include file="common/tooltip.tpl" tooltip={__("shipping_cost_additional_tooltip")}}:</label>
                                <div class="controls">
                                    <input type="text" id="elm_shipping_cost_additional" name="template_data[shipping_cost_additional]" size="5" value="{$template_data.shipping_cost_additional}" />
                                </div>
                            </div>
                    {/if}
                    <div class="control-group">
                        <label class="control-label" for="elm_international_shipping_service">{__("international_shipping_service")}{include file="common/tooltip.tpl" tooltip={__("international_shipping_service_tooltip")}}:</label>
                        <div class="controls">
                        <select id="elm_international_shipping_service" name="template_data[international_shippings]">
                            <option value="">{__('select')}</option>
                            {foreach from=$service_type|fn_get_ebay_shippings:true item="shippings" key="shipping_category"}
                                    {foreach from=$shippings item="shipping"}
                                    <option {if $template_data.international_shippings == $shipping.name}selected="selected"{/if} value="{$shipping.name}">{$shipping.description}</option>
                                    {/foreach}
                            {/foreach}
                        </select>
                        </div>
                    </div>
                <!--box_ebay_shippings--></div>
                
                <div class="control-group">
                    <label for="elm_dispatch_days" class="control-label cm-required">{__("dispatch_days")}{include file="common/tooltip.tpl" tooltip={__("dispatch_days_tooltip")}}:</label>
                    <div class="controls">
                        <input type="text" id="elm_dispatch_days" name="template_data[dispatch_days]" class="input" size="5" value="{$template_data.dispatch_days}" />
                    </div>
                </div>

            <!--content_shippings--></div>

            <div id="content_payments" class="hidden clearfix">
                {include file="addons/ebay/views/ebay/components/category_features.tpl" data_id="category"}
            <!--content_payments--></div>
            <div id="content_returnPolicy" class="hidden clearfix">
                <div class="control-group">
                    <label class="control-label" for="elm_return_policy">{__("return_policy")}{include file="common/tooltip.tpl" tooltip={__("return_policy_tooltip")}}:</label>
                    <div class="controls">
                    <select id="elm_return_policy" name="template_data[return_policy]">
                            <option {if $template_data.return_policy == "ReturnsAccepted"} selected="selected" {/if} value="ReturnsAccepted">{__('returns_accepted')}</option>
                            <option {if $template_data.return_policy == "ReturnsNotAccepted"} selected="selected" {/if} value="ReturnsNotAccepted">{__('no_returns_accepted')}</option>
                    </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="elm_contact_time">{__("contact_time")}{include file="common/tooltip.tpl" tooltip={__("contact_time_tooltip")}}:</label>
                    <div class="controls">
                    <select id="elm_contact_time" name="template_data[contact_time]">
                            <option {if $template_data.contact_time == "Days_14"} selected="selected" {/if} value="Days_14">14 {__('days')}</option>
                            <option {if $template_data.contact_time == "Days_30"} selected="selected" {/if} value="Days_30">30 {__('days')}</option>
                            <option {if $template_data.contact_time == "Days_60"} selected="selected" {/if} value="Days_60">60 {__('days')}</option>
                    </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="elm_refund_method">{__("refund_method")}{include file="common/tooltip.tpl" tooltip={__("refund_method_tooltip")}}:</label>
                    <div class="controls">
                    <select id="elm_refund_method" name="template_data[refund_method]">
                            <option {if $template_data.refund_method == "MoneyBack"} selected="selected" {/if} value="MoneyBack">{__('money_back')}</option>
                            <option {if $template_data.refund_method == "MoneyBackOrReplacement"} selected="selected" {/if} value="MoneyBackOrReplacement">{__('money_back_or_replace')}</option>
                            <option {if $template_data.refund_method == "MoneyBackOrExchange"} selected="selected" {/if} value="MoneyBackOrExchange">{__('money_back_or_exchange')}</option>
                    </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="elm_cost_paid_by">{__("cost_paid_by")}{include file="common/tooltip.tpl" tooltip={__("cost_paid_by_tooltip")}}:</label>
                    <div class="controls">
                    <select id="elm_cost_paid_by" name="template_data[cost_paid_by]">
                            <option {if $template_data.cost_paid_by == "Seller"} selected="selected" {/if} value="Seller">{__('seller')}</option>
                            <option {if $template_data.cost_paid_by == "Buyer"} selected="selected" {/if} value="Buyer">{__('buyer')}</option>
                    </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="elm_return_policy_descr">{__("return_policy_descr")}{include file="common/tooltip.tpl" tooltip={__("return_policy_descr_tooltip")}}:</label>
                    <div class="controls">
                        <textarea id="elm_return_policy_descr" name="template_data[return_policy_descr]" cols="50" rows="4" class="input-large">{$template_data.return_policy_descr}</textarea>
                    </div>
                </div>
            <!--returnPolicy--></div>

            {capture name="buttons"}
                {if $id}
                    {include file="common/view_tools.tpl" url="ebay.update?template_id="}

                    {capture name="tools_list"}
                        <li>{btn type="list" class="cm-confirm" text=__("delete_this_template") href="ebay.delete_template?template_id=`$id`"}</li>
                    {/capture}
                    {dropdown content=$smarty.capture.tools_list}
                {/if}
                {include file="buttons/save_cancel.tpl" but_role="submit-link" but_target_form="template_update_form" but_name="dispatch[ebay.update]" save=$id}
            {/capture}

        </form>
    {/capture}
    {include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox group_name=$runtime.controller active_tab=$smarty.request.selected_section track=true}

{/capture}

{if $id}
    {capture name="mainbox_title"}
        {"{__("editing_ebay_template")}: `$template_data.name`"|strip_tags}
    {/capture}
{else}
    {capture name="mainbox_title"}
        {__("new_ebay_template")}
    {/capture}
{/if}

{include file="common/mainbox.tpl" title=$smarty.capture.mainbox_title content=$smarty.capture.mainbox  buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}

<script type="text/javascript">
function freeShipping() {$ldelim}
    var $ = Tygh.$;
    if ($("#elm_free_shipping").is(":checked")) {
        $("#shipping_cost_req").removeClass("cm-required");
        $("#shipping_cost").hide();
    } else { 
        $("#shipping_cost_req").addClass("cm-required");
        $("#shipping_cost").show();
    }
{$rdelim};
</script>
