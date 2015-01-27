{if $view_tools}
<div class="product-switcher">
    <a class="switcher-icon left {if !$view_tools.prev_id}disabled{elseif $quick_view}cm-dialog-opener cm-dialog-auto-size{/if}" {if $view_tools.prev_id}href="{$view_tools.prev_url|fn_query_remove:"skip_result_ids_check"|fn_url}" title="{if $view_tools.links_label}{$view_tools.links_label}{if $view_tools.show_item_id} #{$view_tools.prev_id}{/if}{else}{__("prev_page")}{/if}" {if $quick_view}data-ca-view-id="{$view_tools.prev_id}" data-ca-target-id="product_quick_view" data-ca-dialog-title="{__("quick_product_viewer")}" rel="nofollow"{/if}{/if}><i class="icon-left-circle"></i></a>
        <span class="switcher-selected-product">{$view_tools.current}</span>
        <span>{__("of")}</span>
        <span class="switcher-total">{$view_tools.total}</span>
    <a class="switcher-icon right {if !$view_tools.next_id}disabled{elseif $quick_view}cm-dialog-opener cm-dialog-auto-size{/if}" {if $view_tools.next_id}href="{$view_tools.next_url|fn_query_remove:"skip_result_ids_check"|fn_url}" title="{if $view_tools.links_label}{$view_tools.links_label}{if $view_tools.show_item_id} #{$view_tools.next_id}{/if}{else}{__("next")}{/if}" {if $quick_view}data-ca-view-id="{$view_tools.next_id}" data-ca-target-id="product_quick_view" data-ca-dialog-title="{__("quick_product_viewer")}" rel="nofollow"{/if}{/if}><i class="icon-right-circle"></i></a>
</div>
{/if}
