{if $capture}
{capture name="carrier_field"}
{/if}

<select {if $id}id="{$id}"{/if} name="{$name}" {if $meta}class="{$meta}"{/if}>
    <option value="">--</option>
    {hook name="carriers:list"}
    {foreach from=$carriers item="code"}
    	<option value="{$code}" {if $carrier == "{$code}"}{$carrier_name = __("carrier_`$code`")}selected="selected"{/if}>{__("carrier_`$code`")}</option>
    {/foreach}
    {/hook}
</select>
{if $capture}
{/capture}

{capture name="carrier_name"}
{$carrier_name}
{/capture}
{/if}