{styles use_scheme=true reflect_less=$reflect_less}
{hook name="index:styles"}
    {style src="reset.css"}
    {style src="grid.less"}
    {style src="lib/ui/jqueryui.css"}
    {style src="base.css"}
    {style src="glyphs.css"}
    {style src="styles.css"}
    {style src="print.css" media="print"}

    {if $runtime.customization_mode.live_editor || $runtime.customization_mode.design}
    {style src="design_mode.css"}
    {/if}
    {if $include_dropdown}
    {style src="dropdown.css"}
    {/if}

    {* Theme editor mode *}
    {if $runtime.customization_mode.theme_editor}
        {style src="theme_editor.css"}
    {/if}

    {style src="scheme.less"}
{/hook}
{/styles}
