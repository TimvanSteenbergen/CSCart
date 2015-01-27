{if $localization.data}
    {assign var="id" value=$localization.data.localization_id}
{else}
    {assign var="id" value=0}
{/if}

{capture name="mainbox"}

{*include file="common/prev_next_links.tpl"*}

{if $prev_id || $next_id}<br />{/if}

{capture name="tabsbox"}

<form action="{""|fn_url}" method="post" class="form-horizontal form-edit " name="localization_update_form">
<input type="hidden" name="localization_id" value="{$id}" />

<div id="content_general">
<fieldset>
    <div class="control-group">
        <label for="elm_localization_name" class="control-label cm-required">{__("name")}:</label>
        <div class="controls">
        <input type="text" name="localization_data[localization]" id="elm_localization_name" size="25" value="{$localization.data.localization}" class="input-large" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_localization_is_default">{__("default")}:</label>
        <div class="controls">
        {if $localization.data.is_default == "Y" || !$default_localization}
        <input type="hidden" name="localization_data[is_default]" value="N" />
        <input type="checkbox" name="localization_data[is_default]" id="elm_localization_is_default" value="Y" {if $localization.data.is_default == "Y"}checked="checked"{/if} />
        {else}
        <a href="{"localizations.update?localization_id=`$default_localization.localization_id`"|fn_url}">{$default_localization.localization}</a>
        {/if}
        </div>
    </div>
    

    <div class="control-group">
        <label class="control-label" for="sw_weight_settings">{__("use_custom_weight_settings")}:</label>
        <div class="controls">
        <input type="hidden" name="localization_data[custom_weight_settings]" value="N" />
        <input id="sw_weight_settings" type="checkbox" name="localization_data[custom_weight_settings]" class="cm-combination" value="Y" {if $localization.data.custom_weight_settings == "Y"}checked="checked"{/if} /></div>
    </div>
    
    <div id="weight_settings" {if $localization.data.custom_weight_settings != "Y"}class="hidden"{/if}>
        <div class="control-group">
            <label class="control-label" for="elm_localization_weight_symbol">{__("weight_symbol")}:</label>
            <div class="controls">
            <input type="text" id="elm_localization_weight_symbol" name="localization_data[weight_symbol]" value="{$localization.data.weight_symbol}" class="input-medium" /></div>
        </div>
        <div class="control-group">
            <label class="control-label" for="elm_localization_weight_unit">{__("grams_in_the_unit_of_weight")}:</label>
            <div class="controls">
            <input type="text" id="elm_localization_weight_unit" name="localization_data[weight_unit]" value="{$localization.data.weight_unit}" class="input-medium" /></div>
        </div>
    </div>
</fieldset>
</div>

<div id="content_details">
    <table width="100%">
    <tr>
        <td class="center"><h4 class="nobold">{__("selected_items")}</h4></td>
        <td>&nbsp;</td>
        <td class="center"><h4 class="nobold">{__("available_items")}</h4></td>
    </tr>
    </table>

    <hr />
    
    {* Countries list *}
    
    {include file="common/double_selectboxes.tpl"
        title=__("countries")
        first_name="localization_data[countries]"
        first_data=$localization.countries
        second_name="all_countries"
        second_data=$localization_countries
        required=true}
    
    {* Currencies list *}

    <hr />
    
    {include file="common/double_selectboxes.tpl"
        title=__("currencies")
        first_name="localization_data[currencies]"
        first_data=$localization.currencies
        second_name="all_currencies"
        second_data=$localization_currencies
        required=true}
    
    {* Languages list *}

    <hr />
    
    {include file="common/double_selectboxes.tpl"
        title=__("languages")
        first_name="localization_data[languages]"
        first_data=$localization.languages
        second_name="all_languages"
        second_data=$localization_languages
        required=true}
</div>

{capture name="buttons"}
    {include file="buttons/save_cancel.tpl" but_name="dispatch[localizations.update]" but_target_form="localization_update_form" save=$id}
{/capture}

</form>

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox}

{/capture}
{if !$id}
    {assign var="title" value=__("new_localization")}
{else}
    {assign var="title" value="{__("editing_localization")}: `$localization.data.localization`"}
{/if}
{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons}