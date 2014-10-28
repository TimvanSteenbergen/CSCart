{if $destination}
    {assign var="id" value=$destination.destination_id}
{else}
    {assign var="id" value=0}
{/if}

{capture name="mainbox"}

{include file="common/subheader.tpl" title=__("general")}

<form action="{""|fn_url}" method="post" name="destinations_form" class="form-horizontal form-edit {if ""|fn_check_form_permissions} cm-hide-inputs{/if}">
<input type="hidden" name="destination_id" value="{$id}" />

<div class="control-group">
    <label for="elm_destination_name" class="control-label cm-required">{__("name")}:</label>
    <div class="controls">
        <input type="text" name="destination_data[destination]" id="elm_destination_name" size="25" value="{$destination.destination}" class="input-large" />
    </div>
</div>

{include file="views/localizations/components/select.tpl" data_name="destination_data[localization]" data_from=$destination.localization}

{include file="common/select_status.tpl" input_name="destination_data[status]" id="elm_destination_status" obj=$destination}

{notes}
    {__("multiple_selectbox_notice")}
{/notes}

<hr />

{* Countries list *}

{include file="common/double_selectboxes.tpl"
    title=__("countries")
    first_name="destination_data[countries]"
    first_data=$destination_data.countries
    second_name="all_countries"
    second_data=$countries
    class_name="destination-countries"}
<hr />

{* States list *}

{include file="common/double_selectboxes.tpl"
    title=__("states")
    first_name="destination_data[states]"
    first_data=$destination_data.states
    second_name="all_states"
    second_data=$states
    class_name="destination-states"}
<hr />

{* Zipcodes list *}
{include file="common/subheader.tpl" title=__("zipcodes")}
<table width="100%">
<tr>
    <td width="48%">
        <textarea name="destination_data[zipcodes]" id="elm_destination_zipcodes" rows="8" class="input-full">{$destination_data.zipcodes}</textarea></td>
    <td>&nbsp;</td>

    <td width="48%">{__("text_zipcodes_wildcards")}</td>
</tr>
</table>

<hr />

{* Cities list *}
{include file="common/subheader.tpl" title=__("cities")}
<table cellpadding="0" cellspacing="0" width="100%"    border="0">
<tr>
    <td width="48%">
        <textarea name="destination_data[cities]" id="elm_destination_cities" rows="8" class="input-full">{$destination_data.cities}</textarea></td>
    <td>&nbsp;</td>

    <td width="48%">{__("text_cities_wildcards")}</td>
</tr>
</table>

<hr />

{* Addresses list *}
{include file="common/subheader.tpl" title=__("addresses")}
<table cellpadding="0" cellspacing="0" width="100%"    border="0">
<tr>
    <td width="48%">
        <textarea name="destination_data[addresses]" id="elm_destination_cities" rows="8" class="input-full">{$destination_data.addresses}</textarea></td>
    <td>&nbsp;</td>

    <td width="48%">{__("text_addresses_wildcards")}</td>
</tr>
</table>

{capture name="buttons"}
    {include file="buttons/save_cancel.tpl" but_name="dispatch[destinations.update]" but_target_form="destinations_form" save=$id}
{/capture}

</form>
{/capture}

{if !$id}
    {assign var="title" value=__("new_location")}
{else}
    {assign var="title" value="{__("editing_location")}: `$destination.destination`"}
{/if}

{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox buttons=$smarty.capture.buttons select_languages=true}