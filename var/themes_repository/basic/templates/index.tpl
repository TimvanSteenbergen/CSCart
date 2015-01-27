<!DOCTYPE html>
<html lang="{$smarty.const.CART_LANGUAGE}">
<head>
{capture name="title"}
{hook name="index:title"}
{if $page_title}
    {$page_title}
{else}
    {foreach from=$breadcrumbs item=i name="bkt"}
        {if !$smarty.foreach.bkt.first}{$i.title|strip_tags}{if !$smarty.foreach.bkt.last} :: {/if}{/if}
    {/foreach}
    {if !$skip_page_title && $location_data.title}{if $breadcrumbs|count > 1} - {/if}{$location_data.title}{/if}
{/if}
{/hook}
{/capture}
<title>{$smarty.capture.title|strip|trim nofilter}</title>
{include file="meta.tpl"}
<link href="{$logos.favicon.image.image_path}" rel="shortcut icon" />
{include file="common/styles.tpl" include_dropdown=true}
{include file="common/scripts.tpl"}
</head>

<body>
{if $runtime.customization_mode.design}
    {include file="common/toolbar.tpl" title=__("on_site_template_editing") href="customization.disable_mode?type=design"}
{/if}
{if $runtime.customization_mode.live_editor}
    {include file="common/toolbar.tpl" title=__("on_site_live_editing") href="customization.disable_mode?type=live_editor"}
{/if}
{if "THEMES_PANEL"|defined}
    {include file="demo_theme_selector.tpl"}
{/if}

<div class="tygh {if $runtime.customization_mode.theme_editor}te-mode{/if} {if $runtime.customization_mode.live_editor || $runtime.customization_mode.design}top-panel-padding{/if}" id="tygh_container">

{include file="common/loading_box.tpl"}
{include file="common/notification.tpl"}

<div class="helper-container" id="tygh_main_container">
    {hook name="index:content"}
        {render_location}
    {/hook}
<!--tygh_main_container--></div>


{if $runtime.customization_mode.design}
    {include file="common/template_editor.tpl"}
{/if}
{if $runtime.customization_mode.theme_editor}
    {include file="common/theme_editor.tpl"}
{/if}
{hook name="index:footer"}{/hook}
<!--tygh_container--></div>
</body>

</html>
