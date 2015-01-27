{if $smarty.request.table_id}
    {assign var="table_id" value=$smarty.request.table_id}
{else}
    {assign var="table_id" value=0}
{/if}

{assign var="report_id" value=$smarty.request.report_id}

{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="statistics_table" class=" form-horizontal form-edit">
<input type="hidden" name="report_id" value="{$report_id}">
<input type="hidden" name="table_id" value="{$table_id}">
<input type="hidden" name="table_data[report_id]" value="{$report_id}">
<input type="hidden" name="selected_section" value="">

{notes}
{__("check_items_text")}
{/notes}

{capture name="tabsbox"}

<div id="content_general">

<fieldset>
<div class="control-group">
    <label for="elm_table_description" class="control-label cm-required">{__("name")}:</label>
    <div class="controls">
        <input type="text" name="table_data[description]" id="elm_table_description" value="{$table.description}" size="70">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_table_position">{__("position")}:</label>
    <div class="controls">
        <input type="text" name="table_data[position]" id="elm_table_position" value="{$table.position}" size="3">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_table_type">{__("type")}:</label>
    <div class="controls">
        <select name="table_data[type]" id="elm_table_type">
            <option value="T">{__("table")}</option>
            <option value="B" {if $table.type == "B"}selected="selected"{/if}>{__("graphic")} [{__("bar")}] </option>
            <option value="P" {if $table.type == "P"}selected="selected"{/if}>{__("graphic")} [{__("pie_3d")}] </option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_update_element_element_id">{__("parameter")}:</label>
    <div class="controls">
        {if $table_id}
            {foreach from=$table.elements item=element}
                <select name="table_data[elements][{$element.element_hash}][element_id]" id="elm_update_element_element_id">
                    {foreach from=$report_elements.parameters item=parameter}
                        {assign var="element_id" value=$parameter.element_id}
                        {assign var="parameter_name" value="reports_parameter_$element_id"}
                        <option value="{$parameter.element_id}" {if $element.element_id==$parameter.element_id}selected="selected"{/if}>{__($parameter_name)}</option>
                    {/foreach}
                </select>
            {/foreach}
        {else}
            <select name="table_data[elements][element_id]" id="elm_update_element_element_id">
                {foreach from=$report_elements.parameters item=parameter}
                    {assign var="element_id" value=$parameter.element_id}
                    {assign var="parameter_name" value="reports_parameter_$element_id"}
                    <option value="{$parameter.element_id}" {if $element.element_id==$parameter.element_id}selected="selected"{/if}>{__($parameter_name)}</option>
                {/foreach}
            </select>
        {/if}
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_table_display">{__("value_to_display")}:</label>
    <div class="controls">
        <select name="table_data[display]" id="elm_table_display">
            {foreach from=$report_elements.values item=element}
                {assign var="element_id" value=$element.element_id}
                {assign var="element_name" value="reports_parameter_$element_id"}
                <option value="{$element.code}" {if $table.display == $element.code}selected="selected"{/if}>{__($element_name)}</option>
            {/foreach}
        </select>
    </div>
</div>

{if $table.type != "P"}
<div class="control-group">
    <label class="control-label" for="elm_table_interval_id">{__("time_interval")}:</label>
    <div class="controls">
        <select name="table_data[interval_id]" id="elm_table_interval_id">
            {foreach from=$intervals item=interval}
                {assign var="interval_id" value=$interval.interval_id}
                {assign var="interval_name" value="reports_interval_$interval_id"}
                <option value="{$interval.interval_id}" {if $table.interval_id==$interval.interval_id}selected="selected"{/if}>{__($interval_name)}</option>
            {/foreach}
        </select>
    </div>
</div>
{/if}

{foreach from=$table.elements item=element}
<div class="control-group">
    <label class="control-label" for="elm_limit_auto">{__("limit")}:</label>
    <div class="controls">
        <input type="text" name="table_data[elements]{if $table_id}[{$element.element_hash}]{/if}[limit_auto]" value="{if $table_id}{$element.limit_auto}{else}5{/if}" size="3" id="elm_limit_auto">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_dependence">{__("dependence")}:</label>
    <div class="controls">
        <select name="table_data[elements]{if $table_id}[{$element.element_hash}]{/if}[dependence]" id="elm_dependence">
            <option value="max_n" {if $element.dependence == "max_n"}selected="selected"{/if}>{__("max_item")}</option>
            <option value="max_p" {if $element.dependence == "max_p"}selected="selected"{/if}>{__("max_amount")}</option>
        </select>
    </div>
</div>
{/foreach}
</fieldset>
<!--id="content_general"--></div>

{************************************************** P A Y M E N T ****************************************}
<div id="content_payment" class="hidden">

    <input name="table_data[conditions][payment]" value="" type="hidden">
    {if $payments}
    <table class="table table-middle">
    <thead>
        <tr>
            <th width="1%">{include file="common/check_items.tpl" check_target="payment"}</th>
            <th width="64%">{__("payment")}</th>
            <th width="20%">{__("processor")}</th>
            <th width="15%" class="center">{__("usergroup")}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$payments item=payment name="pf"}
        <tr>
            <td>
                <input type="checkbox" name="table_data[conditions][payment][]" value="{$payment.payment_id}" {if $conditions.payment[$payment.payment_id]}checked="checked"{/if} class="cm-item-payment"></td>
            <td>
                {$payment.payment}</td>
            <td>
                    {foreach from=$payment_processors item="processor"}
                        {if $payment.processor_id == $processor.processor_id}{$processor.processor}{/if}
                    {/foreach}
            </td>
            <td class="center">
                {if $payment.usergroup}{$payment.usergroup}{else}-{__("all")}-{/if}
            </td>
        </tr>
        {/foreach}
    </tbody>
    </table>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}
