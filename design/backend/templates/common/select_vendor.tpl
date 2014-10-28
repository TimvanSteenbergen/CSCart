{assign var="id" value=$id|default:"company_id"}
{assign var="name" value=$name|default:"company_id"}

{if "MULTIVENDOR"|fn_allowed_for}
    {assign var="lang_search_by_vendor" value=__("search_by_vendor")}
{elseif "ULTIMATE"|fn_allowed_for}
    {assign var="lang_search_by_vendor" value=__("search_by_owner")}
{/if}

{if !$runtime.company_id && !$runtime.simple_ultimate}

<div class="{$class|default:"control-group"}">
    <input type="hidden" name="{$name}" id="{$id}" value="{$search.company_id|default:''}" />
    <label class="control-label">{$lang_search_by_vendor}</label>
    <div class="controls">
    {include file="common/ajax_select_object.tpl" data_url="companies.get_companies_list?show_all=Y&search=Y" text=$search.company_id|fn_get_company_name|default:__("all_vendors") result_elm=$id id="`$id`_selector"}
    </div>
</div>

{/if}