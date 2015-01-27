{capture name="mainbox"}

{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}

<form class="form-horizontal form-edit" action="{""|fn_url}" method="post" name="tags_form">

{include file="common/pagination.tpl" save_current_page=true save_current_url=true}

{if $tags}
<table width="100%" class="table table-sort table-middle">
<thead>
<tr>
    <th class="left" width="1%">{include file="common/check_items.tpl"}</th>
    <th width="50%"><a class="cm-ajax{if $search.sort_by == "tag"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=tag&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("tag")}</a></th>
    <th class="center"><a class="cm-ajax{if $search.sort_by == "popularity"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=popularity&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("popularity")}</a></th>
    <th class="center"><a class="cm-ajax{if $search.sort_by == "users"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=users&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("users")}</a></th>
    {foreach from=$tag_objects item="o"}
    <th class="center">&nbsp;&nbsp;{__($o.name)}&nbsp;&nbsp;</th>
    {/foreach}
    <th>&nbsp;</th>
    <th class="right" width="12%"><a class="cm-ajax{if $search.sort_by == "status"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=status&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("status")}</a></th>
</tr>
</thead>
{foreach from=$tags item="tag"}
<tbody>
    <tr>
        <td class="left"><input type="checkbox" class="cm-item" value="{$tag.tag_id}" name="tag_ids[]"/></td>
        <td>
            <input type="text" name="tags_data[{$tag.tag_id}][tag]" value="{$tag.tag}" size="20" class="input-hidden">
        </td>
        <td class="center">{$tag.popularity}</td>
        <td class="center">{if $tag.users}<a href="{"profiles.manage?tag=`$tag.tag`"|fn_url}">{$tag.users}{else}0{/if}</td>
        {foreach from=$tag_objects key="k" item="o"}
        <td class="center">
            {if $tag.objects_count.$k}<a href="{"`$o.url`&tag=`$tag.tag`"|fn_url}">{$tag.objects_count.$k}</a>{else}0{/if}
        </td>
        {/foreach}
        <td>
            <div class="hidden-tools">
                {capture name="tools_list"}
                    <li>{btn type="list" class="cm-confirm" text=__("delete") href="tags.delete?tag_id=`$tag.tag_id`"}</li>
                {/capture}
                {dropdown content=$smarty.capture.tools_list}
            </div>
        </td>
        <td class="right">
            {include file="common/select_popup.tpl" id=$tag.tag_id status=$tag.status items_status="tags"|fn_get_predefined_statuses object_id_name="tag_id" table="tags"}
        </td>
    </tr>
{/foreach}
</tbody>
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl"}

</form>

{capture name="adv_buttons"}
    {capture name="add_new_picker"}
        <form action="{""|fn_url}" method="post" name="add_tag_form" class="form-horizontal form-edit ">
        <input type="hidden" name="tag_id" value="0">
        <div class="tabs cm-j-tabs">
            <ul class="nav nav-tabs">
                <li id="tab_static_data_new" class="cm-js active"><a>{__("general")}</a></li>
            </ul>
        </div>
        <div class="cm-tabs-content" id="content_tab_static_data_new">
            <div class="control-group">
                <label class=" control-label cm-required" for="add_tag_data">{__("tag")}</label>
                <div class="controls">
                    <input type="text" name="tag_data[tag]" id="add_tag_data" value="">
                </div>
            </div>

            <div class="control-group">
                <label for="add_tag_status" class="control-label cm-required">{__("status")}:</label>
                <div class="controls">
                <select name="tag_data[status]" id="add_tag_status">
                    <option value="A">{__("approved")}</option>
                    <option value="D">{__("disapproved")}</option>
                    <option value="P">{__("pending")}</option>
                </select>
                </div>
            </div>

            <div class="buttons-container">
                {include file="buttons/save_cancel.tpl" but_name="dispatch[tags.update]" cancel_action="close"}
            </div>
        </div>
        </form>
    {/capture}
    {include file="common/popupbox.tpl" id="add_new_picker" text=__("new_tag") content=$smarty.capture.add_new_picker title=__("add_tag") icon="icon-plus" act="general"}
{/capture}

{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {if $tags}
            {hook name="tags:list_extra_links"}
                <li>{btn type="list" text=__("approve_selected") dispatch="dispatch[tags.approve]" form="tags_form"}</li>
                <li>{btn type="list" text=__("disapprove_selected") dispatch="dispatch[tags.disapprove]" form="tags_form"}</li>
            {/hook}
            <li class="divider"></li>
            <li>{btn type="delete_selected" dispatch="dispatch[tags.m_delete]" form="tags_form"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
    {if $tags}
        {include file="buttons/save.tpl" but_name="dispatch[tags.m_update]" but_role="submit-link" but_target_form="tags_form"}
    {/if}
{/capture}

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="tags.manage" view_type="tags"}
    {include file="addons/tags/views/tags/components/tags_search_form.tpl" dispatch="tags.manage"}
{/capture}

{include file="common/mainbox.tpl" title=__("tags") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar}