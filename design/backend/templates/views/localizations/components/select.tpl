{if !"ULTIMATE:FREE"|fn_allowed_for && $config.tweaks.disable_localizations == false}
	{assign var="data" value=$data_from|fn_explode_localizations}

	{if $localizations}
		{if !$no_div}
			<div class="control-group">
		    <label class="control-label" for="{$id}">{__("localization")}:</label>
            <div class="controls">
		{/if}
            {if !$disabled}<input type="hidden" name="{$data_name}" value="" />{/if}
            <select    name="{$data_name}[]" multiple="multiple" size="3" id="{$id|default:$data_name}" class="{if $disabled}elm-disabled{else}span6{/if}" {if $disabled}disabled="disabled"{/if}>
                {foreach from=$localizations item="loc"}
                <option    value="{$loc.localization_id}" {foreach from=$data item="p_loc"}{if $p_loc == $loc.localization_id}selected="selected"{/if}{/foreach}>{$loc.localization}</option>
                {/foreach}
            </select>
		{if !$no_div}
			{__("multiple_selectbox_notice")}
			</div>
			</div>
		{/if}
	{/if}
{/if}