<!--id="content_payment"--></div>

{************************************************** L O C A T I O N ****************************************}
<div id="content_location" class="hidden">
    
    <input name="table_data[conditions][location]" value="" type="hidden">
    {if $destinations}
    <table class="table table-middle">
    <thead>
        <tr>
            <th width="1%">{include file="common/check_items.tpl" check_target="location"}</th>
            <th>{__("name")}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$destinations item=destination}
        <tr>
            <td class="center">
                <input name="table_data[conditions][location][]" value="{$destination.destination_id}" type="checkbox" {if $conditions.location[$destination.destination_id]}checked="checked"{/if} class="checkbox cm-item-location"></td>
            <td>
                {$destination.destination}</td>
        </tr>
        {/foreach}
    </tbody>
    </table>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}
<!--id="content_location"--></div>

{************************************************** S T A T U S ****************************************}
<div id="content_status" class="hidden">
    <input name="table_data[conditions][status]" value="" type="hidden">
    {if $order_status_descr}
    <table class="table table-middle">
    <thead>
        <tr>
            <th width="1%">{include file="common/check_items.tpl" check_target="status"}</th>
            <th>{__("status")}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$order_status_descr key=id item=status}
        <tr>
            <td class="center">
                <input name="table_data[conditions][status][]" value="{$id}" type="checkbox" {if $conditions.status.$id}checked="checked"{/if} class="cm-item-status"></td>
            <td>
                {$status}</td>
        </tr>
        {/foreach}
    </tbody>
    </table>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}
<!--id="content_status"--></div>

{************************************************** C A T E G O R Y ****************************************}
<div id="content_category" class="hidden">
    {include file="pickers/categories/picker.tpl" input_name="table_data[conditions][category]" data_id="categories_list" item_ids=$conditions.category no_item_text=__("no_items") category_id=$c_ids multiple=true placement=right}
</div>


{************************************************** O R D E R ****************************************}
<div id="content_order" class="hidden">
    {include file="pickers/orders/picker.tpl" item_ids=$conditions.order no_item_text=__("no_items") data_id="order_items" input_name="table_data[conditions][order]"}
</div>


{************************************************** P R O D U C T ****************************************}
<div id="content_product" class="hidden">
    {include file="pickers/products/picker.tpl" input_name="table_data[conditions][product]" data_id="added_products" item_ids=$conditions.product type="links" placement=right}
</div>


{************************************************** U S E R ****************************************}
<div id="content_user" class="hidden">
    {include file="views/profiles/components/profiles_scripts.tpl"}
    {include file="pickers/users/picker.tpl" no_item_text=__("no_items") data_id="sales_rep_users" input_name="table_data[conditions][user]" item_ids=$conditions.user placement="right" but_meta="btn" but_icon="icon-plus"}
</div>

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section track=true}


{capture name="buttons"}
    {capture name="tools_list"}
        {if $table_id}
            <li>{btn type="list" text=__("view_report") href="sales_reports.view?report_id=$report_id&table_id=`$table_id`"}</li>
            <li>{btn type="list" text=__("clear_conditions") href="sales_reports.clear_conditions?table_id=`$table_id`&report_id=`$report_id`"}</li>
            {assign var="select_languages" value="true"}
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}

    {include file="buttons/save_cancel.tpl" but_name="dispatch[sales_reports.update_table]" hide_second_button=true save=$id but_target_form="statistics_table" but_role="submit-link" save=$table_id}
{/capture}

</form>
{/capture}

{if $table_id}
    {assign var="_title" value="{__("editing_chart")}: `$table.description`"}
{else}
    {assign var="_title" value=__("new_chart")}
{/if}
{include file="common/mainbox.tpl" title=$_title content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons}
