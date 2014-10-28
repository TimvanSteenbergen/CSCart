{if $item.update_for_all && $settings.Stores.default_state_update_for_all == 'not_active' && !$runtime.simple_ultimate}
    {assign var="disable_input" value=true}
{/if}

{if $parent_item}
<script type="text/javascript">
(function($, _) {
    $('#{$parent_item_html_id}').on('click', function() {
        $('#container_{$html_id}').toggle();
    });
}(Tygh.$, Tygh));
</script>
{/if}

{* Settings without label*}
{if $item.type == "O"}
    <div>{$item.info nofilter}</div>
{elseif $item.type == "E"}
    <div>{include file="addons/`$smarty.request.addon`/settings/`$item.value`"}</div>
{elseif $item.type == "Z"}
    <div>{include file="addons/`$smarty.request.addon`/settings/`$item.value`" skip_addon_check=true}</div>
{elseif $item.type == "H"}
    {if $smarty.capture.header_first == 'true'}
            </fieldset>
        </div>
    {/if}
    {capture name="header_first"}true{/capture}
    {include file="common/subheader.tpl" title=$item.description target="#collapsable_`$html_id`"}
    <div id="collapsable_{$html_id}" class="in collapse">
        <fieldset>
{elseif $item.type != "D" && $item.type != "B"}
    {* Settings with label*}
    <div id="container_{$html_id}" class="control-group{if $class} {$class}{/if} {$item.section_name} {if $parent_item && $parent_item.value != "Y"}hidden{/if}">
        <label for="{$html_id}" class="control-label {if $highlight && $item.name|in_array:$highlight}highlight{/if}" >{$item.description nofilter}{if $item.tooltip}{include file="common/tooltip.tpl" tooltip=$item.tooltip}{/if}:
        </label>

        <div class="controls">
            {if $item.type == "P"}
                <input id="{$html_id}" type="password" name="{$html_name}" size="30" value="{$item.value}" class="input-text" {if $disable_input}disabled="disabled"{/if} />
            {elseif $item.type == "T"}
                <textarea id="{$html_id}" name="{$html_name}" rows="5" cols="19" class="input-large" {if $disable_input}disabled="disabled"{/if}>{$item.value}</textarea>
            {elseif $item.type == "C"}
                <input type="hidden" name="{$html_name}" value="N" {if $disable_input}disabled="disabled"{/if} />
                <input id="{$html_id}" type="checkbox" name="{$html_name}" value="Y" {if $item.value == "Y"}checked="checked"{/if}{if $disable_input} disabled="disabled"{/if} />
            {elseif $item.type == "S"}
                <select id="{$html_id}" name="{$html_name}" {if $disable_input}disabled="disabled"{/if}>
                    {foreach from=$item.variants item=v key=k}
                        <option value="{$k}" {if $item.value == $k}selected="selected"{/if}>{$v}</option>
                    {/foreach}
                </select>
            {elseif $item.type == "R"}
                <div class="select-field" id="{$html_id}">
                {foreach from=$item.variants item=v key=k}
                    <label for="variant_{$item.name}_{$k}" class="radio">
                        <input type="radio" name="{$html_name}" value="{$k}" {if $item.value == $k}checked="checked"{/if} id="variant_{$item.name}_{$k}" {if $disable_input}disabled="disabled"{/if}> {$v}
                    </label>
                {foreachelse}
                    {__("no_items")}
                {/foreach}
                </div>
            {elseif $item.type == "M"}
                <select id="{$html_id}" name="{$html_name}[]" multiple="multiple" {if $disable_input}disabled="disabled"{/if}>
                {foreach from=$item.variants item=v key="k"}
                <option value="{$k}" {if $item.value.$k == "Y"}selected="selected"{/if}>{$v}</option>
                {/foreach}
                </select>
                {__("multiple_selectbox_notice")}
            {elseif $item.type == "N"}
                <div class="select-field" id="{$html_id}">
                    <input type="hidden" name="{$html_name}" value="N" {if $disable_input}disabled="disabled"{/if} />
                    {foreach from=$item.variants item=v key="k"}
                        <label for="variant_{$item.name}_{$k}" class="checkbox">
                            <input type="checkbox" name="{$html_name}[]" id="variant_{$item.name}_{$k}" value="{$k}" {if $item.value.$k == "Y"}checked="checked"{/if} {if $disable_input}disabled="disabled"{/if}>
                            {$v}
                        </label>
                    {foreachelse}
                        {__("no_items")}
                    {/foreach}
                </div>
            {elseif $item.type == "X"}
                <select class="cm-country cm-location-billing" id="{$html_id}" name="{$html_name}" {if $disable_input}disabled="disabled"{/if}>
                    <option value="">- {__("select_country")} -</option>
                    {assign var="countries" value=""|fn_get_simple_countries}
                    {foreach from=$countries item="country" key="code"}
                        <option value="{$code}" {if $code == $item.value}selected="selected"{/if}>{$country}</option>
                    {/foreach}
                </select>
            {elseif $item.type == "W"}
                <select class="cm-state cm-location-billing" id="{$html_id}" name="{$html_name}" {if $disable_input}disabled="disabled"{/if}>
                    <option value="">- {__("select_state")} -</option>
                </select>
                <input type="text" id="{$html_id}_d" name="{$html_name}" value="{$item.value}" size="32" maxlength="64" disabled="disabled" class="cm-state cm-location-billing hidden" />
            {elseif $item.type == "F"}
                <div class="input-append">
                    <input id="file_{$html_id}" type="text" name="{$html_name}" value="{$item.value}" size="30" {if $disable_input}disabled="disabled"{/if}>
                    <button id="{$html_id}" type="button" class="btn" onclick="Tygh.fileuploader.init('box_server_upload', this.id);" {if $disable_input}disabled="disabled"{/if}>{__("browse")}</button>
                </div>
            {elseif $item.type == "G"}
                <div id="{$html_id}">
                    {foreach from=$item.variants item=v key="k"}
                        <label for="variant_{$item.name}_{$k}" class="checkbox">
                            <input type="checkbox" class="cm-combo-checkbox" id="variant_{$item.name}_{$k}" name="{$html_name}[]" value="{$k}" {if $item.value.$k == "Y"}checked="checked"{/if} {if $disable_input}disabled="disabled"{/if}>
                            {$v}
                        </label>
                    {foreachelse}
                        {__("no_items")}
                    {/foreach}
                </div>
            {elseif $item.type == "K"}
                <select id="{$html_id}" name="{$html_name}" class="cm-combo-select" {if $disable_input}disabled="disabled"{/if}>
                    {foreach from=$item.variants item=v key=k}
                        <option value="{$k}" {if $item.value == $k}selected="selected"{/if}>{$v}</option>
                    {/foreach}
                </select>
            {else}
                <input id="{$html_id}" type="text" name="{$html_name}" size="30" value="{$item.value}" class="{if $item.type == "U"} cm-value-integer{/if}" {if $disable_input}disabled="disabled"{/if} />
            {/if}
            <div class="right update-for-all">
                {include file="buttons/update_for_all.tpl" display=$item.update_for_all object_id=$item.object_id name="update_all_vendors[`$item.object_id`]" hide_element=$html_id}
            </div>
        </div>
    </div>
{elseif $item.type == "B"}
    <div class="control-group">
        {include file="common/selectable_box.tpl" addon=$section name=$html_name id=$html_id fields=$item.variants selected_fields=$item.value}
    </div>
{/if}
{if $total == $index && $smarty.capture.header_first == 'true'}
    </fieldset>
        </div>
{/if}