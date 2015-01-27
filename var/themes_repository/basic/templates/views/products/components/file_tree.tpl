{math equation="x*30+5" x=$level|default:"0" assign="shift"}

<tr>
    <td><div style="padding-left: {$shift}px;">
        <i class="icon-file"></i>
        {if $product_file.ekey && ($product_file.activation_type !== "M" || $product_file.active == "Y") && $product_file.edp_info && (!$product_file.max_downloads || $product_file.downloads < $product_file.max_downloads)}<a href="{"orders.get_file?ekey=`$product_file.ekey`&product_id=`$product.product_id`&file_id=`$product_file.file_id`"|fn_url}" class="download-link cm-no-ajax">{$product_file.file_name}</a><i class="icon-download"></i>{else}{$product_file.file_name}{/if}
        {if $product_file.activation_type == "M" && $product_file.active == "N"}<p>{__("notice")}: {__("not_active_file_notice")}</p>{elseif $product_file.max_downloads && $product_file.downloads >= $product_file.max_downloads}<p>{__("notice")}: {__("file_download_limit_exceeded", ["[limit]" => $product_file.max_downloads])}</p>{elseif !$product_file.edp_info}<p>{__("download_link_expired")}</p>{elseif !$product_file.ekey}<p>{if $product_file.activation_type == "P"}{__("file_avail_after_payment")}{else}{__("download_link_expired")}{/if}</p>{/if}

    </div></td>
    <td class="nowrap" style="width: 20%">
        {$product_file.file_size|number_format:0:"":" "}&nbsp;{__("bytes")}
    </td>
</tr>
