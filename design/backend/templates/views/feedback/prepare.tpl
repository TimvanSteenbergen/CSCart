<div id="content_groupfeedback">
<form action="{""|fn_url}" method="get" name="feedback_form" class="">

<p>{__("text_feedback_notice")}</p>
<table  width="100%" class="table">
    <thead>
        <tr>
            <th width="15%">{__("section")}</th>
            <th width="20%">{__("param")}</th>
            <th width="65%">{__("value")}</th>
        </tr>
    </thead>
{foreach from=$fdata key="section" item="data"}
<tr>
    <td colspan="3" class="row-gray strong">
        {assign var="lang_section" value=__($section)}
        {if $section|strpos:__("options_for") === false}{$lang_section}{else}{$section}{/if}</td>
</tr>
    {if $section == 'payments' || $section == 'currencies' || $section == 'taxes' || $section == 'shippings' || $section == 'promotions' || $section == 'addons'}
    <tr>
    <td>&nbsp;</td>
    <td colspan="2">
        <div id="parameters_{$section}">
        <table width="80%" class="table table-condensed">
        {foreach from=$data key="key" item="value" name="section"}
            {if $smarty.foreach.section.first}
            <thead><tr>
            {foreach from=$value item="item" key="key" name="param_keys"}
            <th>{$key}</th>
            {/foreach}
            </tr></thead>
            {/if}
            <tr>
            {foreach from=$value item="item" key="key" name="param_items"}
            <td>{if $item == 'Y'}{__("yes")}{elseif $item == 'N'}{__("no")}{else}{$item}{/if}</td>
            {/foreach}
            </tr>
        {/foreach}
        </table>
        <!--parameters_{$section}--></div>
    </td>
    </tr>
    {else}
    {foreach from=$data key="key" item="value" name="section"}
        <tr>
        {if $section=='settings' || $section=='first_company_settings'}
            <td class="nowrap">&nbsp;</td>
            <td>{$value.name}</td>
            <td>{$value.value|replace:'&amp;':'&amp; '}</td>
        {elseif $section=='addons'}
            <td class="nowrap"></td>
            <td class="nowrap">{$value.addon}</td>
            <td>{$value.status}</td>
        {elseif $section=='languages'}
            <td class="nowrap"></td>
            <td class="nowrap">{$value.lang_code}</td>
            <td>{$value.status}</td>
        {else}
            <td class="nowrap"></td>
            <td class="nowrap">{$key}</td>
            <td  width="200px">{$value}</td>
        {/if}
        </tr>
    {/foreach}
    {/if}
{/foreach}
</table>

<div class="buttons-container">
    {include file="buttons/button.tpl" but_name="dispatch[feedback.send]" but_text=__("send") but_role="button_main"}
</div>
</form>
<!--content_groupfeedback--></div>