{** block-description:store_locator **}

<form action="{""|fn_url}" method="get" name="store_locator_form">

<p><label for="store_locator_search{$block.block_id}" class="required-hidden">{__("search")}:</label></p>

{strip}
<input type="text" size="20" class="input-text" id="store_locator_search{$block.block_id}" name="q" value="{$store_locator_search.q}" />
{include file="buttons/go.tpl" but_name="store_locator.search" alt=__("search")}
{/strip}

</form>