<!DOCTYPE html>
<html lang="en">

<head>
{strip}
<title>
    {if $page_title}
        {$page_title}
    {else}
        {if $navigation.selected_tab}{__($navigation.selected_tab)}{if $navigation.subsection} :: {__($navigation.subsection)}{/if} - {/if}{__("admin_panel")}
    {/if}
</title>
{/strip}
<meta http-equiv="X-UA-Compatible" content="chrome=1">
<meta name="viewport" content="width=1240,maximum-scale=1.0" />
<link href="{$images_dir}/favicon.ico" rel="shortcut icon">
{include file="common/styles.tpl"}
{include file="common/scripts.tpl"}
</head>
{include file="buttons/helpers.tpl"}
<!--[if lte IE 8 ]><body class="ie8"><![endif]-->
<!--[if lte IE 9 ]><body class="ie9"><![endif]-->
<!--[if !IE]><!--><body><!--<![endif]-->     
    {include file="common/loading_box.tpl"}
    {if "THEMES_PANEL"|defined}
        {include file="demo_theme_selector.tpl"}
    {/if}
    {include file="common/notification.tpl"}
    {include file=$content_tpl assign="content"}

    <div id="main_column{if !$auth.user_id || $view_mode == 'simple'}_login{/if}" class="main-wrap">
    {if $view_mode != "simple"}
        <div class="admin-content">

            <div id="header" class="header">
                {include file="menu.tpl"}
            <!--header--></div>

            <div class="admin-content-wrap">
                {hook name="index:main_content"}{/hook}
                {$content nofilter}
                {$stats|default:"" nofilter}
            </div>

        </div>
        {else}
        {$content nofilter}
    {/if}
    
    <!--main_column{if !$auth.user_id || $view_mode == 'simple'}_login{/if}--></div>
    
    {include file="common/comet.tpl"}

    {if $smarty.request.meta_redirect_url|fn_check_meta_redirect}
        <meta http-equiv="refresh" content="1;url={$smarty.request.meta_redirect_url|fn_check_meta_redirect|fn_url}" />
    {/if}

    {if $auth.user_id && (!$runtime.company_id || $runtime.simple_ultimate)}
        {include file="views/settings/store_mode.tpl" show=$show_sm_dialog}
    {/if}

    {hook name="index:after_content"}{/hook}

</body>
</html>
