{if $items|count == 0}
    <thead>
    <tr>
        <th class="left" width="5%"></th>
        <th width="10%">{__("position_short")}</th>
        <th width="65%">
            &nbsp;{__("name")}
        </th>
        <th width="10%">&nbsp;</th>
        <th width="10%" class="center">{__("status")}</th>
    </tr>
    </thead>
{/if}

{foreach from=$items item="item"}
    {if $header}
        {assign var="header" value=""}
        <thead>
        <tr>
            <th class="left" width="5%">
                {include file="common/check_items.tpl"}
            </th>
            <th width="10%">{__("position_short")}</th>
            <th width="65%">
                <div class="pull-left">
                <span class="hand cm-combinations cm-tooltip" title="{__("expand_collapse_list")}" id="on_item">
                    <span class="exicon-expand"></span>
                </span>
                <span class="hand cm-combinations hidden cm-tooltip" title="{__("expand_collapse_list")}" id="off_item">
                    <span class="exicon-collapse"></span>
                </span>
                </div>
                &nbsp;{__("name")}
            </th>
            <th width="10%">&nbsp;</th>
            <th width="10%" class="center">{__("status")}</th>
        </tr>
        </thead>
    {/if}
    <tr class="{if $item.level > 0}multiple-table-row{/if} cm-row-item cm-row-status-{$item.status|lower}">
        <td class="left">
            <input type="checkbox" name="static_data_ids[]" value="{$item.param_id}" class="checkbox cm-item">
        </td>
        <td>
            <input type="text" name="static_data[{$item.param_id}][position]" value="{$item.position}" size="3" class="input-micro input-hidden">
        </td>
        <td>
        <span style="padding-left: {math equation="x*14" x=$item.level|default:0}px;" class="table-elem">
            {if $item.subitems}
                <span class="hand cm-combination cm-tooltip" id="on_item_{$item.param_id}" title="{__("expand_sublist_of_items")}">
                    <span class="exicon-expand"></span>
                </span>
                <span class="hand cm-combination hidden cm-tooltip" id="off_item_{$item.param_id}" title="{__("collapse_sublist_of_items")}">
                    <span class="exicon-collapse"></span>
                </span>
            {/if}
            <a class="cm-external-click" data-ca-external-click-id="{"opener_group`$item.param_id`"}">{$item.descr}</a>
        </span>
        </td>
        <td class="nowrap">
            <div class="pull-right hidden-tools">
                {capture name="tools_list"}
                    <li>{include file="common/popupbox.tpl" act="edit" text="{__($section_data.edit_title)}: `$item.descr`" link_text=__("edit") id="group`$item.param_id`" link_class="tool-link" no_icon_link=true href="static_data.update?param_id=`$item.param_id`&section=`$section`&`$owner_condition`"}</li>
                    <li>{btn type="list" text=__("delete") href="static_data.delete?param_id=`$item.param_id`&section=`$section`&`$owner_condition`" class="cm-confirm cm-ajax cm-delete-row"  data=['data-ca-target-id'=>'static_data_list']}</li>
                {/capture}
                {dropdown content=$smarty.capture.tools_list}
            </div>
        </td>
        <td class="right">
            {include file="common/select_popup.tpl" id=$item.param_id status=$item.status hidden=true object_id_name="param_id" table="static_data"}
        </td>
    </tr>
    {if $item.subitems}
        <tbody id="item_{$item.param_id}" class="hidden">
        {include file="views/static_data/components/multi_list.tpl" items=$item.subitems header=false}
        </tbody>
    {/if}
{/foreach}