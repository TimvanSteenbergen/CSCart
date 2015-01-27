{if $gift_cert_data.gift_cert_id}
    {assign var="id" value=$gift_cert_data.gift_cert_id}
{else}
    {assign var="id" value=0}
{/if}

<script type="text/javascript">
(function(_, $) {
    $(document).ready(function() {
        $.ceFormValidator('registerValidator', {
            class_name: 'cm-gc-validate-amount',
            message: _.tr('text_gift_cert_amount_alert'),
            func: function(id) {
                var max = parseInt((parseFloat(max_amount) / parseFloat(_.currencies.secondary.coefficient))*100)/100;
                var min = parseInt((parseFloat(min_amount) / parseFloat(_.currencies.secondary.coefficient))*100)/100;

                var amount = parseFloat($('#' + id).val());
                if ((amount <= max) && (amount >= min)) {
                    return true;
                }

                return false;
            }
        });
        
        $('#' + (send_via == 'E' ? 'post' : 'email') + '_block').switchAvailability(true, true);

        $(_.doc).on('click', 'input[name="gift_cert_data[send_via]"]', function() {
            $('#email_block').switchAvailability($(this).val() == 'P', true);
            $('#post_block').switchAvailability($(this).val() == 'E', true);
        });
    });
}(Tygh, Tygh.$));
</script>

{include file="views/profiles/components/profiles_scripts.tpl"}

{include file="common/price.tpl" value=$addons.gift_certificates.max_amount assign="max_amount"}
{include file="common/price.tpl" value=$addons.gift_certificates.min_amount assign="min_amount"}
{assign var="text_gift_cert_amount_alert" value=__("text_gift_cert_amount_alert", ["[min]" => $min_amount, "[max]" => $max_amount])}

