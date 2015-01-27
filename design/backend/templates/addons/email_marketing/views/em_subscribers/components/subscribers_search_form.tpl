{include file="common/saved_search.tpl" dispatch="em_subscribers.manage" view_type="em_subscribers"}

<div class="sidebar-row">

<h6>{__("search")}</h6>
<form action="{""|fn_url}" name="subscribers_search_form" method="get">

{capture name="simple_search"}

<div class="sidebar-field">
    <label>{__("email")}</label>
    <input type="text" name="email" size="20" value="{$search.email}" />
</div>

<div class="sidebar-field">
    <label>{__("person_name")}</label>
    <input type="text" name="name" size="20" value="{$search.name}" />
</div>

{/capture}

{capture name="advanced_search"}

<div class="row-fluid">
<div class="group span6 form-horizontal">
    <div class="control-group">
        <label class="control-label" for="elm_lang_code">{__("language")}</label>
        <div class="controls">
            <select id="elm_lang_code" name="lang_code">
                <option value="">--</option>
                {foreach from=""|fn_get_translation_languages item="language"}
                    <option value="{$language.lang_code}" {if $search.lang_code == $language.lang_code}selected="selected"{/if}>{$language.name}</option>
                {/foreach}
            </select>
        </div>
    </div>
</div>
<div class="group span6 form-horizontal">
    <div class="control-group">
        <label class="control-label" for="elm_status">{__("status")}</label>
        <div class="controls">
            <select id="elm_status" name="status">
                <option value="">--</option>
                <option {if $search.status == "A"}selected="selected"{/if} value="A">{__("active")}</option>
                <option {if $search.status == "P"}selected="selected"{/if}value="P">{__("pending")}</option>
            </select>
        </div>
    </div>
</div>
</div>
<div class="row-fluid">
<div class="group form-horizontal">
    <div class="control-group">
        <label class="control-label">{__("period")}</label>
        <div class="controls">
            {include file="common/period_selector.tpl" period=$search.period form_name="subscribers_search_form"}
        </div>
    </div>
</div>
</div>
{/capture}

{include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search=$smarty.capture.advanced_search dispatch=$dispatch view_type="em_subscribers"}

</form>

</div>
