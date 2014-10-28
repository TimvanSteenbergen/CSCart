{foreach name="existing_blocks" from=$unique_blocks item="block"}
    {if $block_types[$block.type]}
        <div class="select-block cm-add-block bm-action-existing-block {if $manage == "Y"}bm-manage{/if} {if $block.single_for_location}bm-block-single-for-location{/if}">
            <input type="hidden" name="block_id" value="{$block.block_id}" />
            <input type="hidden" name="grid_id" value="{$grid_id|default:"0"}" />
            <input type="hidden" name="type" value="{$block.type}" />
            <a class="icon-remove-circle cm-tooltip cm-remove-block" title="{__("delete_block")}"></a>
            <div class="select-block-box">
                <div class="bmicon-{$block.type|replace:"_":"-"}"></div>
            </div>
            <div class="select-block-description">
                <strong title="{$block.name}">{$block.name|truncate:25:"&hellip;":true nofilter}</strong>
                {assign var="block_description" value="block_`$block.type`_description"}
                <p>{__($block_description)}</p>
            </div>
        </div>
    {/if}
{/foreach}
