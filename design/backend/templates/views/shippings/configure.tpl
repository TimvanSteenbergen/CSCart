<div id="content_configure">
{if $service_template}
	{if $addons_path[$service_template]}
		{include file="`$addons_path[$service_template]`/`$service_template`.tpl"}
	{else}
		{include file="views/shippings/components/services/`$service_template`.tpl"}
	{/if}
{/if}

<!--content_configure--></div>