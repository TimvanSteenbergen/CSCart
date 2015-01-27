{if $spec_settings && (($spec_settings|count > 1 && $spec_settings.settings) || (!$spec_settings.settings))}
<div id="toggle_{$s_set_id}">
    <div class="specific-settings pull-left" id="container_{$s_set_id}">
        <a id="sw_additional_{$s_set_id}" class="open cm-combination">
            {__("specific_settings")}
            <span class="combo-arrow"></span>
        </a>
    </div>

    <div class="hidden" id="additional_{$s_set_id}">
    {foreach from=$spec_settings key="set_name" item="_option"}
        {include file="views/block_manager/components/setting_element.tpl" set_name=$set_name option=$_option block=$block set_id=$s_set_id}
    {/foreach}
    </div>
<!--toggle_{$s_set_id}--></div>
{else}
<div id="toggle_{$s_set_id}"><!--toggle_{$s_set_id}--></div>
{/if}