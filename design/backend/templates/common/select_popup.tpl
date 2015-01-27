{if $non_editable || $display == "text"}
    <span class="view-status">
        {if $status == "A"}
            {__("active")}
        {elseif $status == "H"}
            {__("hidden")}
        {elseif $status == "D"}
            {__("disabled")}
        {elseif $status == "P"}
            {__("pending")}
        {elseif $status == "N"}
            {__("new")}
        {/if}
    </span>
{else}
{assign var="prefix" value=$prefix|default:"select"}
{assign var="btn_meta" value=$btn_meta|default:"btn-text"}
{assign var="popup_additional_class" value=$popup_additional_class}
<div class="cm-popup-box dropleft {if !$hide_for_vendor}dropdown{/if} {$popup_additional_class}">
    {if !$hide_for_vendor}
        <a {if $id}id="sw_{$prefix}_{$id}_wrap"{/if} class="{if $btn_meta}{$btn_meta}{/if} btn dropdown-toggle{if $id} cm-combination{/if}" data-toggle="dropdown">
    {/if}
        {if $items_status}
            {$items_status.$status|default:$default_status_text}
        {else}
            {if $status == "A"}
                {__("active")}
            {elseif $status == "D"}
                {__("disabled")}
            {elseif $status == "H"}
                {__("hidden")}
            {elseif $status == "P"}
                {__("pending")}
            {elseif $status == "N"}
                {__("new")}
            {/if}
        {/if}
    {if !$hide_for_vendor}
        <span class="caret"></span>
        </a>
    {/if}
    {if $id && !$hide_for_vendor}
        {assign var="_update_controller" value=$update_controller|default:"tools"}
        {if $table && $object_id_name}{capture name="_extra"}&table={$table}&id_name={$object_id_name}{/capture}{/if}
            <ul class="dropdown-menu">
            {if !$items_status}
            {assign var="items_status" value=$status|fn_get_default_statuses:$hidden}
            {assign var="extra_params" value="&table=`$table`&id_name=`$object_id_name`"}
            {else}
            {assign var="extra_params" value="`$smarty.capture._extra``$extra`"}
            {/if}
            {if $st_return_url}
                {$return_url = $st_return_url|escape:url}
                {$extra_params = "`$extra_params`&redirect_url=`$return_url`"}
            {/if}
            {if $items_status}
                {foreach from=$items_status item="val" key="st"}
                <li {if $status == $st}class="disabled"{/if}><a class="{if $confirm}cm-confirm {/if}status-link-{$st|lower} {if $status == $st}active{else}cm-ajax{/if} {if $status_meta}{$status_meta}{/if}"{if $status_target_id} data-ca-target-id="{$status_target_id}"{/if} href="{"`$_update_controller`.update_status?id=`$id`&status=`$st``$extra_params``$dynamic_object`"|fn_url}" onclick="return fn_check_object_status(this, '{$st|lower}', '{if $statuses}{$statuses[$st].params.color|default:''}{/if}');" {if $st_result_ids}data-ca-target-id="{$st_result_ids}"{/if} data-ca-event="ce.update_object_status_callback">{$val}</a></li>
                {/foreach}
            {/if}
            {capture name="list_items"}
            {if $notify}
                <li class="divider"></li>
                <li><a><label for="{$prefix}_{$id}_notify">
                    <input type="checkbox" name="__notify_user" id="{$prefix}_{$id}_notify" value="Y" checked="checked" onclick="Tygh.$('input[name=__notify_user]').prop('checked', this.checked);" />
                    {$notify_text|default:__("notify_customer")}</label></a>
                </li>
            {/if}
            {if $notify_department}
                <li><a><label for="{$prefix}_{$id}_notify_department">
                    <input type="checkbox" name="__notify_department" id="{$prefix}_{$id}_notify_department" value="Y" checked="checked" onclick="Tygh.$('input[name=__notify_department]').prop('checked', this.checked);" />
                    {__("notify_orders_department")}</label></a>
                </li>
            {/if}
            {if "MULTIVENDOR"|fn_allowed_for && $notify_vendor}
                <li><a><label for="{$prefix}_{$id}_notify_vendor">
                    <input type="checkbox" name="__notify_vendor" id="{$prefix}_{$id}_notify_vendor" value="Y" checked="checked" onclick="Tygh.$('input[name=__notify_vendor]').prop('checked', this.checked);" />
                    {__("notify_vendor")}</label></a>
                </li>
            {/if}

            {hook name="select_popup:notify_checkboxes"}
            {/hook}

            {/capture}
            
            {if $smarty.capture.list_items|trim}
                {$smarty.capture.list_items nofilter}
            {/if}
        </ul>
        {if !$smarty.capture.avail_box}
        {script src="js/tygh/select_popup.js"}
        {capture name="avail_box"}Y{/capture}
        {/if}
    {/if}
</div>
{/if}
