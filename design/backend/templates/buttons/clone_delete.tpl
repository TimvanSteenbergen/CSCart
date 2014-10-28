{if $href_clone}
<a class="clone-item cm-tooltip" title="{__("remove")}" href="{$href_clone|fn_url}"><i class="icon-remove"></i></a>
{/if}
<a class="delete-item cm-tooltip {if !$no_confirm}cm-confirm{/if}{if $microformats} {$microformats}{/if}" title="{__("remove")}" {if $href_delete}href="{$href_delete|fn_url}"{/if} {if $delete_target_id}data-ca-target-id="{$delete_target_id}"{/if}><i class="icon-remove"></i></a>