<script type="text/javascript">
var max_amount = '{$addons.gift_certificates.max_amount|escape:javascript nofilter}';
var min_amount = '{$addons.gift_certificates.min_amount|escape:javascript nofilter}';
var send_via = '{$gift_cert_data.send_via|default:"E"}';
Tygh.tr('text_gift_cert_amount_alert',  '{$text_gift_cert_amount_alert|escape:javascript nofilter}');
</script>

    {capture name="mainbox"}

    <form action="{""|fn_url}" method="post" target="_self" class=" form-horizontal form-edit" name="gift_certificates_form" enctype="multipart/form-data">
    <input type="hidden" name="gift_cert_id" value="{$id}">

    {** Page Section **}

    {if $id}
    {capture name="tabsbox"}
    <div id="content_detailed" class="hidden">
    {/if}

    {** /Page Section **}

        {if $id}
        <fieldset>
        <div class="control-group">
            <label class="control-label" for="elm_gift_cert_code">{__("gift_cert_code")}:</label>
            <div class="controls">
                <input type="hidden" name="gift_cert_data[gift_cert_code]" id="elm_gift_cert_code" value="{$gift_cert_data.gift_cert_code}">
                <div class="text-type-value select-value-wrap"><span class="select-value">{$gift_cert_data.gift_cert_code}</span></div>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_gift_cert_status">{__("status")}:</label>
            <div class="controls">
                <input type="hidden" name="certificate_status" value="{$gift_cert_data.status}">
                {include file="common/status.tpl" status=$gift_cert_data.status display="select" name="gift_cert_data[status]" status_type=$smarty.const.STATUSES_GIFT_CERTIFICATE select_id="elm_gift_cert_status"}
            </div>
        </div>
        {/if}

        {if "ULTIMATE"|fn_allowed_for}
            {include file="views/companies/components/company_field.tpl"
                name="gift_cert_data[company_id]"
                selected=$gift_cert_data.company_id
                id="elm_gift_cert_data_company_id"
            }
        {else}
            <input type="hidden" name="gift_cert_data[company_id]" value="0">
        {/if}

        <div class="control-group">
            <label for="elm_gift_cert_recipient" class="control-label cm-required">{__("gift_cert_to")}:</label>
            <div class="controls">
                <input type="text" id="elm_gift_cert_recipient" class="input-large" name="gift_cert_data[recipient]"  maxlength="255" value="{$gift_cert_data.recipient}">
            </div>
        </div>

        <div class="control-group">
            <label for="elm_gift_cert_sender" class="control-label cm-required">{__("gift_cert_from")}:</label>
            <div class="controls">
                <input type="text" id="elm_gift_cert_sender" class="input-large" name="gift_cert_data[sender]" maxlength="255" value="{$gift_cert_data.sender}">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_gift_cert_message">{__("message")}:</label>
            <div class="controls">
                <textarea id="elm_gift_cert_message" name="gift_cert_data[message]" cols="55" rows="6" class="cm-wysiwyg input-large">{$gift_cert_data.message}</textarea>
            </div>
        </div>

        <div class="control-group">
            <label class="cm-required control-label cm-gc-validate-amount" for="gift_cert_amount">{__("amount")}:</label>
            <div class="controls">
                <div class="text-type-value pull-left">{if $currencies.$secondary_currency.after != "Y"}{$currencies.$secondary_currency.symbol nofilter}{/if}&nbsp;</div>
                <input type="text" id="gift_cert_amount" name="gift_cert_data[amount]" class="valign input-text-short inp-el cm-numeric" data-p-sign="s" data-a-sep="" data-a-dec="{$currencies.$secondary_currency.decimals_separator}" size="5" value="{if $gift_cert_data}{$gift_cert_data.amount|fn_format_rate_value:"":$currencies.$secondary_currency.decimals:".":"":$currencies.$secondary_currency.coefficient}{else}{$addons.gift_certificates.min_amount|fn_format_rate_value:"":$currencies.$secondary_currency.decimals:".":"":$currencies.$secondary_currency.coefficient}{/if}" />


                {if $currencies.$secondary_currency.after == "Y"}{$currencies.$secondary_currency.symbol nofilter}{/if}
                <p><small>{$text_gift_cert_amount_alert nofilter}</small></p>
            </div>
        </div>

        <div class="control-group">
            <div class="controls">
                <label for="elm_gift_cert_send_via_email" class="radio inline">
                    <input id="elm_gift_cert_send_via_email" type="radio" name="gift_cert_data[send_via]" value="E" {if !$id || $gift_cert_data.send_via == "E"}checked="checked"{/if}>
                    {__("send_via_email")}
                </label>
                <label class="radio inline" for="elm_gift_cert_send_via_post">
                    <input id="elm_gift_cert_send_via_post" type="radio" name="gift_cert_data[send_via]" value="P" {if $gift_cert_data.send_via == "P"}checked="checked"{/if}>
                    {__("send_via_postal_mail")}
                </label>
            </div>
        </div>

        <div id="email_block" {if $gift_cert_data.send_via == "P"}class="hidden"{/if}>
            <div class="control-group">
                <label for="elm_gift_cert_email" class="cm-required control-label cm-email">{__("email")}:</label>
                <div class="controls">
                    <input type="text" id="elm_gift_cert_email" name="gift_cert_data[email]" class="input-large" maxlength="128" value="{$gift_cert_data.email}">
                </div>
            </div>
            {if $templates|sizeof > 1}
                <div class="control-group">
                    <label class="control-label" for="elm_gift_cert_template">{__("template")}:</label>
                    <div class="controls">
                        <select id="elm_gift_cert_template" name="gift_cert_data[template]">
                        {foreach from=$templates item="name" key="file"}
                            <option value="{$file}" {if $file == $gift_cert_data.template}selected{/if}>{$name}</option>
                        {/foreach}
                        </select>
                    </div>
                </div>
            {else}
                {foreach from=$templates item="name" key="file"}
                    <input type="hidden" name="gift_cert_data[template]" value="{$file}">
                {/foreach}
            {/if}
        </div>

        <div id="post_block" {if !$id || $gift_cert_data.send_via == "E"}class="hidden"{/if}>
            <div class="control-group">
                <label for="elm_gift_cert_address" class="control-label cm-required">{__("address")}:</label>
                <div class="controls">
                    <input type="text" id="elm_gift_cert_address" name="gift_cert_data[address]" value="{$gift_cert_data.address}">
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="elm_gift_cert_address_2">{__("address_2")}:</label>
                <div class="controls">
                    <input type="text" id="elm_gift_cert_address_2" name="gift_cert_data[address_2]" value="{$gift_cert_data.address_2}">
                </div>
            </div>

            <div class="control-group">
                <label for="elm_gift_cert_city" class="control-label cm-required">{__("city")}:</label>
                <div class="controls">
                    <input type="text" id="elm_gift_cert_city" name="gift_cert_data[city]" value="{$gift_cert_data.city}">
                </div>
            </div>

            {$_country = $gift_cert_data.country|default:$settings.General.default_country}
            <div class="control-group">
                <label for="elm_gift_cert_country" class="control-label cm-required">{__("country")}:</label>
                <div class="controls">
                    <select id="elm_gift_cert_country" name="gift_cert_data[country]" class="cm-country cm-location-billing">
                        <option value="">- {__("select_country")} -</option>
                        {foreach from=$countries item="country" key="code"}
                            <option {if $_country == $code}selected="selected"{/if} value="{$code}">{$country}</option>
                        {/foreach}
                    </select>
                </div>
            </div>

            {$_state = $gift_cert_data.state|default:$settings.General.default_state}
            <div class="control-group">
                <label for="elm_gift_cert_state" class="control-label cm-required">{__("state")}:</label>
                <div class="controls">
                    <select class="cm-state cm-location-billing" id="elm_gift_cert_state" name="gift_cert_data[state]">
                        <option value="">- {__("select_state")} -</option>
                        {if $states && $states.$_country}
                            {foreach from=$states.$_country item=state}
                                <option {if $_state == $state.code}selected="selected"{/if} value="{$state.code}">{$state.state}</option>
                            {/foreach}
                        {/if}
                    </select>
                    <input type="text" id="elm_gift_cert_state_d" name="gift_cert_data[state]" class="cm-state cm-location-billing hidden" maxlength="64" value="{$_state}" disabled="disabled">
                </div>
            </div>

            <div class="control-group">
                <label for="elm_gift_cert_zipcode" class="control-label cm-required cm-zipcode cm-location-billing">{__("zip_postal_code")}:</label>
                <div class="controls">
                    <input type="text" id="elm_gift_cert_zipcode" name="gift_cert_data[zipcode]" value="{$gift_cert_data.zipcode}">
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="elm_gift_cert_phone">{__("phone")}:</label>
               <div class="controls">
                    <input type="text" id="elm_gift_cert_phone" name="gift_cert_data[phone]" value="{$gift_cert_data.phone}">
               </div>
            </div>
        </div>

        {if $id}</fieldset>{/if}

        {if $addons.gift_certificates.free_products_allow == "Y"}
            {include file="common/subheader.tpl" title=__("free_products")}
            {include file="pickers/products/picker.tpl" data_id="free_products" item_ids=$gift_cert_data.products input_name="gift_cert_data[products]" type="table" picker_for="gift_certificates" placement="right"}
        {/if}
        
        <div class="control-group">
            <label for="notify_user" class="control-label">
                {__("notify_customer")}
            </label>
            <div class="controls">
                <input type="checkbox" name="notify_user" id="notify_user" value="Y">
            </div>
        </div>
        </form>

    {** Page Section **}
    {if $id}
        </div>
        <div id="content_log" class="hidden">
            {include file="common/pagination.tpl"}

            {assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
            {assign var="c_icon" value="<i class=\"exicon-`$search.sort_order_rev`\"></i>"}
            {assign var="c_dummy" value="<i class=\"exicon-dummy\"></i>"}

            {if $log}
            <table class="table sortable">
            <thead>
                <tr>
                    <th><a class="cm-ajax{if $search.sort_by == "timestamp"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=timestamp&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("date")}{if $search.sort_by == "timestamp"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                    <th><a class="cm-ajax{if $search.sort_by == "email"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=email&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("email")}{if $search.sort_by == "email"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                    <th><a class="cm-ajax{if $search.sort_by == "name"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=name&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("person_name")}{if $search.sort_by == "name"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                    <th><a class="cm-ajax{if $search.sort_by == "order_id"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=order_id&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("order_id")}{if $search.sort_by == "order_id"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                    <th><a class="cm-ajax{if $search.sort_by == "amount"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=amount&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("balance")}{if $search.sort_by == "amount"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                    <th><a class="cm-ajax{if $search.sort_by == "debit"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=debit&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("gift_cert_debit")}{if $search.sort_by == "debit"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$log item="l"}
            <tr>
                <td>{$l.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
                <td class="nowrap">{if $l.user_id || $l.order_email}<a href="mailto:{if $l.user_id}{$l.email|escape:url}{else}{$l.order_email|escape:url}{/if}" class="underlined">{if $l.user_id}{$l.email}{else}{$l.order_email}{/if}</a>{else}-{/if}</td>
                <td class="nowrap">
                    {if $l.user_id}
                        <a href="{"profiles.update?user_id=`$l.user_id`"|fn_url}" class="underlined">{$l.firstname} {$l.lastname}</a>
                    {elseif $l.order_id}
                        {$l.order_firstname} {$l.order_lastname}
                    {else}
                        -
                    {/if}
                </td>
                <td>{if $l.order_id}<a href="{"orders.details?order_id=`$l.order_id`&selected_section=payment_information"|fn_url}" class="underlined">&nbsp;{$l.order_id}&nbsp;</a>{else}-{/if}</td>
                <td>
                    {if $addons.gift_certificates.free_products_allow == "Y"}<span>{__("amount")}:</span>&nbsp;{/if}{include file="common/price.tpl" value=$l.amount}
                    {if $l.products && $addons.gift_certificates.free_products_allow == "Y"}
                    <p><span>{__("free_products")}:</span></p>
                    <ul>
                    {foreach from=$l.products item="product_item"}
                        <li>&nbsp;<span>&#187;</span>&nbsp;{$product_item.amount} - {if $product_item.product}<a href="{"products.update?product_id=`$product_item.product_id`"|fn_url}">{$product_item.product|truncate:30:"...":true}</a>{else}{__("deleted_product")}{/if}</li>
                    {/foreach}
                    </ul>
                    {/if}
                </td>
                <td>
                    {if $addons.gift_certificates.free_products_allow == "Y"}<span>{__("amount")}:</span>&nbsp;{/if}{include file="common/price.tpl" value=$l.debit}
                    {if $l.debit_products && $addons.gift_certificates.free_products_allow == "Y"}
                    <p><span>{__("free_products")}:</span></p>
                    {foreach from=$l.debit_products item="product_item"}
                    <div>
                        &nbsp;<span>&#187;</span>&nbsp;{$product_item.amount} - {if $product_item.product}<a href="{"products.update?product_id=`$product_item.product_id`"|fn_url}">{$product_item.product|truncate:30:"...":true}</a>{else}{__("deleted_product")}{/if}
                    </div>
                    {/foreach}
                    {/if}
                </td>
            </tr>
            {/foreach}
            </tbody>
            </table>
            {else}
                <p class="no-items">{__("no_data")}</p>
            {/if}
            {include file="common/pagination.tpl"}
        </div>
        {/capture}
        {include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section}
    {/if}
    {** /Page Section **}

    {/capture}

    {capture name="buttons"}
        {capture name="tools_list"}
            <li>{btn type="list" text=__("preview") class="cm-new-window cm-submit" dispatch="dispatch[gift_certificates.preview]" form="gift_certificates_form"}</li>
            {if $id}
                <li>{btn type="list" text=__("delete") class="cm-confirm" href="gift_certificates.delete?gift_cert_id=$id"}</li>
            {/if}
        {/capture}
        {dropdown content=$smarty.capture.tools_list}

        {if !$id}
            {assign var="title" value=__("new_certificate")}
        {else}
            {assign var="title" value="{__("editing_certificate")}: `$gift_cert_data.gift_cert_code`"}
        {/if}

        {include file="buttons/save_cancel.tpl" but_name="dispatch[gift_certificates.update]" but_role="submit-link" extra=$smarty.capture.gift_extra_tools save=$id but_target_form="gift_certificates_form"}
    {/capture}

{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}