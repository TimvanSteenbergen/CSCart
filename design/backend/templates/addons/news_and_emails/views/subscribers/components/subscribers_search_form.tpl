{include file="common/saved_search.tpl" dispatch="subscribers.manage" view_type="subscribers"}

<div class="sidebar-row">

<h6>{__("search")}</h6>
<form action="{""|fn_url}" name="subscribers_search_form" method="get">

{capture name="simple_search"}

<div class="sidebar-field">
    <label>{__("email")}</label>
    <input type="text" name="email" size="20" value="{$search.email}" />
</div>

<div class="sidebar-field">
    <label>{__("mailing_list")}</label>
    <select    name="list_id">
        <option    value="">--</option>
        {foreach from=$mailing_lists key="m_id" item="m"}
            <option    value="{$m_id}" {if $search.list_id == $m_id}selected="selected"{/if}>{$m.object}</option>
        {/foreach}
    </select>
</div>

<div class="sidebar-field">
    <label>{__("confirmed")}</label>
    <select    name="confirmed">
        <option    value="">--</option>
        <option    value="Y" {if $search.confirmed == "Y"}selected="selected"{/if}>{__("yes")}</option>
        <option    value="N" {if $search.confirmed == "N"}selected="selected"{/if}>{__("no")}</option>
    </select>
</div>
{/capture}

{capture name="advanced_search"}
<div class="search-field">
    <label for="elm_search_language">{__("language")}:</label>
    <select id="elm_search_language" name="language">
        <option value="">--</option>
        {foreach from=$languages item="lng"}
        <option {if $search.language == $lng.lang_code}selected="selected"{/if} value="{$lng.lang_code}">{$lng.name}</option>
        {/foreach}
    </select>
</div>

<div class="search-field">
    <label>{__("period")}:</label>
    {include file="common/period_selector.tpl" period=$search.period form_name="subscribers_search_form"}
</div>

{/capture}

{include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search content=$smarty.capture.advanced_search dispatch=$dispatch view_type="subscribers"}

</form>

</div>
