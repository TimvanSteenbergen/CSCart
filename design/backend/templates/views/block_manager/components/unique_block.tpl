{if $block_data && !$block_data.disabled}

    {assign var="editable" value=true}
    {if $simple && ($show_for_location && $block_data.location != $show_for_location)}
        {assign var="editable" value=false}
    {/if}
    {assign var="block_content_id" value="unique_block_content_`$block_data.block_id`"}
    <div class="block base-block" id="block_{$block_data.block_id}">
        {strip}
            {if $editable && ($location == "all_pages" || $block_data.location != "all_pages") && !$block_data.properties.static_block}
            <div><a class="pull-right cm-confirm delete-block" href="{"block_manager.delete?selected_section=`$location`&block_id=`$block_data.block_id`&redirect_url=`$redirect_url`"|fn_url}"><i class="icon-trash" title="{__("delete")}"></i></a></div>
            {/if}
            <h4>
                <span>
                    {assign var="block_content_id" value="block_content_`$block_data.block_id`"}
                    <i id="on_{$block_content_id}" class="exicon-expand cm-combination cm-save-state{if $smarty.cookies.$block_content_id} hidden{/if}" ></i><i id="off_{$block_content_id}" class="exicon-collapse cm-combination cm-save-state{if !$smarty.cookies.$block_content_id} hidden{/if}"></i>
                    {$block_data.name}
                </span>
            </h4>
        {/strip}

        <div id="{$block_content_id}" class="block-container clear{if !$smarty.cookies.$block_content_id} hidden{/if}">
            <div class="block-content">
                <div>
                    {__("default_wrapeper")}: {$block_data.properties.wrapper}
                    {__("default_template")}: {$block_data.properties.template}
                </div>
                <div class="break">
                    {include file="common/object_group.tpl" content=$smarty.capture.update_block id="`$block_data.block_id`" no_table=true but_name="dispatch[block_manager.update_block]" href="block_manager.update_block?block_data[block_id]=`$block_data.block_id`&block_data[type]=`$block_data.type`&descr_sl=`$smarty.const.DESCR_SL`" header_text="{__("editing_block")}: `$block_data.name`" opener_ajax_class="cm-ajax" link_class="cm-ajax-force" picker_meta="cm-clear-content"}
                </div>
            </div>
        </div>

        <div class="block-bottom"><p class="no-margin">&nbsp;</p></div>
    </div>
{/if}