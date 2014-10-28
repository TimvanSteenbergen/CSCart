{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="product_options_form" enctype="multipart/form-data" class="cm-disable-empty-files form-horizontal form-edit">
<input type="hidden" name="fake" value="1" />
<input type="hidden" name="product_id" value="{$product_id}" />

{include file="common/pagination.tpl" save_current_page=true save_current_url=true}
{if $inventory}
<table  class="table" width="100%">
<thead>
<tr>
    <th class="left" width="1%">{include file="common/check_items.tpl"}</th>
    <th>{__("combination")}</th>
    <th>&nbsp;</th>
</tr>
</thead>
{foreach from=$inventory item="i"}
<tr valign="top">
    <td class="center"><input type="checkbox" name="combination_hashes[]" value="{$i.combination_hash}" class=" cm-item" /></td>
    <td>
        {foreach from=$i.combination item="c" key="k"}
            <div class="control-group">
                <label class="control-label">{$product_options.$k.option_name}</label>
                <div class="controls">
                <span class="shift-input">
                {if $product_options.$k.option_type == "C"}
                    [{if $product_options.$k.variants.$c.position == "1"}{__("yes")}{else}{__("no")}{/if}]
                {else}
                    {$product_options.$k.variants.$c.variant_name}
                {/if}
                </span>
                </div>
            </div>
        {/foreach}

        <div class="control-group">
            <label class="control-label" for="inventory_{$i.combination_hash}_product_code">{__("sku")}</label>
            <div class="controls">
            <input type="text" id="inventory_{$i.combination_hash}_product_code" name="inventory[{$i.combination_hash}][product_code]" size="16" maxlength="32" value="{$i.product_code}" />
            </div>
        </div>

        {if $product_inventory == "O"}
        <div class="control-group">
            <label class="control-label" for="inventory_{$i.combination_hash}_quantity">{__("quantity")}</label>
            <div class="controls">
            <input type="text" id="inventory_{$i.combination_hash}_quantity" name="inventory[{$i.combination_hash}][amount]" size="10" value="{$i.amount}" />
            </div>
        </div>
        {else}
            <input type="hidden" name="inventory[{$i.combination_hash}][amount]" size="10" value="{$i.amount}" />
        {/if}

        <div class="control-group">
            <label class="control-label" for="inventory_{$i.combination_hash}_position">{__("position")}</label>
            <div class="controls">
            <input type="text" id="inventory_{$i.combination_hash}_position" name="inventory[{$i.combination_hash}][position]" size="3" value="{$i.position}" />
            </div>
        </div>

        {hook name="product_options:inventory_item"}{/hook}

        {include file="common/attach_images.tpl" image_name="combinations" image_object_type="product_option" image_pair=$i.image_pairs image_object_id=$i.combination_hash image_key=$i.combination_hash icon_title=__("additional__option_thumbnail") no_thumbnail=true}
    </td>
    <td class="nowrap">
        <div class="hidden-tools">
            {capture name="tools_list"}
                <li>{btn type="list" class="cm-confirm" text=__("delete") href="product_options.delete_combination?combination_hash=`$i.combination_hash`&product_id=`$product_id`"}</li>
            {/capture}
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl"}

</form>

{capture name="add_new_combination"}
<form action="{""|fn_url}" method="post" name="new_combination_form">
<input type="hidden" name="fake" value="1" />
<input type="hidden" name="product_id" value="{$product_id}" />

<table class="table table-middle">
<thead>
<tr class="cm-first-sibling">
    <th>{__("combination")}</th>
    {if $product_inventory == "O"}
    <th>{__("in_stock")}</th>
    {/if}
    <th>&nbsp;</th>
</tr>
</thead>
<tr id="box_new_item">
    <td>
{hook name="product_options:new_inventory_item"}
<table>
{foreach from=$product_options item="option" name="add_inv_fe"}
<tr class="no-border">
    <td>{$option.option_name}</td>
    <td>{if $option.option_type == "C"}
            <select name="add_options_combination[0][{$option.option_id}]">
                {foreach from=$option.variants item="variant"}
                <option value="{$variant.variant_id}">{if $variant.position == 0}{__("no")}{else}{__("yes")}{/if}</option>
                {/foreach}
            </select>
        {else}
            <select name="add_options_combination[0][{$option.option_id}]">
                {foreach from=$option.variants item="variant"}
                <option value="{$variant.variant_id}">{$variant.variant_name}</option>
                {/foreach}
            </select>
        {/if}
    </td>
</tr>
{/foreach}
</table>
{/hook}
</td>
    {if $product_inventory != "O"}
        <input type="hidden" name="add_inventory[0][amount]" value="" />
    {/if}
    {if $product_inventory == "O"}
    <td valign="top"><input type="text" name="add_inventory[0][amount]" size="10" value="1" class="input-text-short inventory" /></td>
    {/if}
    <td valign="top" class="right">
        <div class="hidden-tools">
            {include file="buttons/multiple_buttons.tpl" item_id="new_item"}
        </div>
    </td>
</tr>
</table>

<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_name="dispatch[product_options.add_combinations]" cancel_action="close"}
</div>

</form>
{/capture}
{/capture}

{capture name="adv_buttons"}
    {include file="common/popupbox.tpl" id="add_new_combination" text=__("new_combination") title=__("add_combination") content=$smarty.capture.add_new_combination act="general" icon="icon-plus"}
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        <li>{btn type="list" text=__("rebuild_combinations") href="product_options.rebuild_combinations?product_id=`$product_id`"}</li>
        {if $inventory}
            <li class="divider"></li>
            <li>{btn type="delete_selected" dispatch="dispatch[product_options.m_delete_combinations]" form="product_options_form"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}

    {if $inventory}
        {include file="buttons/save.tpl" but_name="dispatch[product_options.update_combinations]" but_role="submit-link" but_target_form="product_options_form"}
    {/if}
{/capture}

{include file="common/mainbox.tpl" title=__("inventory") content=$smarty.capture.mainbox adv_buttons=$smarty.capture.adv_buttons buttons=$smarty.capture.buttons}