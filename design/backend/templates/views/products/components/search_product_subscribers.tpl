{capture name="section"}
<form action="{""|fn_url}" class="form-inline" name="subscribers_search_form" method="get">
	<input type="hidden" name="product_id" value="{$product_id}" />
    <input type="hidden" name="selected_section" value="subscribers" />
    <input type="hidden" name="dispatch" value="{$dispatch}" />
    <div class="input-append shift-left">
    <input type="text" name="email" size="20" value="{$search.email}" class="input-medium" placeholder="{__("email")}" />
    {include file="buttons/search.tpl" }
    </div>
</form>
{/capture}
{include file="common/section.tpl" section_content=$smarty.capture.section}