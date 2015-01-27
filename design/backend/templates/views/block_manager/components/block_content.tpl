{strip}
    {if $block_scheme.content}
        {if $block_scheme.multilanguage && $block.block_id && $languages|count > 1}
            <div class="control-group">
                <div class="controls {if $editable}cm-no-hide-input{/if}">
                    <label class="checkbox">
                        <input type="hidden" name="block_data[apply_to_all_langs]" value="N" />
                        <input type="checkbox" id="apply_to_all_languages_{$name}" name="block_data[apply_to_all_langs]" value="Y" />{__("apply_to_all_languages")}
                    </label>
                </div>
            </div>
        {/if}

        {foreach from=$block_scheme.content item=setting_data key=name}
            {if $setting_data.type != "function"}
                {include file="views/block_manager/components/setting_element.tpl" option=$setting_data name=$name block=$block html_id=$html_id|default:"block_`$block.block_id`_content_`$name`" html_name="block_data[content][`$name`]" editable=$editable value=$block.content.$name}
            {/if}
        {/foreach}
    {/if}
{/strip}