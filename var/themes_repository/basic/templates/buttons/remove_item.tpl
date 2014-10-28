{strip}
{if !$simple}
<a name="remove" id="{$item_id}" alt="{__("remove_this_item")}" title="{__("remove_this_item")}" class="button-icon icon-remove icon-remove-disable{if $only_delete == "Y"} hidden{/if}" ><i class="icon-cancel-circle"></i></a>
{/if}
<a name="remove_hidden" id="{$item_id}" alt="{__("remove_this_item")}" title="{__("remove_this_item")}"{if $but_onclick} onclick="{$but_onclick}"{/if} class="button-icon icon-remove {if !$simple && $only_delete != "Y"} hidden{/if}{if $but_class} {$but_class}{/if}" ><i class="icon-cancel-circle"></i></a>
{/strip}