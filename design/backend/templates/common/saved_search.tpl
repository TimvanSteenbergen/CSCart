{assign var="views" value=$view_type|fn_get_views}

{assign var="max_items" value="4"}
{hook name="advanced_search:views"}
    {if $views}
        <div class="sidebar-row" id="views">
            <h6>{__("saved_search")}</h6>
                <ul class="nav nav-list saved-search">
                    {if $views}
                    <li {if !$search.view_id}class="active"{/if}>
                        <a href="{"`$dispatch`.reset_view"|fn_url}">{__("all")}</a>
                    </li>
                    {foreach from=$views item=view name=views}
                        {if $smarty.foreach.views.index == $max_items}
                        {$s_id=$dispatch|fn_crc32|string_format:"saved_searches_%s"}
                        <li>
                            <span class="more hand">
                                <a id="on_{$s_id}" class="collapsed cm-combination cm-save-state {if $smarty.cookies.$s_id}hidden{/if}">{__("more")}<i class="exicon-collapse"></i></a>
                                <a id="off_{$s_id}" class="cm-combination cm-save-state {if !$smarty.cookies.$s_id}hidden{/if}">{__("more")}<i class="exicon-collapse"></i></a>
                            </span>
                        </li>
                        <li id="{$s_id}" class="{if !$smarty.cookies.$s_id}hidden{/if}">
                            <ul class="nav nav-list">
                        {/if}
                        <li {if $view.view_id == $search.view_id}class="active"{/if}>
                            {assign var="return_current_url" value=$config.current_url|fn_query_remove:"view_id":"new_view"}
                            {assign var="redirect_current_url" value=$config.current_url|escape:url}
                            <a href="{"`$dispatch`.delete_view?view_id=`$view.view_id`&redirect_url=`$redirect_current_url`"|fn_url}" class="cm-confirm cm-tooltip icon-trash" title="{__("delete")}"></a>
                            <a class="cm-view-name" data-ca-view-id="{$view.view_id}" href="{"`$dispatch`?view_id=`$view.view_id`"|fn_url}">{$view.name}</a>
                        </li>
                    {/foreach}
                    {if $smarty.foreach.views.total > $max_items}
                            </ul>
                        </li>
                    {/if}
                    {/if}
                    <li class="last">
                        {include file="buttons/button.tpl" but_text=__("new_saved_search") but_role="text" but_meta="text-button cm-dialog-opener" but_target_id="adv_search"}
                    </li>
                </ul>
        </div>
        <hr>
    {/if}
{/hook}