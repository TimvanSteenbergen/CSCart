{capture name="add_seo_redirect"}

<form action="{""|fn_url}" method="post" name="rule_add_var" class="form-horizontal form-edit">
<input type="hidden" name="page" value="{$smarty.request.page}" />

<div class="control-group">
    <label class="control-label cm-required" for="elm_old_url">{__("seo.old_url")}:</label>
    <div class="controls">
        <input type="text" name="redirect_data[src]" id="elm_old_url" value="" class="span9" />
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-required">{__("seo.new_url")}</label>
    <div class="controls mixed-controls cm-bs-group">

        {$id = "s_new_redirect"}
        <div class="form-inline clearfix cm-bs-container">
            <label class="radio pull-left">
                <input type="radio" class="cm-bs-trigger" checked="checked" name="redirect_data[type]" value="s" />
                {__($seo_vars.s.name)}:
            </label>
            <div class="cm-bs-block pull-left disable-overlay-wrap">
                <input type="text" name="redirect_data[dest]" id="s_new_redirect" value="" class="span3" />
                <div class="disable-overlay cm-bs-off hidden"></div>
            </div>
        </div>

    {foreach from=$seo_vars key="var_type" item="seo_var"}
        {if $seo_var.picker}
        {$id = "`$var_type`_new_redirect"}

        <div class="form-inline clearfix cm-bs-container">
            <label class="radio pull-left">
                <input type="radio" class="cm-bs-trigger" name="redirect_data[type]" value="{$var_type}" />
                {__($seo_var.name)}:
            </label>
            <div class="cm-bs-block pull-left disable-overlay-wrap">
                {include_ext
                file=$seo_var.picker
                data_id=$id
                input_name="redirect_data[object_id]"
                view_mode="links"
                params_array=$seo_var.picker_params}
                <div class="disable-overlay cm-bs-off"></div>
            </div>
        </div>
        {/if}
    {/foreach}
    </div>
</div>

{if $addons.seo.single_url != "Y" && $languages|sizeof > 1}
<div class="control-group">
    <label class="control-label cm-required" for="elm_lang_code">{__("language")}:</label>
    <div class="controls">
        <select class="span2" name="redirect_data[lang_code]">
        {foreach from=$languages item=lng}
        <option value="{$lng.lang_code}" {if $lng.lang_code == $smarty.const.CART_LANGUAGE}selected="selected"{/if}>{$lng.name}</option>
        {/foreach}
        </select>
    </div>
</div>
{else}
<input type="hidden" name="redirect_data[lang_code]" value="{$smarty.const.CART_LANGUAGE}" />
{/if}

<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_name="dispatch[seo_redirects.update]" cancel_action="close"}
</div>
</form>

{/capture}

{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="seo_redirects_form" class="form-horizontal form-edit">

{include file="common/pagination.tpl" save_current_page=true save_current_url=true}

{if $seo_redirects}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th width="1%">
        {include file="common/check_items.tpl"}</th>
    <th width="35%">{__("seo.old_url")}</th>
    <th width="35%">{__("seo.new_url")}</th>
    <th width="30%">{__("type")}</th>
    {if $addons.seo.single_url != "Y" && $languages|sizeof > 1}
    <th>{__("language")}</th>
    {/if}
    <th>&nbsp;</th>
</tr>
</thead>
{foreach from=$seo_redirects item="redirect" key="key"}
<tr>
    <td>
        <input type="checkbox" name="redirect_ids[]" value="{$redirect.redirect_id}" class="cm-item" /></td>
    <td>
        <input type="text" class="input-hidden" name="seo_redirects[{$key}][src]" value="{$redirect.src}" /></td>
    <td>
        {if $redirect.type == "s"}
        <input type="text" class="input-hidden" name="seo_redirects[{$key}][dest]" value="{$redirect.dest}" /></td>
        {else}
        <a href="{$redirect.parsed_url}" target="_blank">{$redirect.parsed_url}</a>
        {/if}
    <td>
        {__($seo_vars[$redirect.type].name)}
    </td>
    {if $addons.seo.single_url != "Y" && $languages|sizeof > 1}
    <td>{$languages[$redirect.lang_code].name}</td>
    {/if}    
    <td class="nowrap">

        <div class="hidden-tools">
            {capture name="tools_list"}
                <li>{btn type="list" text=__("delete") class="cm-confirm" href="seo_redirects.delete?redirect_id=`$redirect.redirect_id`"}</li>
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
        {if $seo_redirects}
            <li>{btn type="delete_selected" dispatch="dispatch[seo_redirects.m_delete]" form="seo_redirects_form"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}

    {if $seo_redirects}
        {include file="buttons/save.tpl" but_name="dispatch[seo_redirects.m_update]" but_role="submit-link" but_target_form="seo_redirects_form"}
    {/if}
{/capture}

{capture name="adv_buttons"}
    {include file="common/popupbox.tpl" id="add_seo_redirect" text=__("seo.new_redirect") title=__("add_new") content=$smarty.capture.add_seo_redirect act="general" icon="icon-plus"}
{/capture}

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="seo_redirects.manage" view_type="seo_redirects"}
    {include file="addons/seo/views/seo_redirects/components/search_form.tpl" dispatch="seo_redirects.manage"}
{/capture}

{include file="common/mainbox.tpl" title=__("seo.redirects_manager") content=$smarty.capture.mainbox title_extra=$smarty.capture.title_extra buttons=$smarty.capture.buttons  adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar}