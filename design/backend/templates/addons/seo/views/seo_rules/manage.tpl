{capture name="add_seo_rule"}

<form action="{""|fn_url}" method="post" name="rule_add_var" class="form-horizontal form-edit">
<input type="hidden" name="page" value="{$smarty.request.page}" />

<div class="control-group">
    <label class="control-label cm-required" for="rule_name">{__("seo_name")}:</label>
    <div class="controls">
        <input type="text" name="rule_data[name]" id="rule_name" value="" class="span9" />
    </div>
</div>
<div class="control-group">
    <label class="control-label cm-required" for="rule_dispatch">{__("url_dispatch_part")}</label>
    <div class="controls">
        <input type="text" name="rule_data[rule_dispatch]" id="rule_dispatch" value="" class="span9" />
        <p class="muted">{__("controller_description")}</p>
    </div>
</div>

<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_name="dispatch[seo_rules.update]" cancel_action="close"}
</div>
</form>

{/capture}

{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="seo_form" class="form-horizontal form-edit">

{include file="common/pagination.tpl" save_current_page=true save_current_url=true}

{if $seo_data}
<input type="hidden" name="page" value="{$smarty.request.page}" />
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th width="1%">
        {include file="common/check_items.tpl"}</th>
    <th width="35%">{__("dispatch_value")}</th>
    <th width="64%">{__("seo_name")}</th>
    <th>&nbsp;</th>
</tr>
</thead>
{foreach from=$seo_data item="var" key="key"}
<tr>
    <td>
        <input type="checkbox" name="dispatches[]" value="{$var.dispatch}" class="cm-item" /></td>
    <td>
        <input type="hidden" name="seo_data[{$key}][rule_dispatch]" value="{$var.dispatch}" />
        <span>{$var.dispatch}</span></td>
    <td>
        <input type="text" name="seo_data[{$key}][name]" value="{$var.name}" class="input-hidden span7" /></td>
    <td class="nowrap">
        <div class="hidden-tools">
            {capture name="tools_list"}
                {assign var="_dispatch" value="`$var.dispatch`"|escape:url}
                <li>{btn type="list" text=__("delete") href="seo_rules.delete?rule_dispatch=`$_dispatch`"}</li>
            {/capture}
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl"}
</form>
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {if $seo_data}
            <li>{btn type="delete_selected" dispatch="dispatch[seo_rules.m_delete]" form="seo_form"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}

    {if $seo_data}
        {include file="buttons/save.tpl" but_name="dispatch[seo_rules.m_update]" but_role="submit-link" but_target_form="seo_form"}
    {/if}
{/capture}

{capture name="adv_buttons"}
    {include file="common/popupbox.tpl" id="add_seo_rule" text=__("new_rule") title=__("add_new") content=$smarty.capture.add_seo_rule act="general" icon="icon-plus"}
{/capture}

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="seo_rules.manage" view_type="seo_rules"}
    {include file="addons/seo/views/seo_rules/components/search_form.tpl" dispatch="seo_rules.manage"}
{/capture}

{include file="common/mainbox.tpl" title=__("seo_rules") content=$smarty.capture.mainbox title_extra=$smarty.capture.title_extra buttons=$smarty.capture.buttons  adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar select_languages=true}