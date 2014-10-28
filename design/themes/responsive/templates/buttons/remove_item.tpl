{strip}
{if !$simple}
<a name="remove" id="{$item_id}" alt="{__("remove_this_item")}" title="{__("remove_this_item")}" class="button-icon ty-icon-remove ty-icon-remove-disable{if $only_delete == "Y"} hidden{/if}" ><i class="ty-icon-cancel-circle"></i></a>
{/if}
<a name="remove_hidden" id="{$item_id}" alt="{__("remove_this_item")}" title="{__("remove_this_item")}"{if $but_onclick} onclick="{$but_onclick}"{/if} class="button-icon ty-icon-remove {if !$simple && $only_delete != "Y"} hidden{/if}{if $but_class} {$but_class}{/if}" ><i class="ty-icon-cancel-circle"></i></a>
{/strip}