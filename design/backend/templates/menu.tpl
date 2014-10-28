{if "THEMES_PANEL"|defined}
    {$sticky_scroll = 5}
    {$sticky_padding = 36}
{else}
    {$sticky_scroll = 41}
    {$sticky_padding = 0}
{/if}

{function menu_attrs attrs=[]}
    {foreach $attrs as $attr => $value}
        {$attr}="{$value}" 
    {/foreach}
{/function}
<div class="navbar-admin-top">
    <!--Navbar-->
    <div class="navbar navbar-inverse" id="header_navbar">
        <div class="navbar-inner">

        {if $runtime.company_data.company}
            {$name = $runtime.company_data.company}
        {else}
            {$name = $settings.Company.company_name}
        {/if}

        {if "ULTIMATE"|fn_allowed_for}
            <div class="nav-ult">
                {hook name="menu:storefront_icon"}
                {if !$runtime.company_data.company_id}
                    {assign var="name" value=__("all_vendors")}
                {/if}
                {if $runtime.company_data.storefront}
                    <a href="{"?company_id=`$runtime.company_data.company_id`"|fn_url:"C"}" target="_blank" class="brand" title="{__("view_storefront")}">
                        <i class="icon-shopping-cart icon-white"></i>
                    </a>
                {else}
                    <a class="brand" title="{__("storefront_url_not_defined")}"><i class="icon-shopping-cart icon-white cm-tooltip"></i></a>
                {/if}
                {/hook}
                {if $runtime.companies_available_count > 1}
                    <ul class="nav">
                    {capture name="extra_content"}
                        {if fn_check_view_permissions("companies.manage", "GET")}
                            <li class="divider"></li>
                            <li><a href="{"companies.manage?switch_company_id=0"|fn_url}">{__("manage_stores")}...</a></li>
                        {/if}
                    {/capture}

                    {include file="common/ajax_select_object.tpl"
                        data_url="companies.get_companies_list?show_all=Y&action=href"
                        text=$name
                        id="top_company_id"
                        type="list"
                        extra_content=$smarty.capture.extra_content
                    }

                    </ul>
                {else}
                    <ul class="nav">
                        {if $auth.company_id}
                            <li class="dropdown">
                                <a href="{"companies.update?company_id=`$runtime.company_id`"|fn_url}">{__("vendor")}: {$runtime.company_data.company}</a>
                            </li>
                        {else}
                            {if fn_check_view_permissions("companies.manage", "GET")}
                                <li class="dropdown vendor-submenu">
                                    <a class=" dropdown-toggle" data-toggle="dropdown">
                                        <span>{$name|truncate:60:"...":true}</span><b class="caret"></b>
                                    </a>
                                    <ul class="dropdown-menu" id="top_company_id_ajax_select_object">
                                        <li><a href="{"companies.manage?switch_company_id=0"|fn_url}">{__("manage_stores")}...</a></li>
                                    </ul>
                                </li>
                            {/if}
                        {/if}
                    </ul>
                {/if}
            </div>
        {/if}

        {if "MULTIVENDOR"|fn_allowed_for && !$runtime.simple_ultimate}
            <ul class="nav">
                <a href="{$config.http_location|fn_url|escape}" target="_blank" class="brand" title="{__("view_storefront")}">
                    <i class="icon-shopping-cart icon-white"></i>
                </a>
                <a href="{""|fn_url}" class="brand">{$settings.Company.company_name|truncate:60:"...":true}</a>
                {if $runtime.customization_mode.live_editor}
                    {assign var="company_name" value=$runtime.company_data.company}
                {else}
                    {assign var="company_name" value=$runtime.company_data.company|truncate:43:"...":true}
                {/if}

                {if $auth.company_id}
                    <li class="dropdown">
                        <a href="{"companies.update?company_id=`$runtime.company_id`"|fn_url}">{__("vendor")}: {$runtime.company_data.company}</a>
                    </li>
                {else}
                    {if fn_check_view_permissions("companies.get_companies_list", "GET")}
                        {capture name="extra_content"}
                            <li class="divider"></li>
                            <li><a href="{"companies.manage?switch_company_id=0"|fn_url}">{__("manage_vendors")}...</a></li>
                        {/capture}

                        {include file="common/ajax_select_object.tpl" data_url="companies.get_companies_list?show_all=Y&action=href" text=$company_name id="top_company_id" type="list" extra_content=$smarty.capture.extra_content}
                    {else}
                        <li class="dropdown">
                            <a class="unedited-element">{$company_name}</a>
                        </li>
                    {/if}
                {/if}
            </ul>
        {/if}
            <ul class="nav hover-show navbar-right" aria-labelledby="dropdownMenu">
            {if $auth.user_id && $navigation.static}

                {foreach from=$navigation.static.top key=first_level_title item=m name="first_level_top"}
                    <li class="dropdown dropdown-top-menu-item{if $first_level_title == $navigation.selected_tab} active{/if}">
                        <a id="elm_menu_{$first_level_title}" href="#" class="dropdown-toggle {$first_level_title}" data-toggle="dropdown">
                            {__($first_level_title)}
                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu">
                            {foreach from=$m.items key=second_level_title item="second_level" name="sec_level_top"}
                                <li class="{if $second_level.subitems}dropdown-submenu{/if}{if $second_level_title == $navigation.subsection} active{/if} {if $second_level.is_promo}cm-promo-popup{/if} {$second_level.attrs.class}" {menu_attrs attrs=$second_level.attrs.main}>
                                    {if $second_level.type == "title"}
                                        <a id="elm_menu_{$first_level_title}_{$second_level_title}" {if $second_level.attrs.class_href}class="{$second_level.attrs.class_href}"{/if} {menu_attrs attrs=$second_level.attrs.href}>{$second_level.title|default:__($second_level_title)}</a>
                                    {elseif $second_level.type != "divider"}
                                        <a id="elm_menu_{$first_level_title}_{$second_level_title}" {if $second_level.attrs.class_href}class="{$second_level.attrs.class_href}"{/if} href="{$second_level.href|fn_url}" {menu_attrs attrs=$second_level.attrs.href}>{$second_level.title|default:__($second_level_title)}</a>
                                    {/if}
                                    {if $second_level.subitems}
                                        <ul class="dropdown-menu">
                                            {foreach from=$second_level.subitems key=subitem_title item=sm}
                                                <li class="{if $sm.active}active{/if} {if $sm.is_promo}cm-promo-popup{/if} {$second_level.attrs.class}" {menu_attrs attrs=$sm.attrs.main}>
                                                    {if $sm.type == "title"}
                                                        {__($subitem_title)}
                                                    {elseif $sm.type != "divider"}
                                                        <a id="elm_menu_{$first_level_title}_{$second_level_title}_{$subitem_title}" href="{$sm.href|fn_url}" {menu_attrs attrs=$sm.attrs.href}>{__($subitem_title)}</a>
                                                    {/if}
                                                </li>
                                                {if $sm.type == "divider"}
                                                    <li class="divider"></li>
                                                {/if}
                                            {/foreach}
                                        </ul>
                                    {/if}
                                </li>
                                {if $second_level.type == "divider"}
                                    <li class="divider"></li>
                                {/if}
                            {/foreach}
                        </ul>
                    </li>
                {/foreach}
            {/if}
                <!-- end navbar-->

            {if $auth.user_id}

                {if $languages|sizeof > 1 || $currencies|sizeof > 1}
                    <li class="divider-vertical"></li>
                {/if}

                <!--language-->
                {if !"ULTIMATE:FREE"|fn_allowed_for}
                    {if $languages|sizeof > 1}
                        {include file="common/select_object.tpl" style="dropdown" link_tpl=$config.current_url|fn_link_attach:"sl=" items=$languages selected_id=$smarty.const.CART_LANGUAGE display_icons=true key_name="name" key_selected="lang_code" class="languages"}
                    {/if}
                {/if}
                <!--end language-->

                <!--Curriencies-->
                {if $currencies|sizeof > 1}
                {include file="common/select_object.tpl" style="dropdown" link_tpl=$config.current_url|fn_link_attach:"currency=" items=$currencies selected_id=$secondary_currency display_icons=false key_name="description" key_selected="currency_code"}
                {/if}
                <!--end curriencies-->

                <li class="divider-vertical"></li>

                <!-- user menu -->
                <li class="dropdown dropdown-top-menu-item">
                    {hook name="index:top_links"}
                        <a class="dropdown-toggle" data-toggle="dropdown">
                            <i class="icon-white icon-user"></i>
                            <b class="caret"></b>
                        </a>
                    {/hook}
                    <ul class="dropdown-menu pull-right">
                        <li class="disabled">
                            <a><strong>{__("signed_in_as")}</strong><br>{if $settings.General.use_email_as_login == "Y"}{$user_info.email}{else}{$user_info.user_login}{/if}</a>
                        </li>
                        <li class="divider"></li>
                        {hook name="menu:profile"}
                        <li><a href="{"profiles.update?user_id=`$auth.user_id`"|fn_url}">{__("edit_profile")}</a></li>
                        <li><a href="{"auth.logout"|fn_url}">{__("sign_out")}</a></li>
                        {if !$runtime.company_id}
                            <li class="divider"></li>
                            <li>
                                {include file="common/popupbox.tpl" id="group`$id_prefix`feedback" edit_onclick=$onclick text=__("feedback_values") act="link" picker_meta="cm-clear-content" link_text=__("send_feedback", ["[product]" => $smarty.const.PRODUCT_NAME]) content=$smarty.capture.update_block href="feedback.prepare" no_icon_link=true but_name="dispatch[feedback.send]" opener_ajax_class="cm-ajax"}
                            </li>
                        {/if}
                        {/hook}
                    </ul>
                </li>
                <!--end user menu -->
            {/if}
            </ul>

        </div>
    <!--header_navbar--></div>

    <!--Subnav-->
    <div class="subnav cm-sticky-scroll" data-ce-top="{$sticky_scroll}" data-ce-padding="{$sticky_padding}" id="header_subnav">
        <!--quick search-->
        <div class="search pull-right">
            {hook name="index:global_search"}
                <form id="global_search" method="get" action="{""|fn_url}">
                    <input type="hidden" name="dispatch" value="search.results" />
                    <input type="hidden" name="compact" value="Y" />
                    <button class="icon-search cm-tooltip " type="submit" title="{__("search_tooltip")}" id="search_button">{__("go")}</button>
                    <label for="gs_text"><a><input type="text" class="cm-autocomplete-off" id="gs_text" name="q" value="{$smarty.request.q}" /></a></label>
                </form>
            {/hook}

        </div>
        <!--end quick search-->

        <!-- quick menu -->
        {include file="common/quick_menu.tpl"}
        <!-- end quick menu -->

        <ul class="nav hover-show nav-pills">
            <li><a href="{""|fn_url}" class="home"><i class="icon-home"></i></a></li>
        {if $auth.user_id && $navigation.static}
            {foreach from=$navigation.static.central key=first_level_title item=m name="first_level"}
                <li class="dropdown {if $first_level_title == $navigation.selected_tab} active{/if} ">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" >
                        {__($first_level_title)}
                        <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu">
                        {foreach from=$m.items key=second_level_title item="second_level" name="sec_level"}
                            <li class="{$second_level_title}{if $second_level.subitems} dropdown-submenu{/if}{if $second_level_title == $navigation.subsection && $first_level_title == $navigation.selected_tab} active{/if}" {menu_attrs attrs=$second_level.attrs.main}><a class="{if $second_level.is_promo}cm-promo-popup{/if} {$second_level.attrs.class}" {if !$second_level.is_promo}href="{$second_level.href|fn_url}"{/if} {menu_attrs attrs=$second_level.attrs.href}>
                                <span>{__($second_level_title)}{if $second_level.attrs.class == 'is-addon'}<i class="icon-is-addon"></i>{/if}</span>
                                {if __($second_level.description) != "_`$second_level_title`_menu_description"}{if $settings.Appearance.show_menu_descriptions == "Y"}<span class="hint">{__($second_level.description)}</span>{/if}{/if}</a>

                                {if $second_level.subitems}
                                    <ul class="dropdown-menu">
                                        {foreach from=$second_level.subitems key=subitem_title item=sm}
                                            <li class="{if $sm.active}active{/if} {if $sm.is_promo}cm-promo-popup{/if} {$second_level.attrs.class}" {menu_attrs attrs=$sm.attrs.main}><a href="{$sm.href|fn_url}" {menu_attrs attrs=$sm.attrs.href}>{__($subitem_title)}</a></li>
                                        {/foreach}
                                    </ul>
                                {/if}
                            </li>
                        {/foreach}
                    </ul>
                </li>
            {/foreach}
        {/if}
        </ul>
    <!--header_subnav--></div>
</div>

<script type="text/javascript">
    (function(_, $) {
        $('.navbar-right li').hover(function() {
            var pagePosition = $(".admin-content").offset();
            var adminContentWidth = 1240;

            if($(this).hasClass('dropdown-submenu')) {
                var elmPosition = $(this).find('.dropdown-menu').offset().left + $(this).find('.dropdown-menu').width();
                if((elmPosition - pagePosition.left) > adminContentWidth) {
                    $(this).find('.dropdown-menu').addClass('dropdown-menu-to-right');
                }
            }
        }, function() {
            $(this).find('.dropdown-menu').removeClass('dropdown-menu-to-right');
        });
    }(Tygh, Tygh.$));
</script>
