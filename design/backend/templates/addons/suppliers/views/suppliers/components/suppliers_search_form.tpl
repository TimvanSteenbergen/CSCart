{if $in_popup}
    <div class="adv-search">
    <div class="group">
{else}
    <div class="sidebar-row">
    <h6>{__("search")}</h6>
{/if}
<form name="suppliers_search_form" action="{""|fn_url}" method="get" class="{$form_meta}">

{if $smarty.request.redirect_url}
<input type="hidden" name="redirect_url" value="{$smarty.request.redirect_url}" />
{/if}

{if $put_request_vars}
{foreach from=$smarty.request key="k" item="v"}
{if $v && $k != "callback"}
<input type="hidden" name="{$k}" value="{$v}" />
{/if}
{/foreach}
{/if}

{capture name="simple_search"}
{$extra nofilter}
<div class="sidebar-field">
    <label for="elm_name">{__("name")}</label>
    <div class="break">
        <input type="text" name="name" id="elm_name" value="{$search.name}" />
    </div>
</div>
<div class="sidebar-field">
    <label for="elm_company">{__("company")}</label>
    <div class="break">
        <input type="text" name="company" id="elm_company" value="{$search.company}" />
    </div>
</div>
<div class="sidebar-field">
    <label for="elm_email">{__("email")}</label>
    <div class="break">
        <input type="text" name="email" id="elm_email" value="{$search.email}" />
    </div>
</div>
{/capture}

{capture name="advanced_search"}

{hook name="profiles:search_form"}
{/hook}

<div class="group">
    <div class="control-group">
        <label class="control-label">{__("ordered_products")}</label>
        <div class="controls">
            {include file="common/products_to_search.tpl"}
        </div>
    </div>
</div>

{/capture}

{include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search=$smarty.capture.advanced_search dispatch=$dispatch view_type="suppliers" in_popup=$in_popup}

</form>

{if $in_popup}
</div></div>
{else}
</div><hr>
{/if}