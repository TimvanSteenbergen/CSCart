<div class="sidebar-row">
<h6>{__("search")}</h6>

<form action="{""|fn_url}" name="seo_rules_search_form" method="get">
{capture name="simple_search"}
<div class="sidebar-field">
    <label>{__("seo_name")}</label>
    <input type="text" name="name" size="20" value="{$search.name}" class="search-input-text" />
</div>
<div class="sidebar-field">
    <label>{__("dispatch_value")}</label>
    <input type="text" name="rule_dispatch" size="20" value="{$search.rule_dispatch}" class="input-text" />
</div>
{/capture}

{include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search dispatch=$dispatch view_type="seo_rules" in_popup=$in_popup}

</form>
</div>