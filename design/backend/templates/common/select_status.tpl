{if $display == "select" || $display == "popup"}
{assign var="selected_st" value=$obj.status|default:"A"}
{capture name="status_title"}
    {if $selected_st == "A"}
        {__("active")}
    {elseif $selected_st == "H"}
        {__("hidden")}
    {elseif $selected_st == "D"}
        {__("disabled")}
    {/if}
{/capture}
{/if}
{if $display == "select"}
<select class="input-small {if $meta}{$meta}{/if}" name="{$input_name}" {if $input_id}id="{$input_id}"{/if}>
    <option value="A" {if $obj.status == "A"}selected="selected"{/if}>{__("active")}</option>
    {if $hidden}
    <option value="H" {if $obj.status == "H"}selected="selected"{/if}>{__("hidden")}</option>
    {/if}
    <option value="D" {if $obj.status == "D"}selected="selected"{/if}>{__("disabled")}</option>
</select>
{elseif $display == "popup"}
<input {if $meta}class="{$meta}"{/if} type="hidden" name="{$input_name}" id="{$input_id|default:$input_name}" value="{$selected_st}" />
<div class="cm-popup-box btn-group dropleft">
    <a id="sw_{$input_name}" class="dropdown-toggle btn-text" data-toggle="dropdown">
    {$smarty.capture.status_title nofilter}
    <span class="caret"></span>
    </a>
    <ul class="dropdown-menu cm-select">
        {assign var="items_status" value=$status|fn_get_default_statuses:$hidden}
        {foreach from=$items_status item="val" key="st"}
            <li {if $selected_st == $st}class="disabled"{/if}><a class="status-link-{$st|lower} {if $selected_st == $st}active{/if}"  onclick="return fn_check_object_status(this, '{$st|lower}', '{if $statuses}{$statuses[$st].color|default:''}{/if}');" data-ca-result-id="{$input_id|default:$input_name}">{$val}</a></li>
        {/foreach}
    </ul>
</div>
{if !$smarty.capture.avail_box}
    {script src="js/tygh/select_popup.js"}
    {capture name="avail_box"}Y{/capture}
{/if}
{elseif $display == "text"}
<div class="control-group">
    <label class="control-label cm-required">{__("status")}</label>
    <div class="controls">
    <span>
    {$smarty.capture.status_title nofilter}
    </span>
    </div>
</div>
{else}
<div class="control-group">
    <label class="control-label cm-required">{__("status")}</label>
    <div class="controls">
        {if $items_status}
            {foreach from=$items_status item="val" key="st" name="status_cycle"}
                <label class="radio inline" for="{$id}_{$obj_id|default:0}_{$st|lower}"><input type="radio" name="{$input_name}" id="{$id}_{$obj_id|default:0}_{$st|lower}" {if $obj.status == $st || (!$obj.status && $smarty.foreach.status_cycle.first)}checked="checked"{/if} value="{$st}" />{$val}</label>
            {/foreach}
        {else}
            <label class="radio inline" for="{$id}_{$obj_id|default:0}_a"><input type="radio" name="{$input_name}" id="{$id}_{$obj_id|default:0}_a" {if $obj.status == "A" || !$obj.status}checked="checked"{/if} value="A" />{__("active")}</label>

        {if $hidden}
            <label class="radio inline" for="{$id}_{$obj_id|default:0}_h"><input type="radio" name="{$input_name}" id="{$id}_{$obj_id|default:0}_h" {if $obj.status == "H"}checked="checked"{/if} value="H" />{__("hidden")}</label>
        {/if}

        {if $obj.status == "P"}
            <label class="radio inline" for="{$id}_{$obj_id|default:0}_p"><input type="radio" name="{$input_name}" id="{$id}_{$obj_id|default:0}_p" checked="checked" value="P"/>{__("pending")}</label>
        {/if}

            <label class="radio inline" for="{$id}_{$obj_id|default:0}_d"><input type="radio" name="{$input_name}" id="{$id}_{$obj_id|default:0}_d" {if $obj.status == "D"}checked="checked"{/if} value="D" />{__("disabled")}</label>
        {/if}
    </div>
</div>
{/if}