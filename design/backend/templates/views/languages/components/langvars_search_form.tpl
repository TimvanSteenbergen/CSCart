<div class="sidebar-row">
<h6>{__("search")}</h6>
<form action="{""|fn_url}" name="langvars_search_form" method="get">

<div class="sidebar-field">
	<label>{__("search_for_pattern")}</label>
	<input type="text" name="q" size="20" value="{$smarty.request.q}" class="search-input-text" />
</div>

{include file="buttons/search.tpl" but_name="dispatch[languages.translations]"}
</form>

</div>