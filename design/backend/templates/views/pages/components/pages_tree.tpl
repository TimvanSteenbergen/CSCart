{if !$checkbox_name}{assign var="checkbox_name" value="page_ids"}{/if}

{if $parent_id}<div {if !$expand_all}class="hidden"{/if} id="page{$combination_suffix}_{$parent_id}">{/if}
{foreach from=$pages_tree item=page}

{if "ULTIMATE"|fn_allowed_for}
    {assign var="allow_save" value=$page|fn_allow_save_object:"pages"}
{/if}

<table width="100%" class="table table-tree table-middle table-nobg">
{if $header && !$hide_header}
{assign var="header" value=""}
<thead>
<tr>
    <th class="left" width="3%">
    {if $display != "radio"}
        {include file="common/check_items.tpl"}
    {/if}
    </th>
    {if !$picker}
    <th class="left" width="7%">{__("position_short")}</th>
    {/if}
    <th width="70%" class="left">
        {if !$hide_show_all && !$search.paginate}    
            <span alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" id="on_page{$combination_suffix}" class="cm-combinations-pages{$combination_suffix}{if $expand_all} hidden{/if}" ><span class="exicon-expand "></span></span>
            <span alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" id="off_page{$combination_suffix}" class="cm-combinations-pages{$combination_suffix}{if !$expand_all} hidden{/if}" ><span class="exicon-collapse "></span></span>
        {/if}
        {__("name")}
    </th>
    {if !$hide_delete_button}<th width="10%">&nbsp;</th>{/if}
    {if !$picker}
        <th width="10%" class="right">{__("status")}</th>
    {/if}
</tr>
</thead>
{/if}
<tr class="cm-row-status-{$page.status|lower}" {if $page.level > 0 && !$search.paginate}class="multiple-table-row"{/if}>
    <td class="left" width="3%">
        {if $display == "radio"}
        <input type="radio" name="{$checkbox_name}" id="radio_{$page.page_id}" value="{$page.page_id}" class="cm-item" />
        {else}
        <input type="checkbox" name="{$checkbox_name}[]" id="checkbox_{$page.page_id}" value="{$page.page_id}" class="cm-item" />
        {/if}
    </td>
    {if !$picker}
    <td width="7%">
        <input type="text" name="pages_data[{$page.page_id}][position]" size="3" maxlength="10" value="{$page.position}" class="input-micro input-hidden" />
        {if "ULTIMATE"|fn_allowed_for}
            <input type="hidden" name="pages_data[{$page.page_id}][company_id]" size="3" maxlength="3" value="{$page.company_id}" class="hidden" />
        {/if}
    </td>
    {/if}
    <td class="row-status" width="70%">
        {strip}
        <div class="text-over" {if !$search.paginate}style="padding-left: {math equation="x*14" x=$page.level|default:0}px;"{/if}>
            {if $page.subpages || $page.has_children}
            {assign var="_dispatch" value=$dispatch|default:"pages.manage"}
            {if $except_id}
                {assign var="except_url" value="&except_id=`$except_id`"}
            {/if}
            <span alt="{__("expand_sublist_of_items")}" title="{__("expand_sublist_of_items")}" id="on_page{$combination_suffix}_{$page.page_id}" class="cm-combination-pages{$combination_suffix} {if $expand_all && !$hide_show_all}hidden{/if}" {if $page.has_children}onclick="Tygh.$.ceAjax('request', '{"$_dispatch?parent_id=`$page.page_id`&get_tree=multi_level`$except_url`&display=`$display`&checkbox_name=`$checkbox_name`&combination_suffix=`$combination_suffix`"|fn_url nofilter}', {$ldelim}result_ids: 'page{$combination_suffix}_{$page.page_id}', caching: true{$rdelim});"{/if}><span class="exicon-expand"></span></span>
            <span alt="{__("collapse_sublist_of_items")}" title="{__("collapse_sublist_of_items")}" id="off_page{$combination_suffix}_{$page.page_id}" class="cm-combination-pages{$combination_suffix} {if !$expand_all || $hide_show_all}hidden{/if}"><span class="exicon-collapse"></span> </span>
            {elseif !$search.paginate}
            <span style="padding-left: 14px;">&nbsp;</span>
            {/if}


            {if !$picker}<a href="{"pages.update?page_id=`$page.page_id`&come_from=`$come_from`"|fn_url}" {if $page.status == "N"}class="manage-root-item-disabled"{/if} id="page_title_{$page.page_id}" title="{$page.page}">{else}<label class="inline-label" for="radio_{$page.page_id}" id="page_title_{$page.page_id}">{/if}
                {$page.page}
            {if !$picker}</a>{else}</label>{/if}

            {if $page.page_type}
            {assign var="pt" value=$page_types[$page.page_type]}
            <span class="muted"> ({__($pt.single)})</span>
            {/if}
            <div class="shift-left">
                {include file="views/companies/components/company_name.tpl" object=$page}
            </div>
        </div>
        {/strip}
    </td>
    {if !$picker}
    <td width="10%">
        <input type="hidden" name="pages_data[{$page.page_id}][parent_id]" size="3" maxlength="10" value="{$page.parent_id}" />
        {capture name="tools_list"}
            {if $search.get_tree}
                {assign var="multi_level" value="&multi_level=Y"}
            {/if}
            {if !$picker}
                {assign var="_href" value="pages.update?page_id=`$page.page_id`&come_from=`$come_from`"}
            {/if}
            <li>{btn type="list" text=__("edit") href=$_href}</li>
            {if "ULTIMATE"|fn_allowed_for && $allow_save || !"ULTIMATE"|fn_allowed_for}
                <li>{btn type="list" text=__("delete") class="confirm" href="pages.delete?page_type=`$page.page_type`&page_id=`$page.page_id``$multi_level`&come_from=`$come_from`"}</li>
            {/if}
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
    {/if}
    {if !$hide_delete_button}
    <td width="10%" class="nowrap right">
        {if "ULTIMATE"|fn_allowed_for && $allow_save || !"ULTIMATE"|fn_allowed_for}
            {include file="common/select_popup.tpl" id=$page.page_id status=$page.status hidden=true object_id_name="page_id" table="pages" popup_additional_class="dropleft"}
        {/if}
    </td>
    {/if}
</tr>
</table>

{if $page.subpages || $page.has_children}
    {include file="views/pages/components/pages_tree.tpl" pages_tree=$page.subpages parent_id=$page.page_id}
{/if}
{foreachelse}
    {if !$hide_show_all}
        <p class="no-items">{__("no_data")}</p>
    {/if}
{/foreach}

{if $parent_id}<!--page{$combination_suffix}_{$parent_id}--></div>{/if}