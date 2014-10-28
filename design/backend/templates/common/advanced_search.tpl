{script src="js/tygh/advanced_search.js"}
<script type="text/javascript">
Tygh.tr('object_exists', '{__("object_exists")|escape:"javascript"}');
</script>

<input type="hidden" name="is_search" value="Y" />
{assign var="a_id" value=$dispatch|fn_crc32|string_format:"s_%s"}
{assign var="views" value=$view_type|fn_get_views}

{if !$in_popup}
    {if $simple_search}
    <div id="simple_search_common">
        <div id="simple_search">
            {$simple_search nofilter}
        </div>
    </div>
    {/if}
    <div class="sidebar-field">
        {include file="buttons/search.tpl" but_name="dispatch[`$dispatch`]"}
        {if !$no_adv_link}
            <a class="advanced-search cm-dialog-opener" id="adv_search_opener" data-ca-target-id="adv_search">{__("advanced_search")}</a>
        {/if}
    </div>

<div id="{$a_id}">
    <div class="hidden adv-search" id="adv_search" title="{__("advanced_search")}">
        {if $simple_search}
            <div class="group" id="simple_search_popup"></div>
        {/if}

        {$advanced_search nofilter}
        
        <div class="modal-footer buttons-container">
            <div class="pull-right">
                <a class="cm-dialog-closer cm-cancel tool-link btn" data-dismiss="modal">{__("cancel")}</a>
                {include file="buttons/search.tpl" but_name="dispatch[`$dispatch`]" but_role="submit" method="GET"}
            </div>
            {if !$not_saved}
                <div class="pull-left">
                    {if $smarty.request.dispatch|strpos:".picker" === false}
                    <span class="pull-left">{__("save_this_search_as")}</span>
                    <div class="input-append">
                    <input type="text" id="view_name" name="new_view" value="{if $search.view_id && $views[$search.view_id]}{$views[$search.view_id].name}{else}{__("name")}{/if}" title="{__("name")}" class="input-medium cm-hint" />
                        {include file="buttons/button.tpl" but_text=__("save") but_id="adv_search_save" but_role="advanced-search"}
                        </div>
                    {/if}
                </div>
            {/if}
        </div>
    </div>
</div>

{else}
    {$simple_search nofilter}
    <div class="sidebar-field in-popup">
    {include file="buttons/search.tpl" but_name="dispatch[`$dispatch`]"}
    {if $advanced_search|trim != ""}
        <a id="sw_{$a_id}" class="cm-combination cm-save-state" title="{__("advanced_search_options")}">
            <i id="on_{$a_id}" class="icon-chevron-down cm-combination cm-save-state {if $smarty.cookies.$a_id}hidden{/if}"></i>
            <i id="off_{$a_id}" class="icon-chevron-up cm-combination cm-save-state {if !$smarty.cookies.$a_id}hidden{/if}"></i>
        </a>
    {/if}
    </div>
    <div id="{$a_id}" class="search-advanced {if !$smarty.cookies.$a_id}hidden{/if}">
        {$advanced_search nofilter}
    </div>
{/if}
