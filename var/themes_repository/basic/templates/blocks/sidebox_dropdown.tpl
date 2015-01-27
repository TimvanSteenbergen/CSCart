{hook name="blocks:sidebox_dropdown"}{strip}
{assign var="foreach_name" value="item_`$iid`"}

{foreach from=$items item="item" name=$foreach_name}

{hook name="blocks:sidebox_dropdown_element"}

    <li class="{if $separated}b-border {/if}{if $item.$childs}dir{/if}{if $item.active || $item|fn_check_is_active_menu_item:$block.type} active{/if}">
    
        {if $item.$childs}
            <i class="icon-right-open"></i><i class="icon-left-open"></i>
            {hook name="blocks:sidebox_dropdown_childs"}
    
            <div class="hide-border">&nbsp;</div>
            <ul>
                {include file="blocks/sidebox_dropdown.tpl" items=$item.$childs separated=true submenu=true iid=$item.$item_id}
            </ul>

            {/hook}

        {/if}
        {assign var="item_url" value=$item|fn_form_dropdown_object_link:$block.type}
        <a{if $item_url} href="{$item_url}"{/if} {if $item.new_window}target="_blank"{/if}>{$item.$name}</a>
    </li>

{/hook}

{/foreach}
{/strip}{/hook}