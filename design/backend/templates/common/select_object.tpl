{if $items|sizeof > 1}

{if $style == "graphic"}
<div class="btn-group {$class}" {if $select_container_id}id="{$select_container_id}"{/if}>
    <a class="btn btn-text dropdown-toggle " id="sw_select_{$selected_id}_wrap_{$suffix}" data-toggle="dropdown">
        {if $display_icons == true}
            <i class="flag flag-{$items.$selected_id.country_code|lower}" data-ca-target-id="sw_select_{$selected_id}_wrap_{$suffix}"></i>
        {/if}
            {$items.$selected_id.$key_name}{if $items.$selected_id.symbol}&nbsp;({$items.$selected_id.symbol nofilter})
        {/if}
        <span class="caret"></span>
    </a>
        {if $key_name == 'company'}<input id="filter" class="input-text cm-filter" type="text" style="width: 85%"/>{/if}
        <ul class="dropdown-menu cm-select-list{if $display_icons == true} popup-icons{/if}">
            {foreach from=$items item=item key=id}
                <li><a name="{$id}" href="{"`$link_tpl``$id`"|fn_url}" {if  $target_id}class="cm-ajax" data-ca-target-id="{$target_id}"{/if}>{if $display_icons == true}<i class="flag flag-{$item.country_code|lower}"></i>{/if}{$item.$key_name}{if $item.symbol}&nbsp;({$item.symbol nofilter}){/if}</a></li>
            {/foreach}
            {if $extra}{$extra nofilter}{/if}
        </ul>
</div>
{elseif $style == "dropdown"}
    <li class="dropdown dropdown-top-menu-item {$class}" {if $select_container_id}id="{$select_container_id}"{/if}>
        <a class="dropdown-toggle cm-combination" data-toggle="dropdown" id="sw_select_{$selected_id}_wrap_{$suffix}">
            {if $key_selected}
                {if $items.$selected_id.symbol}
                    {$items.$selected_id.symbol nofilter}
                {else}
                    {$items.$selected_id.$key_selected|upper nofilter}
                {/if}
            {else}
                {$items.$selected_id.$key_name nofilter}
            {/if}
            <b class="caret"></b>
        </a>
        <ul class="dropdown-menu cm-select-list pull-right">
            {foreach from=$items item=item key=id}
                <li {if $id == $selected_id}class="active"{/if}>
                    <a name="{$id}" href="{"`$link_tpl``$id`"|fn_url}">
                        {if $display_icons == true}
                            <i class="flag flag-{$item.country_code|lower}"></i>
                        {/if}
                        {$item.$key_name}{if $item.symbol}&nbsp;({$item.symbol nofilter}){/if}
                    </a>
                </li>
            {/foreach}
        </ul>
    </li>
{elseif $style == "field"}
<div class="cm-popup-box btn-group {if $class}{$class}{/if}">
    {if !$selected_key}
    {assign var="selected_key" value=$items|key}
    {/if}
    {if !$selected_name}
    {assign var="selected_name" value=$items[$selected_key]}
    {/if}
    <input type="hidden" name="{$select_container_name}" {if $select_container_id}id="{$select_container_id}"{/if} value="{$selected_key}" />
    <a id="sw_{$select_container_name}" class="dropdown-toggle btn-text btn" data-toggle="dropdown">
    {$selected_name}
    <span class="caret"></span>
    </a>
    <ul class="dropdown-menu cm-select">
        {foreach from=$items item="value" key="key"}
            <li {if $selected_key == $key}class="disabled"{/if}><a class="{if $selected_key == $key}active {/if}cm-select-option" data-ca-list-item="{$key}">{$value nofilter}</a></li>
        {/foreach}
    </ul>
</div>
{/if}

{/if}