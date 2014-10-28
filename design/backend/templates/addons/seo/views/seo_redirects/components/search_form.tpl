<div class="sidebar-row">
<h6>{__("search")}</h6>

<form action="{""|fn_url}" name="seo_redirects_search_form" method="get">
{capture name="simple_search"}
<div class="sidebar-field">
    <label>{__("seo.old_url")}</label>
    <input type="text" name="src" size="20" value="{$search.src}" class="search-input-text" />
</div>

<div class="sidebar-field">
    <label>{__("type")}</label>
    <select name="type">
    <option value="">--</option>
    {foreach from=$seo_vars key="var_type" item="seo_var"}
    {if $seo_var.picker || $var_type == "s"}
    <option {if $var_type == $search.type}selected="selected"{/if} value="{$var_type}">{__($seo_var.name)}</option>
    {/if}
    {/foreach}
    </select>    
</div>

{if $addons.seo.single_url != "Y" && $languages|sizeof > 1}
<div class="sidebar-field">
    <label>{__("language")}</label>
    <select name="lang_code">
    <option value="">--</option>
    {foreach from=$languages item=lng}
    <option value="{$lng.lang_code}" {if $search.lang_code == $lng.lang_code}selected="selected"{/if} >{$lng.name}</option>
    {/foreach}
    </select>
</div>
{/if}
{/capture}

{include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search dispatch=$dispatch view_type="seo_redirects" in_popup=$in_popup}

</form>
</div>