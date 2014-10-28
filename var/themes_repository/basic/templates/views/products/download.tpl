<div class="subheader">
    {if $product.ekey && $no_capture}
        <a href="{"orders.download?ekey=`$product.ekey`&product_id=`$product.product_id`"|fn_url}"></a>
    {/if}
        {$product.product nofilter}
    {if $product.ekey && $no_capture}</a>{/if}
    
    {if $no_capture && !$hide_order}
    &nbsp;(<a href="{"orders.details?order_id=`$product.order_id`"|fn_url}">{__("order")}# {$product.order_id}</a>)
    {/if}
</div>


<table class="table table-width">
    <thead>
        <tr>
            <th>
                {__("name")}
            </th>
            <th style="width: 20%">{__("size")}</th>
        </tr>
    </thead>
{if $product.files_tree}
    {foreach from=$product.files_tree.folders item="folder"}
        <tr>
            <td>
                <div style="padding-left: 5px;" class="hand">
                <input type="hidden" name="folder_{$folder.folder_id}" value="{$folder.folder_name}" />
                <div id="on_group_order_{$product.order_id}_folder_{$folder.folder_id}" class="cm-combination {if $expand_all} hidden{/if} icon-folder"> {$folder.folder_name} {if !$folder.files}<span class="mid-gray">({__("folder_is_empty")}){/if}</span></div>
                <div id="off_group_order_{$product.order_id}_folder_{$folder.folder_id}" class="cm-combination {if !$expand_all} hidden{/if} icon-folder-open"> {$folder.folder_name} {if !$folder.files}<span class="mid-gray">({__("folder_is_empty")}){/if}</span></div>
            </td>
            <td class="nowrap" style="width: 20%">
                {$folder.folder_size|number_format:0:"":" "}&nbsp;{__("bytes")}
            </td>
        </tr>

        <tbody id=group_order_{$product.order_id}_folder_{$folder.folder_id} {if !$expand_all} style="display: none;"{/if}>
        {foreach from=$folder.files item="file"}
            {include file="views/products/components/file_tree.tpl" product_file=$file level=1}
        {foreachelse}
            <tr>
                <td>
                    <div class="mid-gray" style="padding-left: 35px;">
                        {__("no_files")}
                    </div>
                </td>
                <td></td>
            </tr>
        {/foreach}
        </tbody>
    {/foreach}

    {if $product.files_tree.files}
        <tbody>
        {foreach from=$product.files_tree.files item="file"}
            {include file="views/products/components/file_tree.tpl" product_file=$file}
        {/foreach}
        </tbody>
    {/if}


{else}
    <tr>
        <td colspan="2"><p class="no-items">{__("no_items")}</p></td>
    </tr>
{/if}
</table>


{capture name="mainbox_title"}{__("download")}{/capture}