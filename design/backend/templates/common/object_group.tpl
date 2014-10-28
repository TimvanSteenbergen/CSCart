{if !$no_table}
<table width="100%" class="table table-middle table-objects{if $table_striped} table-striped{/if}">
    <tbody>
{/if}
        <tr class="cm-row-status-{$status|lower} {$additional_class} cm-row-item" {if $row_id}id="{$row_id}"{/if} data-ct-{$table}="{$id}">
            {if $checkbox_name}
            <td>
                <input type="checkbox" name="{$checkbox_name}" value="{$checkbox_value|default:$id}"{if $checked} checked="checked"{/if} class="cm-item" />
            </td>
            {/if}
            {if $draggable}
                <td width="1%" class="no-padding-td">
                    <span class="handler cm-sortable-handle"></span>
                </td>
            {/if}
            <td width="{if $href_desc}77{else}28{/if}%">
                <div class="object-group-link-wrap">
                {if !$non_editable}
                    <a {if $no_popup}href="{$href|fn_url}"{/if} class="row-status cm-external-click{if $non_editable} no-underline{/if}{if $main_link} link{/if} {$link_meta}{if $is_promo}cm-promo-popup{/if}"{if !$non_editable && !$not_clickable} data-ca-external-click-id="opener_group{$id_prefix}{$id}"{/if}{if $main_link} {if !$is_promo}href="{$main_link|fn_url}{/if}"{/if}>{if $show_id}#{$id}{/if} {$text}</a>
                {else}
                    <span class="unedited-element block {$link_meta}">{$text|default:__("view")}</span>
                {/if}
                {if $href_desc}<small>{$href_desc nofilter}</small>{/if}
                {if $company_object}
                    {include file="views/companies/components/company_name.tpl" object=$company_object}
                {/if}
                </div>
            </td>
            <td width="{if $href_desc}0{else}50{/if}%">
                <span class="row-status object-group-details">{$details nofilter}</span>
            </td>
            <td width="10%" class="right nowrap">

                <div class="pull-right hidden-tools">
                    {capture name="items_tools"}
                    {if $tool_items}
                        {$tool_items nofilter}
                    {/if}
                        {if !$non_editable || $is_view_link}
                            {if $no_popup}
                                <li>{btn type="list" text=$link_text|default:__("edit") href=$href}</li>
                            {else}
                               <li>{include file="common/popupbox.tpl" id="group`$id_prefix``$id`" edit_onclick=$onclick text=$header_text act=$act|default:"edit" picker_meta=$picker_meta link_text=$link_text href=$href is_promo=$is_promo no_icon_link=true}</li>
                            {/if}
                        {/if}

                        {if !$non_editable}
                            {if $href_delete && !$skip_delete}
                                {if $is_promo}
                                    {$class="cm-promo-popup"}
                                {else}
                                    {$class="cm-delete-row"}
                                    {$href=$href_delete}
                                {/if}
                                <li>{btn type="text" text=__("delete") href=$href class="cm-confirm cm-tooltip cm-ajax cm-ajax-force cm-ajax-full-render `$class`" data=["data-ca-target-id" => $delete_target_id, "data-ca-params" => $delete_data]}</li>
                            {/if}
                        {/if}
                    {/capture}
                    {dropdown content=$smarty.capture.items_tools class="dropleft"}
                </div>
                {$links nofilter}
            </td>
            {if !$nostatus}
                <td width="12%">
                    <div class="pull-right nowrap">
                        {if $non_editable == true || $is_promo}
                            {assign var="display" value="text"}
                        {/if}

                        {if $can_change_status}
                            {assign var="non_editable" value=false}
                            {assign var="display" value=""}
                        {/if}

                        {include file="common/select_popup.tpl" popup_additional_class="dropleft" id=$id status=$status hidden=$hidden object_id_name=$object_id_name table=$table hide_for_vendor=$hide_for_vendor display=$display non_editable=$non_editable update_controller=$update_controller st_result_ids=$st_result_ids}
                    </div>
                </td>
            {/if}
        <!--{$row_id}--></tr>
{if !$no_table}
    </tbody>
</table>
{/if}