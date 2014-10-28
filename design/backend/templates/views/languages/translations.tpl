{capture name="mainbox"}

<div id="content_translations">

<form action="{""|fn_url}" method="post" name="language_variables_form">
<input type="hidden" name="q" value="{$smarty.request.q}">
<input type="hidden" name="selected_section" value="{$smarty.request.selected_section}">

{include file="common/pagination.tpl" save_current_page=true save_current_url=true}
{$c_url = $config.current_url|escape:url}

{if $lang_data}
<table class="table" width="100%">
<thead>
    <tr>
        <th width="1%">{include file="common/check_items.tpl"}</th>
        <th width="60%">{__("value")}</th>
        <th width="33%">{__("language_variable")}</th>
        <th>&nbsp;</th>
    </tr>
</thead>
<tbody>
{foreach from=$lang_data item="var" key="key"}
<tr>
    <td>
        <input type="checkbox" name="names[]" value="{$var.name}" class="checkbox cm-item">
    </td>
    <td>
        <textarea name="lang_data[{$key}][value]" rows="3" class="span7">{$var.value}</textarea>
    </td>
    <td>
        <input type="hidden" name="lang_data[{$key}][name]" value="{$var.name}">
        <p class="lang-name"><span>{$var.name}</span></p>
    </td>
    <td>
        {if "ULTIMATE"|fn_allowed_for && !$runtime.company_id}
            {include file="buttons/update_for_all.tpl" display=true object_id=$key name="lang_data[`$key`][overwrite]"}
        {/if}
        {capture name="tools_items"}
        <a class="cm-confirm" href="{"languages.delete_variable?name=`$var.name`&redirect_url=`$c_url`"|fn_url}" title="{__("delete")}">
            {if "ULTIMATE"|fn_allowed_for}
                {if $runtime.company_id}
                    {__("restore_default")}
                {/if}
            {else}
                <i class="icon-trash"></i>
            {/if}
        </a>
        {/capture}
        <div class="hidden-tools">
            {include file="common/table_tools_list.tpl" prefix=$var.name tools_list=$smarty.capture.tools_items}
        </div>
    </td>
</tr>
{/foreach}
</tbody>
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}
{include file="common/pagination.tpl"}
</form>

{if $lang_data}
    {capture name="delete_button"}
        {$smarty.capture.delete_button}
        <li class="cm-tab-tools" id="tools_translations_delete_buttons">
            {btn type="delete_selected" dispatch="dispatch[languages.m_delete_variables]" form="language_variables_form"}
        </li>
    {/capture}

    {capture name="add_button"}
        {$smarty.capture.add_button}
        <span class="cm-tab-tools btn-group" id="tools_translations_save_button">
            {include file="buttons/save.tpl" but_name="dispatch[languages.m_update_variables]" but_role="submit-link" but_target_form="language_variables_form"}
        </span>
    {/capture}
{/if}


{capture name="add_langvar"}

<form action="{""|fn_url}" method="post" name="lang_add_var">
<input type="hidden" name="page" value="{$smarty.request.page}" />
<input type="hidden" name="q" value="{$smarty.request.q}" />
<input type="hidden" name="selected_section" value="{$smarty.request.selected_section}" />

<table class="table">
<thead>
    <tr class="cm-first-sibling">
        <th width="40%">{__("language_variable")}</th>
        <th width="50%">{__("value")}</th>
        <th width="10%">&nbsp;</th>
    </tr>
</thead>
<tbody>
    <tr id="box_new_lang_tag" valign="top">
        <td>
            <input type="text" size="30" name="new_lang_data[0][name]"></td>
        <td>
            <textarea name="new_lang_data[0][value]" cols="48" rows="2"></textarea></td>
        <td>
            {include file="buttons/multiple_buttons.tpl" item_id="new_lang_tag"}</td>
    </tr>
</tbody>
</table>

<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_name="dispatch[languages.update_variables]" cancel_action="close"}
</div>

</form>

{/capture}

</div>

{$smarty.capture.popups nofilter}

{/capture}

{capture name="sidebar"}
    {include file="views/languages/components/langvars_search_form.tpl"}
{/capture}

{capture name="adv_buttons"}
    {include file="common/popupbox.tpl" id="add_langvar" text=__("new_language_variable") title=__("add_language_variable") content=$smarty.capture.add_langvar act="general" icon="icon-plus"}
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        <li>{btn type="list" text=__("on_site_live_editing") href="customization.update_mode?type=live_editor&status=enable"|fn_url target="_blank"}</li>
        <li class="divider"></li>
        {$smarty.capture.delete_button nofilter}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}

    {$smarty.capture.add_button nofilter}
{/capture}

{include file="common/mainbox.tpl" title=__("translations") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar select_languages=true}
