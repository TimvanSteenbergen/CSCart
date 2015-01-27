{assign var="l" value="promotion_bonus_`$bonus_data.bonus`"}

<div class="option clearfix cm-row-item">
    <div class="pull-right">
        <a class="hand icon-trash cm-delete-row cm-tooltip" name="remove" id="{$item_id}" title="{__("remove")}"></a>
    </div>
    <label>{__($l)}:&nbsp;</label>
    <input type="hidden" name="{$prefix}[bonus]" value="{$bonus_data.bonus}" />

    {if $schema.bonuses[$bonus_data.bonus].type == "input"}
    <input class="input-text" type="text" name="{$prefix}[value]" value="{$bonus_data.value}" />

    {elseif $schema.bonuses[$bonus_data.bonus].type == "hidden"}
    <input type="hidden" name="{$prefix}[value]" value="Y" />{__("yes")}

    {elseif $schema.bonuses[$bonus_data.bonus].type == "checkbox"}
    <input class="checkbox" type="checkbox" name="{$prefix}[value]" value="Y" {if $bonus_data.value == "Y"}checked="checked"{/if} />

    {elseif $schema.bonuses[$bonus_data.bonus].type == "select"}
    {assign var="_items" value=$schema.bonuses[$bonus_data.bonus].variants|default:$schema.bonuses[$bonus_data.bonus].variants_function|fn_get_promotion_variants}

    {if $_items}
    <select name="{$prefix}[value]">
        {foreach from=$_items key="_k" item="v"}
            <option value="{$_k}" {if $_k == $bonus_data.value}selected="selected"{/if}>{if $schema.bonuses[$bonus_data.bonus].variants_function}{$v}{else}{__($v)}{/if}</option>
        {/foreach}
    </select>
    {else}
    <input type="hidden" name="{$prefix}[value]" value="" />
    <p>{__("no_data")}</p>
    {/if}

    {elseif $schema.bonuses[$bonus_data.bonus].type == "picker"}
        {include_ext file=$schema.bonuses[$bonus_data.bonus].picker_props.picker data_id="objects_`$elm_id`" input_name="`$prefix`[value]" item_ids=$bonus_data.value params_array=$schema.bonuses[$bonus_data.bonus].picker_props.params owner_company_id=$promotion_data.company_id but_meta='pull-left'}
    {/if}

    {if $schema.bonuses[$bonus_data.bonus].discount_bonuses}
        <select name="{$prefix}[discount_bonus]">
            {foreach from=$schema.bonuses[$bonus_data.bonus].discount_bonuses item="v"}
                <option value="{$v}" {if $v == $bonus_data.discount_bonus}selected="selected"{/if}>{__($v)}</option>
            {/foreach}
        </select>

        <input class="input-medium cm-numeric" data-a-dec="." data-a-sep="" data-a-sign="" type="text" name="{$prefix}[discount_value]" value="{$bonus_data.discount_value}" />
        <script type="text/javascript">
            $('.cm-numeric').autoNumeric('init');
        </script>
    {/if}
</div>