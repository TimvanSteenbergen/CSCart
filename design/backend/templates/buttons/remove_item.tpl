{if !$simple}
	<a class="icon-remove cm-opacity cm-tooltip {if $only_delete == "Y"} hidden{/if}" name="remove" id="{$item_id}" title="{__("remove")}"></a>
{/if}
<a name="remove_hidden" id="{$item_id}" class="icon-remove cm-tooltip {if !$simple && $only_delete != "Y"} hidden{/if}{if $but_class} {$but_class}{/if}" title="{__("remove")}" {if $but_onclick} onclick="{$but_onclick}"{/if}></a>