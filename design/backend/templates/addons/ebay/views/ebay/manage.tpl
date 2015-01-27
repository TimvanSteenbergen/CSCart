{capture name="mainbox"}
<form action="{""|fn_url}" method="post" name="ebay_templates_form" class="">
<input type="hidden" name="fake" value="1" />

{include file="common/pagination.tpl" save_current_page=true save_current_url=true}

{assign var="return_current_url" value=$config.current_url|escape:url}
{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
{assign var="c_icon" value="<i class=\"exicon-`$search.sort_order_rev`\"></i>"}
{assign var="c_dummy" value="<i class=\"exicon-dummy\"></i>"}

<table width="100%" class="table table-middle">
<thead>
<tr>
    <th width="1%" class="left">
        {include file="common/check_items.tpl"}</th>
    <th width="6%"><a class="cm-ajax" href="{"`$c_url`&sort_by=id&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("id")}{if $search.sort_by == "id"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th width="83%"><a class="cm-ajax" href="{"`$c_url`&sort_by=template&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("name")}{if $search.sort_by == "template"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th width="10%" class="right"><a class="cm-ajax" href="{"`$c_url`&sort_by=status&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{if $search.sort_by == "status"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}{__("status")}</a></th>
</tr>
</thead>
{foreach from=$templates item=template}
<tr class="cm-row-status-{$template.status|lower}">
    {assign var="allow_save" value=$template|fn_allow_save_object:"ebay_templates"}
    {if $allow_save}
        {assign var="no_hide_input" value="cm-no-hide-input"}
        {assign var="display" value=""}
    {else}
        {assign var="no_hide_input" value=""}
        {assign var="display" value="text"}
    {/if}
    <td class="left">
        <input type="checkbox" name="template_ids[]" value="{$template.template_id}" class="cm-item" /></td>
    <td class="row-status"><a href="{"ebay.update?template_id=`$template.template_id`"|fn_url}">&nbsp;<span>{$template.template_id}</span>&nbsp;</a></td>
    <td class="row-status">
        <a href="{"ebay.update?template_id=`$template.template_id`"|fn_url}">{$template.name}</a>
        {include file="views/companies/components/company_name.tpl" object=$template}
    </td>
    <td class="right nowrap">
        {include file="common/select_popup.tpl" popup_additional_class="dropleft `$no_hide_input`" display=$display id=$template.template_id status=$template.status hidden=false object_id_name="template_id" table="ebay_templates"}
    </td>
</tr>
{foreachelse}
<tr class="no-items">
    <td colspan="9"><p>{__("no_data")}</p></td>
</tr>
{/foreach}
</table>


{include file="common/pagination.tpl"}
</form>

{capture name="adv_buttons"}
    {include file="common/tools.tpl" tool_href="ebay.add" prefix="top" hide_tools="true" title=__("add_ebay_template") icon="icon-plus"}
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {if $templates}
            <li>{btn type="list" text=__("delete_selected") class="cm-confirm" dispatch="dispatch[ebay.m_delete]" form="ebay_templates_form"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}


{/capture}
{include file="common/mainbox.tpl" title=__("ebay_templates") content=$smarty.capture.mainbox  buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}
