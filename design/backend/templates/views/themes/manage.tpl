{script src="js/lib/bootstrap_switch/js/bootstrapSwitch.js"}
{style src="lib/bootstrap_switch/stylesheets/bootstrapSwitch.css"}

{include file="common/previewer.tpl"}

{capture name="mainbox"}

{capture name="upload_theme"}
    {include file="views/themes/components/upload_theme.tpl"}
{/capture}

{$theme = $available_themes.current}
{$theme_name = $available_themes.current.theme_name}

<div class="themes" id="themes_list">

<h4>{__("current_theme")}</h4>
<div class="row">
    {capture name="add_new_picker"}
        <form action="{""|fn_url}" method="post" name="clone_theme_{$theme_name}_form" class="cm-ajax cm-comet cm-form-dialog-closer form-horizontal form-edit ">
            <input type="hidden" name="theme_data[theme_src]" value="{$theme_name}">
            <input type="hidden" name="result_ids" value="themes_list,elm_sidebar">

            <div class="add-new-object-group">
                <div class="tabs cm-j-tabs">
                    <ul class="nav nav-tabs">
                        <li id="tab_clone_theme_{$theme_name}" class="cm-js active"><a>{__("general")}</a></li>
                    </ul>
                </div>

                <div class="cm-tabs-content" id="content_tab_clone_theme_{$theme_name}">
                    <fieldset>
                        <div class="control-group">
                            <label class="control-label cm-required" for="elm_theme_dir_{$theme_name}">{__("directory")}</label>
                            <div class="controls">
                                <input type="text" id="elm_theme_dir_{$theme_name}" name="theme_data[theme_dest]" value="{$theme_name}_clone" />
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="elm_theme_title_{$theme_name}">{__("name")}</label>
                            <div class="controls">
                                <input type="text" id="elm_theme_title_{$theme_name}" name="theme_data[title]" value="{$theme.title}" />
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="elm_theme_desc_{$theme_name}">{__("description")}</label>
                            <div class="controls">
                                <textarea name="theme_data[description]" id="elm_theme_desc_{$theme_name}" cols="50" rows="4" class="span9">{$theme.description}</textarea>
                            </div>
                        </div>

                    </fieldset>
                </div>
            </div>

            <div class="buttons-container">
                {include file="buttons/save_cancel.tpl" but_name="dispatch[themes.clone]" cancel_action="close" save=true}
            </div>

        </form>
    {/capture}

{if $theme.screenshot}
    <div id="theme_image">
        {if $theme.styles[$layout.style_id].image}
            <img class="span4 screenshot" width="250" src="{$theme.styles[$layout.style_id].image}">
        {else}
            <img class="span4 screenshot" src="{$images_dir}/user_styles.png" alt="" width="250">
        {/if}

    <!--theme_image--></div>
{/if}
<div class="span8 theme-description" id="theme_description_container">
    <h4 class="lead">{$theme.title}{if $layout.style_name}: {$layout.style_name}{/if}</h4>
    {hook name="themes:current_theme_options"}
    {if $theme.styles}
    <span class="muted">{__("theme_styles_and_layouts")}</span>
        <table class="table table-middle">
            <thead>
                <tr>
                    <th>{__("layout")}</th>
                    <th>{__("theme_editor.style")}</th>
                    <th> </th>
                </tr>
            </thead>
            <tbody>
                {foreach $theme.layouts as $available_layout}
                    <tr>
                        <td>{$available_layout.name}</td>
                        <td>
                            {$styles_descr = []}
                            {foreach $available_themes.current.styles as $style}
                                {$styles_descr[$style.style_id] = $style.name}
                            {/foreach}

                            {include file="common/select_popup.tpl" id=$available_layout.layout_id status=$available_layout.style_id items_status=$styles_descr update_controller="themes.styles" status_target_id="theme_description_container,themes_list" statuses=$available_themes.current.styles btn_meta="btn-text o-status-`$o.status`"|lower default_status_text=__('none')}
                        </td>
                        <td class="right btn-toolbar">
                            {if $available_layout.is_default}
                                {$is_default = 'btn-small btn-primary'}
                            {else}
                                {$is_default = 'btn-small'}
                            {/if}
                            {include file="buttons/button.tpl" but_href="customization.update_mode?type=theme_editor&status=enable&s_layout=`$available_layout.layout_id`" but_text=__("visual_editor") but_role="action" but_meta=$is_default but_target="_blank"}
                            {include file="buttons/button.tpl" but_href="customization.update_mode?type=live_editor&status=enable&s_layout=`$available_layout.layout_id`" but_text=__("edit_content_on_site") but_role="action" but_meta=$is_default but_target="_blank"}
                        </td>
                    <tr>
                {/foreach}
            </tbody>
        </table>
    {else}
        <div class="theme-no-styles">
            <span class="muted">{__("theme_no_styles_text")}</span>
            {include file="buttons/button.tpl" but_href="customization.update_mode?type=theme_editor&status=enable" but_text=__("visual_editor") but_role="action" but_meta="btn-primary"}
        </div>
    {/if}
    {/hook}
