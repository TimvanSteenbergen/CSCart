{styles}
	{style src="lib/ui/jqueryui.css"}
    {hook name="index:styles"}
        {style src="styles.less"}
        {style src="glyphs.css"}
        {if $runtime.customization_mode.live_editor || $runtime.customization_mode.design}
        {style src="design_mode.css"}
        {/if}
        {include file="views/statuses/components/styles.tpl"}
    {/hook}
{/styles}