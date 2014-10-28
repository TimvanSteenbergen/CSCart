{script src="js/tygh/node_cloning.js"}

{assign var="tag_level" value=$tag_level|default:"1"}
{strip}
{if $only_delete != "Y"}
    {if !$hide_add}
    	{include file="buttons/add_empty_item.tpl" but_onclick="Tygh.$('#box_' + this.id).cloneNode($tag_level); `$on_add`" item_id=$item_id}
    {/if}

    {if !$hide_clone}
    	{include file="buttons/clone_item.tpl" but_onclick="Tygh.$('#box_' + this.id).cloneNode($tag_level, true);" item_id=$item_id}
	{/if}
{/if}

{include file="buttons/remove_item.tpl" only_delete=$only_delete but_class="cm-delete-row"}
{/strip}