<!--theme_description_container--></div>
</div>

{capture name="tabsbox"}

<div id="content_installed_themes">
    <div id="themes_manage" class="themes-current clearfix">

    <div class="themes-available">
    {if $available_themes.installed}
    {foreach $available_themes.installed|array_reverse as $theme_name => $installed_theme}
        <div class="row">
        {if $installed_theme}
            <div class="theme-subtitle">
                {hook name="themes:remove_theme"}
                <a class="cm-confirm cm-tooltip btn pull-right btn-small" data-ce-tooltip-position="top" href="{"themes.delete?theme_name=`$theme_name`"|fn_url}" title="{__("remove_theme")}"> <i class="icon-trash"></i> </a>
                {/hook}
                <span class="label pull-right">{$installed_theme.layouts|count} {__("layouts")}</span>
                <span class="label pull-right">{$installed_theme.styles|count} {__("theme_editor.styles")}</span>
                <h4 id="anchor_{$installed_theme.title|replace:' ':'_'}">{$installed_theme.title}{if $theme.theme_name == $theme_name} <span class="label label-success">{__("active")}</span>{/if}</h4>
            </div>
            {if $installed_theme.styles}
                {foreach $installed_theme.styles as $style}
                    <div class="span4">
                        <div class="theme {if $style.style_id == $layout.style_id && $layout.theme_name == $theme_name}theme-selected{/if}">
                            <div class="theme-title">
                               <span title="{$installed_theme.title}">{$installed_theme.title}: {$style.name}</span>
                            </div>
                            <div class="theme-use">
                                {if $theme_name != $runtime.layout.theme_name}
                                    {$but_text = __("activate")}
                                {else}
                                    {$but_text = __("use_this_style")}
                                {/if}

                                {if $style.style_id == $layout.style_id && $layout.theme_name == $theme_name}
                                    <a href="{$style.image}" data-ca-image-id="img_{$theme_name}_{$style.style_id}" class="theme-in-use cm-previewer">{__("currently_in_use")}</a>
                                {else}
                                    {include file="buttons/button.tpl" but_href="themes.set?theme_name=`$theme_name`&amp;style=`$style.style_id`" but_text=$but_text but_role="action" but_meta="btn-primary"}
                                {/if}
                            </div>
                            {if $style.image}
                                <a id="image_img_{$theme_name}_{$style.style_id}" href="{$style.image}" data-ca-image-id="img_{$theme_name}_{$style.style_id}" class="cm-previewer">
                                    <img class="screenshot" src="{$style.image}" alt="" width="250">
                                </a>
                            {else}
                                <div>
                                    <img class="screenshot" src="{$images_dir}/user_styles.png" alt="" width="250">
                                </div>
                            {/if}
                        </div>
                    </div>
                {/foreach}
                
            {else}
                <div class="span4">
                    <div class="theme">
                        <div class="theme-title">
                           <span title="{$theme.title}">{$installed_theme.title}</span>
                        </div>
                        {if $theme_name != $runtime.layout.theme_name}
                            <div class="theme-use">
                                {include file="buttons/button.tpl" but_href="themes.set?theme_name=`$theme_name`&amp;style=`$style.style_id`" but_text=__("activate") but_role="action" but_meta="btn-primary"}
                            </div>
                        {/if}

                        {if $installed_theme.screenshot}
                            <a id="image_img_{$theme_name}" href="{$installed_theme.screenshot}" data-ca-image-id="img_{$theme_name}" class="cm-previewer"><img class="screenshot" src="{$installed_theme.screenshot}" alt="" width="250"></a>
                        {/if}
                    </div>
                </div>
            {/if}
        {/if}
        <!--/row--></div>
    {/foreach}
    {else}
        <div class="no-items">
            {__("no_themes_available")}
        </div>
    {/if}
    </div>
</div>
</div>
<div id="content_browse_all_available_themes">

