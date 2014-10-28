{if $option.values}
    <label for="{$html_id}"{if $option.required} class="cm-required"{/if}>{if $option.option_name}{__($option.option_name)}{else}{__($name)}{/if}:</label>

    <select id="{$html_id}" name="{$html_name}">
    {foreach from=$option.values key="k" item="v"}
        <option value="{$k}" {if $value && $value == $k || !$value && $option.default_value == $k}selected="selected"{/if}>{$v}</option>
    {/foreach}
    </select>    
{else}
    {__("no_menus")}
{/if}
<div>
    <a href="{"menus.manage"|fn_url}">{__("manage_menus")}</a>
</div>