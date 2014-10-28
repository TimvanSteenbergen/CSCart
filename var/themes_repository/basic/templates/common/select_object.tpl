{assign var="language_text" value=$text|default:__("select_descr_lang")}

{if $style == "graphic"}
    {if $text}{$text}:{/if}
    
    {if $display_icons == true}
        <i class="flag flag-{$items.$selected_id.country_code|lower} cm-external-click" data-ca-external-click-id="sw_select_{$selected_id}_wrap_{$suffix}" ></i>
    {/if}
    
    <a class="select-link cm-combination" id="sw_select_{$selected_id}_wrap_{$suffix}"><span>{$items.$selected_id.$key_name}{if $items.$selected_id.symbol} ({$items.$selected_id.symbol nofilter}){/if}</span><i class="icon-down-micro"></i></a>

    <div id="select_{$selected_id}_wrap_{$suffix}" class="select-popup cm-popup-box cm-smart-position hidden">
        <ul class="cm-select-list select-list flags">
            {foreach from=$items item=item key=id}
                <li><a rel="nofollow" href="{"`$link_tpl``$id`"|fn_url}" class="{if $display_icons == true}item-link clearfix{/if} {if $selected_id == $id}active{/if} {if $suffix == "live_editor_box"}cm-lang-link{/if}" {if $display_icons == true}data-ca-country-code="{$item.country_code|lower}"{/if} data-ca-name="{$id}">
                    {if $display_icons == true}
                        <i class="flag flag-{$item.country_code|lower}"></i>
                    {/if}
                    {$item.$key_name nofilter}{if $item.symbol} ({$item.symbol nofilter}){/if}</a></li>
            {/foreach}
        </ul>
    </div>
{else}
    {if $text}<label for="id_{$var_name}">{$text}:</label>{/if}
    <select id="id_{$var_name}" name="{$var_name}" onchange="Tygh.$.redirect(this.value);" class="valign">
        {foreach from=$items item=item key=id}
            <option value="{"`$link_tpl``$id`"|fn_url}" {if $id == $selected_id}selected="selected"{/if}>{$item.$key_name nofilter}</option>
        {/foreach}
    </select>
{/if}
