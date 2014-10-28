{capture name="mainbox"}
<div id="import_store">
    {strip}
        <ul class="import-progress">
            {foreach from=$steps key='step_number' item='step_data'}
                {if $step > $step_number}
                    {assign var="class" value="done"}
                {elseif $step == $step_number}
                    {assign var="class" value="active"}
                {else}
                    {assign var="class" value=""}
                {/if}
                <li class="{$class}">
                    <span class="import-progress-bg"></span>
                    <span class="import-progress-title">{if $class && $class != "active" && $step_data.href}<a href="{$step_data.href}">{$step_data.name}</a>{else}{$step_data.name}{/if}</span>
                </li>
            {/foreach}
        </ul>
    {/strip}

    {if $step == 1}
        <div id="import_step_1">
            <form action="{""|fn_url}" method="GET" name="import_step_1" enctype="multipart/form-data" class="form-horizontal">
               <div id="store_import_step_1">
                   <div class="control-group import-control-center">
                       <label class="control-label">{__("store_import.local_store")} <a class="cm-tooltip" title="{__("store_import.local_store_tooltip")}"><i class="icon-question-sign"></i></a>:</label>
                       <div class="controls">
                            <input type="text" class="input-import" name="store_data[path]" id="store_data_path" size="44" value="{$store_data.path}" placeholder="{__("store_import.full_path_installation_directory")}" />
                            {include file="buttons/button.tpl" but_name="dispatch[store_import.index.step_2]" but_text=__("store_import.select_store") but_role="button_main"}
                       </div>
                   </div>
               </div>
            </form>
        </div>
    {/if}
    {if $step == 2}
        {include file="addons/store_import/views/store_import/components/sidebar.tpl"}
        <div id="import_step_2">
            <p>{__("store_import.fill_form")}</p>
            {if $store_data.product_version == '2.2.4' || $store_data.product_version == '2.2.5'}
                <p>{__("store_import.second_step_22x_notice")}</p>
            {/if}
            <div class="import-from clearfix">
                <form action="{""|fn_url}" method="get" name="import_step_2" class="form-horizontal form-edit{if $smarty.const.DEVELOPMENT != true} cm-ajax cm-comet{/if}" enctype="multipart/form-data">
                    <div class="pull-left import-cart" id="store_import_step_2_from">
                        {include file="common/subheader.tpl" title="{__("store_import.from")} {$text_from}"}
                        {if $store_data.product_edition == 'ULTIMATE'}
                            <div class="control-group">
                                <label class="control-label">{__("store_import.admin_url")}:</label>
                                <div class="controls">
                                    <input type="text" value="{$store_data.admin_index}" disabled="disabled" />
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">{__("store_import.companies_count")}:</label>
                                <div class="controls">
                                    <input type="text" value="{$companies_count_from}" disabled="disabled" />
                                </div>
                            </div>
                        {else}
                            <div class="control-group">
                                <label class="control-label">{__("store_import.storefront_url")}:</label>
                                <div class="controls">
                                    <input type="text" value="{$store_data.storefront}" disabled="disabled" />
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">{__("store_import.secure_storefront_url")}:</label>
                                <div class="controls">
                                    <input type="text" value="{$store_data.secure_storefront}" disabled="disabled" />
                                </div>
                            </div>
                        {/if}

                        <div class="control-group">
                            <label class="control-label">{__("store_import.db_host")}:</label>
                            <div class="controls">
                                <input type="text" value="{$store_data.db_host}" disabled="disabled" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label">{__("store_import.db_name")}:</label>
                            <div class="controls">
                                <input type="text" value="{$store_data.db_name}" disabled="disabled" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label">{__("store_import.db_user")}:</label>
                            <div class="controls">
                                <input type="text" value="{$store_data.db_user}" disabled="disabled" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label">{__("store_import.table_prefix")}:</label>
                            <div class="controls">
                                <input type="text" value="{$store_data.table_prefix}" disabled="disabled" />
                            </div>
                        </div>
                    </div>

                    <div class="pull-right import-cart" id="store_import_step_to">

                        {include file="common/subheader.tpl" title=$text_to}
                        {if $store_data.product_edition == 'ULTIMATE'}
                            <div class="control-group">
                                <label class="control-label">{__("store_import.admin_url")}:</label>
                                <div class="controls">
                                    <input type="text" value="{$config.http_location}/{$config.admin_index}" disabled="disabled" />
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">{__("store_import.companies_count")}:</label>
                                <div class="controls">
                                    <input type="text" value="{$companies_count_from}" disabled="disabled" />
                                </div>
                            </div>
                        {else}
                            <div class="control-group">
                                <label class="control-label">{__("store_import.storefront_url")}:</label>
                                <div class="controls">
                                    <input type="text" value="{$config.http_location}" disabled="disabled" />
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">{__("store_import.secure_storefront_url")}:</label>
                                <div class="controls">
                                    <input type="text" value="{$config.https_location}" disabled="disabled" />
                                </div>
                            </div>
                        {/if}

                        <div class="control-group">
                            <label class="control-label">{__("store_import.db_host")}:</label>
                            <div class="controls">
                                <input type="text" value="{$config.db_host}" disabled="disabled" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label">{__("store_import.db_name")}:</label>
                            <div class="controls">
                                <input type="text" value="{$config.db_name}" disabled="disabled" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label">{__("store_import.db_user")}:</label>
                            <div class="controls">
                                <input type="text" value="{$config.db_user}" disabled="disabled" />
                            </div>
                        </div>
                        <div class="control-group">
                           <label class="control-label">{__("store_import.table_prefix")}:</label>
                           <div class="controls">
                                <input type="text" value="{$config.table_prefix}" disabled="disabled" />
                           </div>
                        </div>
                        <div class="control-group">
                            <div class="controls">
                                {include file="buttons/button.tpl" but_name="dispatch[store_import.index.step_3]" but_text=__("import_data")  but_target_form="import_step_2" but_role="button_main"}
                                {include file="buttons/button.tpl" but_href="store_import.index.step_1" but_text=__("go_back")  but_role="action"}
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    {/if}
    {if $step == 4}
        {include file="addons/store_import/views/store_import/components/sidebar.tpl"}
        <div id="import_step_4">
            {__("store_import.text_configuration")}
            {$step4_notification nofilter}
            {if fn_allowed_for("MULTIVENDOR")}
                {assign var="langvar_name" value="vendors"}
            {else}
                {assign var="langvar_name" value="stores"}
            {/if}
            {__("store_import.text_configuration2", ["[storefronts_vendors]" => __($langvar_name)])}
            <div>{include file="buttons/button.tpl" but_href="store_import.index.step_5" but_text=__("store_import.complete_configuration") but_role="action" but_meta="btn-primary"}</div>
        </div>
    {/if}
    {if $step == 5}
        {include file="addons/store_import/views/store_import/components/sidebar.tpl"}
        <div id="import_step_5">
            <div class="import-step-5">
                {assign var="date" value=fn_date_format($import_date, "%d.%m.%Y, %H:%M")}
                {assign var="edition" value=ucfirst(strtolower($store_data.product_edition))}
                {__("store_import.actualize_data_text", ["[date]" => $date, "[version]" => $store_data.product_version, "[edition]" => $edition])}
            </div>

            {if $smarty.const.DEVELOPMENT != true}
                {assign var=but_meta value="cm-ajax cm-comet"}
            {/if}
            {if $check_company_count_failed}
                {assign var=but_meta value="disabled"}
            {/if}
            {include file="buttons/button.tpl" but_href="store_import.index.actualize" but_text=__("store_import.actualize_data") but_role="action" but_meta=$but_meta}
            {include file="buttons/button.tpl" but_href="store_import.index.step_6" but_text=__("store_import.complete_store_import") but_role="action" but_meta="btn-primary"}
        </div>
    {/if}
    {if $step == 6}
        {include file="addons/store_import/views/store_import/components/sidebar.tpl"}
        <h4>{__("congratulations")}</h4>
        {__("store_import.completed")}
        {if fn_allowed_for('MULTIVENDOR')}
            {__("store_import.completed_text_mve")}
        {else}
            {__("store_import.completed_text_ult")}
        {/if}
        {include file="buttons/button.tpl" but_href="store_import.index.step_7" but_text=__("store_import.start_new_store_import")  but_role="action" but_meta="btn-primary"}
    {/if}
<!--import_store--></div>

{/capture}
{include file="common/mainbox.tpl" title=__("store_import.store_import") content=$smarty.capture.mainbox title_extra=$smarty.capture.title_extra sidebar=$smarty.capture.sidebar}
