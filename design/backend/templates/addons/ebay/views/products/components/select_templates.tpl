{if $ebay_templates}
    <select name="ebay_template_id">
    {foreach from=$ebay_templates item="template"}
        <option value="{$template.template_id}" {if $template.is_default == 'Y'}selected="selected"{/if}>{$template.name}</option>
    {/foreach}
    </select>
{/if}