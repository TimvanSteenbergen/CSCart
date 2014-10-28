{** block-description:text_links **}

{if $block.properties.show_items_in_line == 'Y'}
    {assign var="inline" value=true}
{/if}

{if $items}
    <ul class="ty-text-links{if $inline} ty-text-links_show_inline{/if}">
        {foreach from=$items item="menu"}
            <li class="ty-text-links__item ty-level-{$menu.level|default:0}{if $menu.active} ty-text-links__active{/if}">
                <a class="ty-text-links__a" {if $menu.href}href="{$menu.href|fn_url}"{/if}>{$menu.item}</a> 
                {if $menu.subitems}
                    {include file="blocks/menu/text_links.tpl" items=$menu.subitems}
                {/if}
            </li>
        {/foreach}
    </ul>
{/if}