{hook name="blocks:sidebox_dropdown"}{strip}
{assign var="foreach_name" value="item_`$iid`"}

{foreach from=$items item="item" name=$foreach_name}
{hook name="blocks:sidebox_dropdown_element"}

    <li class="ty-menu__item cm-menu-item-responsive {if $item.$childs}dropdown-vertical__dir{/if}{if $item.active || $item|fn_check_is_active_menu_item:$block.type} ty-menu__item-active{/if} menu-level-{$level}">
        {if $item.$childs}
            <div class="ty-menu__item-toggle visible-tablet visible-phone cm-responsive-menu-toggle">
                <i class="ty-icon-down-open"></i>
            </div>
            <div class="hidden-tablet hidden-phone">
                <i class="ty-icon-right-open"></i>
                <i class="ty-icon-left-open"></i>
            </div>
        {/if}

        {assign var="item_url" value=$item|fn_form_dropdown_object_link:$block.type}
        <div class="ty-menu__submenu-item-header">
            <a{if $item_url} href="{$item_url}"{/if} {if $item.new_window}target="_blank"{/if} class="ty-menu__item-link">{$item.$name}</a>
        </div>

        {if $item.$childs}
            {hook name="blocks:sidebox_dropdown_childs"}
            <div class="ty-menu__submenu">
                <ul class="ty-menu__submenu-items cm-responsive-menu-submenu">
                    {include file="blocks/sidebox_dropdown.tpl" items=$item.$childs separated=true submenu=true iid=$item.$item_id level=$level+1}
                </ul>
            </div>
            {/hook}
        {/if}
    </li>
{/hook}

{/foreach}
{/strip}{/hook}