{hook name="themes:install_themes"}

    {split data=$available_themes.repo size=3 assign="splitted_themes" simple=true}
    <div class="themes-available">
    
    {if $available_themes.repo}
    {foreach from=$splitted_themes item="repo_themes"}
    <div class="row">
    {foreach from=$repo_themes item="repo_theme" key="theme_name"}
        {if $repo_theme}
            <div class="span4">
                <div class="theme">

                    <div class="theme-title">
                       <span title="{$theme.title}">{$repo_theme.title}</span>
                    </div>

                    <div class="theme-use">
                        {include file="buttons/button.tpl" but_href="themes.install?theme_name=`$theme_name`" but_text=__("install") but_role="action" but_meta="btn-primary cm-comet cm-ajax" but_target_id="themes_list"}
                    </div>

                    {if $repo_theme.screenshot}
                    <a id="image_img_{$theme_name}" href="{$repo_theme.screenshot}" data-ca-image-id="img_{$theme_name}" class="cm-previewer"><img class="screenshot" src="{$repo_theme.screenshot}" alt="" width="250"></a>
                    {/if}

                    <div class="theme-actions">
                        {capture name="tools_list"}

                            {if $repo_theme.screenshot}
                            <li><a id="image_img_{$theme_name}" href="{$repo_theme.screenshot}" data-ca-image-id="img_button_{$theme_name}" class="cm-previewer">{__("preview")}</a></li>
                            {/if}

                            {* <li><a href={$config.resources.demo_store_url}?demo_theme[C]={$theme_name}>{__("live_preview")}</a></li> *}
                            <li><a class="cm-comet cm-ajax" data-ca-target-id="themes_list" href="{"themes.install?theme_name=`$theme_name`"|fn_url}" data-ca-target-id="themes_list">{__("install")}</a></li>
                        {/capture}
                        {dropdown content=$smarty.capture.tools_list placement="right"}
                    </div>
                </div>
            </div>
        {/if}
    {/foreach}
    </div>
    {/foreach}
    {else}
        <div class="no-items">
            {__("no_themes_available")}
        </div>
    {/if}
    </div>
{/hook}

    {$theme_name = $available_themes.current.theme_name}
    <div class="hidden" id="content_elm_clone_theme_{$theme_name}" title="{__("clone_theme")}">
        {$smarty.capture.add_new_picker nofilter}
    </div>

</div>

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section}
<!--themes_list--></div>

{capture name="sidebar"}
    <div class="container themes-side">

        {hook name="themes:settings"}
        <div class="sidebar-row">
            <ul class="unstyled list-with-btns">
                <li>
                    <div class="list-description">
                        {__("rebuild_cache_automatically")} <i class="cm-tooltip icon-question-sign" title="{__("rebuild_cache_automatically_tooltip")}"></i>
                    </div>
                    <div class="switch switch-mini cm-switch-change list-btns" id="rebuild_cache_automatically">
                        <input type="checkbox" name="compile_check" value="1" {if $dev_modes.compile_check}checked="checked"{/if}/>
                    </div>
                </li>
            </ul>
        </div>
        <script type="text/javascript">

            (function (_, $) {
                $(_.doc).on('switch-change', '.cm-switch-change', function (e, data) {
                    var value = data.value;
                    $.ceAjax('request', fn_url("themes.update_dev_mode"), {
                        data: {
                            dev_mode: data.el.prop('name'),
                            state: value ? 1 : 0
                        }
                    });
                });

                $.ceEvent('on', 'ce.ajaxdone', function(){
                    if ($('.switch .switch-mini').length == 0) {
                        $('.switch')['bootstrapSwitch']();
                    }
                });
            }(Tygh, Tygh.$));
        </script>
        <hr>
        {/hook}

        {hook name="themes:options"}
        <div class="sidebar-row clearfix">
            <h6>{__("theme_information")}</h6>
            <div class="row-fluid">
                <div class="span7 muted">{__("name")}</div>
                <div class="span5 right">{$theme.title}</div>
            </div>
            <div class="row-fluid">
                <div class="span7 muted" title="/{$settings.theme_name}">{__("directory")}</div>
                <div class="span5 right"><a class="pull-right" href="{"file_editor.manage?active_section=themes&selected_path=`$settings.theme_name`"|fn_url}">/{$settings.theme_name}</a></div>
            </div>
            <div class="row-fluid">
                <div class="span7 muted">{__("layouts")}</div>
                <div class="span5 right"><a href="{"block_manager.manage"|fn_url}">{$theme.layouts|count}</a></div>
            </div>
            <div class="row-fluid">
                <div class="span7 muted">{__("theme_editor.styles")}</div>
                <div class="span5 right"><a href="#anchor_{$theme.title|replace:' ':'_'}">{$theme.styles|count}</a> </div>
            </div>
            <div class="row-fluid">
                <div class="span7 muted" >{__("developer")}</div>
                <div class="span5 right">{$theme.developer}</div>
            </div>
        </div>
        {/hook}

        <hr>
        <div class="sidebar-row marketplace">
            <h6>{__("marketplace")}</h6>
            <p class="marketplace-link">{__("marketplace_find_more", ["[href]" => $config.resources.marketplace_url])}</p>
        </div>
    </div>
{/capture}

{capture name="adv_buttons"}
    {hook name="themes:adv_buttons"}
    {if ("ULTIMATE"|fn_allowed_for && $runtime.company_id) || ("MULTIVENDOR"|fn_allowed_for && !$runtime.company_id)}
        {include file="common/popupbox.tpl" id="upload_theme" text=__("upload_theme") title=__("upload_theme") content=$smarty.capture.upload_theme act="general" link_class="cm-dialog-auto-size" icon="icon-plus" link_text=""}
    {/if}
    {/hook}
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {hook name="themes:tools_list"}
        <li>{btn type="dialog" text=__("clone_theme") target_id="content_elm_clone_theme_`$theme_name`"}</li>
        {/hook}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}

{/capture}
{include file="common/mainbox.tpl" title={__("themes")} content=$smarty.capture.mainbox sidebar=$smarty.capture.sidebar adv_buttons=$smarty.capture.adv_buttons buttons=$smarty.capture.buttons}
