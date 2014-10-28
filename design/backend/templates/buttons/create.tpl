{if $but_text}
	{assign var="_but_text" value=$but_text}
{else}
	{assign var="_but_text" value=__("create")}
{/if}
{include file="buttons/button.tpl" but_text=$_but_text but_onclick=$but_onclick but_href=$but_href but_role=$but_role}