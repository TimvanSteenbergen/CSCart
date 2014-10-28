{if $payment}
    {assign var="id" value=$payment.payment_id}
{else}
    {assign var="id" value="0"}
{/if}

{assign var="allow_save" value=$payment|fn_allow_save_object:"payments"}

<div id="content_group{$id}">

<form action="{""|fn_url}" method="post" name="payments_form_{$id}" enctype="multipart/form-data" class=" form-horizontal{if !$allow_save} cm-hide-inputs{/if}">
<input type="hidden" name="payment_id" value="{$id}" />

<div class="tabs cm-j-tabs">
    <ul class="nav nav-tabs">
        <li id="tab_details_{$id}" class="cm-js active"><a>{__("general")}</a></li>
        <li id="tab_conf_{$id}" class="cm-js cm-ajax {if !$payment.processor_id}hidden{/if}"><a {if $payment.processor_id}href="{"payments.processor?payment_id=`$id`"|fn_url}"{/if}>{__("configure")}</a></li>
    </ul>
</div>

<div class="cm-tabs-content" id="tabs_content_{$id}">
    <div id="content_tab_details_{$id}">
    <fieldset>
        <div class="control-group">
            <label for="elm_payment_name_{$id}" class="cm-required control-label">{__("name")}:</label>
            <div class="controls">
                <input id="elm_payment_name_{$id}" type="text" name="payment_data[payment]" value="{$payment.payment}">
            </div>
        </div>

        {if "ULTIMATE"|fn_allowed_for && $allow_save}
            {include file="views/companies/components/company_field.tpl"
                name="payment_data[company_id]"
                id="payment_data_`$smarty.request.payment_id`"
                selected=$payment.company_id
            }
        {/if}

        <div class="control-group">
            <label class="control-label" for="elm_payment_processor_{$id}">{__("processor")}:</label>
            <div class="controls">
                <select id="elm_payment_processor_{$id}" name="payment_data[processor_id]" onchange="fn_switch_processor({$id}, this.value);">
                    <option value="">{__("offline")}</option>
                    {hook name="payments:processors_optgroups"}
                    <optgroup label="{__("checkout")}">
                        {foreach from=$payment_processors item="processor"}
                            {if $processor.type != "P"}
                                <option value="{$processor.processor_id}" {if $payment.processor_id == $processor.processor_id}selected="selected"{/if}>{$processor.processor}</option>
                            {/if}
                        {/foreach}
                    </optgroup>
                    <optgroup label="{__("gateways")}">
                        {foreach from=$payment_processors item="processor"}
                            {if $processor.type == "P"}
                                <option value="{$processor.processor_id}" {if $payment.processor_id == $processor.processor_id}selected="selected"{/if}>{$processor.processor}</option>
                            {/if}
                        {/foreach}
                    </optgroup>
                    {/hook}
                </select>
                
                <p id="elm_processor_description_{$id}" class="description {if !$payment_processors[$payment.processor_id].description}hidden{/if}"><br>
                    <small>{$payment_processors[$payment.processor_id].description nofilter}</small>
                </p>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_payment_tpl_{$id}">{__("template")}:</label>
            <div class="controls">
                <select id="elm_payment_tpl_{$id}" name="payment_data[template]" {if $payment.processor_id}disabled="disabled"{/if}>
                    {foreach $templates as $template => $full_path}
                        <option value="{$full_path}" {if $payment.template == $full_path}selected="selected"{/if}>{$template}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_payment_category_{$id}">{__("payment_category")}:</label>
            <div class="controls">
                <select id="elm_payment_category_{$id}" name="payment_data[payment_category]">
                    <option value="tab1" {if $payment.payment_category == "tab1"}selected="selected"{/if}>{__("payments_tab1")}</option>
                    <option value="tab2" {if $payment.payment_category == "tab2"}selected="selected"{/if}>{__("payments_tab2")}</option>
                    <option value="tab3" {if $payment.payment_category == "tab3"}selected="selected"{/if}>{__("payments_tab3")}</option>
                </select>
                <p class="description">
                    <small>{__("payment_category_note")}</small>
                </p>
            </div>
        </div>

        {if !"ULTIMATE:FREE"|fn_allowed_for}
            <div class="control-group">
                <label class="control-label">{__("usergroups")}:</label>
                <div class="controls">
                    {include file="common/select_usergroups.tpl" id="elm_payment_usergroup_`$id`" name="payment_data[usergroup_ids]" usergroups=$usergroups usergroup_ids=$payment.usergroup_ids list_mode=false}
                </div>
            </div>
        {/if}

        <div class="control-group">
            <label class="control-label" for="elm_payment_description_{$id}">{__("description")}:</label>
            <div class="controls">
                <input id="elm_payment_description_{$id}" type="text" name="payment_data[description]" value="{$payment.description}">
            </div>
        </div>

        {hook name="payments:update"}
        {/hook}

        <div class="control-group">
            <label class="control-label" for="elm_payment_surcharge_{$id}">{__("surcharge")}:</label>
                <div class="controls">
                    <input id="elm_payment_surcharge_{$id}" type="text" name="payment_data[p_surcharge]" class="input-mini" value="{$payment.p_surcharge}" size="4"> % + <input type="text" name="payment_data[a_surcharge]" value="{$payment.a_surcharge}" class="input-mini" size="4"> {$currencies.$primary_currency.symbol nofilter}</div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_payment_surcharge_title_{$id}">{__("surcharge_title")}:</label>
            <div class="controls">
                <input id="elm_payment_surcharge_title_{$id}" type="text" name="payment_data[surcharge_title]" value="{$payment.surcharge_title}">
            </div>
        </div>

        <div class="control-group">
        <label class="control-label">{__("taxes")}:</label>
            <div class="controls">
                    {foreach from=$taxes item="tax"}
                        <label for="elm_payment_taxes_{$tax.tax_id}" class="checkbox">
                            <input type="checkbox" name="payment_data[tax_ids][{$tax.tax_id}]" id="elm_payment_taxes_{$tax.tax_id}" {if $tax.tax_id|in_array:$payment.tax_ids}checked="checked"{/if} value="{$tax.tax_id}">
                            {$tax.tax}
                        </label>
                    {foreachelse}
                        <div class="text-type-value">&mdash;</div>
                    {/foreach}
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_payment_instructions_{$id}">{__("payment_instructions")}:</label>
            <div class="controls">
                <textarea id="elm_payment_instructions_{$id}" name="payment_data[instructions]" cols="55" rows="8" class="cm-wysiwyg input-textarea-long">{$payment.instructions}</textarea>
            </div>
            
        </div>

        {if !$id}
            {include file="common/select_status.tpl" input_name="payment_data[status]" id="elm_payment_status_`$id`" obj_id=$id obj=$payment}
        {/if}

        {include file="views/localizations/components/select.tpl" data_name="payment_data[localization]" id="elm_payment_localization_`$id`" data_from=$payment.localization}

        <div class="control-group">
            <label class="control-label">{__("icon")}:</label>
            <div class="controls">{include file="common/attach_images.tpl" image_name="payment_image" image_key=$id image_object_type="payment" image_pair=$payment.icon no_detailed="Y" hide_titles="Y" image_object_id=$id}</div>
        </div>

        {hook name="payments:properties"}
        {/hook}
    </fieldset>
    <!--content_tab_details_{$id}--></div>
</div>

{if !$hide_for_vendor}
    <div class="buttons-container">
        {include file="buttons/save_cancel.tpl" but_name="dispatch[payments.update]" cancel_action="close" save=$id}
    </div>
{/if}

</form>
<!--content_group{$id}--></div>
