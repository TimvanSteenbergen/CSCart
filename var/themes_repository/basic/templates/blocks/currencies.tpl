<div id="currencies_{$block.block_id}">

{if $currencies && $currencies|count > 1}
    {if $dropdown_limit > 0 && $currencies|count <= $dropdown_limit}
        <div class="select-wrap currencies">
            {if $text}{$text}:{/if}
            {foreach from=$currencies key=code item=currency}
                <a href="{$config.current_url|fn_link_attach:"currency=`$code`"|fn_url}" {if $secondary_currency == $code}class="active-element"{/if}>{if $format == "name"}{$currency.description}&nbsp;({$currency.symbol nofilter}){else}{$currency.symbol nofilter}{/if}</a>
            {/foreach}
        </div>

    {else}
        {if $format == "name"}
            {assign var="key_name" value="description"}
        {else}
            {assign var="key_name" value=""}
        {/if}
        <div class="select-wrap">{include file="common/select_object.tpl" style="graphic" suffix="currency" link_tpl=$config.current_url|fn_link_attach:"currency=" items=$currencies selected_id=$secondary_currency display_icons=false key_name=$key_name}</div>
    {/if}
{/if}

<!--currencies_{$block.block_id}--></div>
