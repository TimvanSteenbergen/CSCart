
{if $field}
    {assign var="id" value=$field.field_id}
{else}
    {assign var="id" value="0"}
{/if}

{script src="js/tygh/tabs.js"}

{literal}
<script type="text/javascript">
function fn_check_field_type(value, tab_id)
{
    Tygh.$('#' + tab_id).toggleBy(!(value == 'R' || value == 'S'));
}
</script>
{/literal}

{if $field.is_default == "Y" || $field.section == "B"}
    {assign var="block_fields" value=true}
{/if}

{if "ULTIMATE"|fn_allowed_for && $runtime.company_id}
    {assign var="hide_inputs" value="cm-hide-inputs"}
    {assign var="hide_multiple_buttons" value="hidden"}
{/if}

{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="add_fields_form" class="form-horizontal form-edit  {$hide_inputs}">

<div class="cm-j-tabs cm-track tabs">
    <ul class="nav nav-tabs">
        <li id="tab_new_profile{$id}" class="cm-js active"><a>{__("general")}</a></li>
        <li id="tab_variants{$id}" class="cm-js {if $block_fields || ($field.field_type != "R" && $field.field_type != "S")}hidden{/if}"><a>{__("variants")}</a></li>
    </ul>
</div>
<div class="cm-tabs-content">
    <div id="content_tab_new_profile{$id}">
        <input type="hidden" name="field_data[field_id]" value="{$field.field_id}" />
        <input type="hidden" name="field_data[matching_id]" value="{$field.matching_id}" />
        <input type="hidden" name="field_id" value="{$id}" />
        <input type="hidden" name="field_data[field_name]" value="{$field.field_name}" />
        <div class="control-group">
            <label for="elm_field_description" class="control-label cm-required">{__("description")}:</label>
            <div class="controls">
            <input id="elm_field_description" class="input-large" type="text" name="field_data[description]" value="{$field.description}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_field_position">{__("position")}:</label>
            <div class="controls">
            <input class="input-text-short" id="elm_field_position" type="text" size="3" name="field_data[position]" value="{$field.position}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_field_type">{__("type")}:</label>
            <div class="controls">
                {if strpos("AON", $field.field_type) === false}
                    <select id="elm_field_type" name="field_data[field_type]" onchange="fn_check_field_type(this.value, 'tab_variants{$id}');" {if $block_fields}disabled="disabled"{/if}>
                        <option value="P" {if $field.field_type == "P"}selected="selected"{/if}>{__("phone")}</option>
                        <option value="Z" {if $field.field_type == "Z"}selected="selected"{/if}>{__("zip_postal_code")}</option>
                        <option value="C" {if $field.field_type == "C"}selected="selected"{/if}>{__("checkbox")}</option>
                        <option value="D" {if $field.field_type == "D"}selected="selected"{/if}>{__("date")}</option>
                        <option value="I" {if $field.field_type == "I"}selected="selected"{/if}>{__("input_field")}</option>
                        <option value="R" {if $field.field_type == "R"}selected="selected"{/if}>{__("radiogroup")}</option>
                        <option value="S" {if $field.field_type == "S"}selected="selected"{/if}>{__("selectbox")}</option>
                        <option value="T" {if $field.field_type == "T"}selected="selected"{/if}>{__("textarea")}</option>
                        <option value="E" {if $field.field_type == "E"}selected="selected"{/if}>{__("email")}</option>
                    </select>
                {else}
                    <select id="elm_field_type" name="field_data[field_type]" disabled="disabled">
                        <option value="A" {if $field.field_type == "A"}selected="selected"{/if}>{__("states")}</option>
                        <option value="O" {if $field.field_type == "O"}selected="selected"{/if}>{__("country")}</option>
                        <option value="N" {if $field.field_type == "N"}selected="selected"{/if}>{__("address_type")}</option>
                    </select>
                {/if}
            {if $block_fields}
                <input type="hidden" name="field_data[field_type]" value="{$field.field_type}" />
            {/if}
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_field_section">{__("section")}:</label>
            <div class="controls">
            {if $id}
                <input type="hidden" name="field_data[section]" value="{$field.section}" />
                <span class="shift-input">
                    {if $field.section == "C"}{__("contact_information")}{elseif $field.section == "B" || $field.section == "S"}{__("billing_address")}/{__("shipping_address")}{/if}
                </span>
            {else}
                <select id="elm_field_section" name="field_data[section]">
                    <option value="C">{__("contact_information")}</option>
                    <option value="BS">{__("billing_address")}/{__("shipping_address")}</option>
                </select>
            {/if}
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_field_user_class">{__("user_class")}:</label>
            <div class="controls">
                <input id="elm_field_user_class" class="input-large" type="text" name="field_data[class]" value="{$field.class}" />
            </div>
        </div>
        
        {foreach from=$profile_fields_areas key="key" item="d"}
        {assign var="_show" value="`$key`_show"}
        {assign var="_required" value="`$key`_required"}
        <div class="control-group">
            <label class="control-label">{__($d)} ({__("show")}&nbsp;/&nbsp;{__("required")}):</label>
            <div class="controls">
                <input type="hidden" name="field_data[{$_show}]" value="{if $field.$_show == "Y" && $field.field_name == "email"}Y{else}N{/if}" />
                <input type="checkbox" name="field_data[{$_show}]" value="Y" {if $field.$_show == "Y"}checked="checked"{/if} id="sw_req_{$_required}" class="cm-switch-availability checkbox" {if $field.field_name == "email"}disabled="disabled"{/if} />&nbsp;

                <input type="hidden" name="field_data[{$_required}]" value="{if $field.field_name == "email"}Y{else}N{/if}" />
                <span id="req_{$_required}{if $field.field_name == "email"}_email{/if}"><input type="checkbox" name="field_data[{$_required}]" value="Y" {if $field.$_required == "Y"}checked="checked"{/if} {if $field.$_show == "N" || $field.field_name == "email"}disabled="disabled"{/if} class="checkbox" /></span>
            </div>
        </div>
        {/foreach}
    <!--content_tab_new_profile{$id}--></div>

    <div class="{if $block_fields || ($field.field_type != "R" && $field.field_type != "S")}hidden{/if}" id="content_tab_variants{$id}">
        <table class="table table-middle">
        <tr id="field_values_{$id}" class="no-border td-no-bg">
            <td colspan="{$_colspan}">
                <table width="1" class="table">
                    <thead>
                        <tr class="cm-first-sibling">
                            <th style="width: 8%">{__("position_short")}</th>
                            <th style="width: 68%">{__("description")}</th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>
                {if $field}
                    {foreach name="values" from=$field.values key="value_id" item="value"}
                    <tr class="cm-first-sibling">
                        <td>
                            <input class="input-micro" size="3" type="text" name="field_data[values][{$value_id}][position]" value="{$smarty.foreach.values.iteration}" />
                        </td>
                        <td>
                            <input class="span7" type="text" name="field_data[values][{$value_id}][description]" value="{$value}" />
                        </td>
                        <td>
                            {include file="buttons/multiple_buttons.tpl" only_delete="Y"}
                        </td>
                    </tr>
                    {/foreach}
                {/if}
                <tr id="box_elm_values_{$id}" {if $hide_multiple_buttons}class="{$hide_multiple_buttons}"{/if}>
                    <td><input class="input-micro" size="3" type="text" name="field_data[add_values][0][position]" /></td>
                    <td><input class="span7" type="text" name="field_data[add_values][0][description]" /></td>
                    <td>{include file="buttons/multiple_buttons.tpl" item_id="elm_values_`$id`" tag_level=2}</td>
                </tr>
                </table>
            </td>
        </tr>
        </table>
    <!--content_tab_variants{$id}--></div>
</div>
</form>

{capture name="buttons"}
    {include file="buttons/save_cancel.tpl" but_name="dispatch[profile_fields.update]" but_target_form="add_fields_form" save=$id}
{/capture}

{/capture}

{if !$id}
    {assign var="title" value=__("new_profile_field")}
{else}
    {assign var="title" value="{__("editing_profile_field")}:&nbsp;`$field.description`"}
{/if}
{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons}
