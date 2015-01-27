{include file="views/profiles/components/profiles_scripts.tpl" states=1|fn_get_all_states}

{script src="js/tygh/tabs.js"}
{script src="js/tygh/filter_table.js"}
{script src="js/tygh/fileuploader_scripts.js"}

{literal}
    <script type="text/javascript">
        (function(_, $) {
            $(document).ready(function(){
                var search_field = $("#elm_addon");
                var search_clear = $("#elm_addon_clear");

                // Init plugin
                search_field.ceFilterTable({
                    table: ".table-addons",
                    empty: ".no-items"
                });

                // Clear input
                search_clear.on("click", function() {
                    search_field.val("").trigger("input");
                    search_clear.addClass("hidden");
                });
                
                // Show clear button if search filed isn't empty
                search_field.on("keyup input", function() {
                    if(search_field.val().length > 0) {
                        search_clear.removeClass("hidden");
                    } else {
                        search_clear.addClass("hidden");
                    }
                });

            });
        }(Tygh, Tygh.$));
    </script>
{/literal}

{capture name="mainbox"}

{capture name="sidebar"}
    {include file="views/addons/components/addons_search_form.tpl" dispatch="addons.manage"}
    <hr>
    <div class="sidebar-row marketplace">
        <h6>{__("marketplace")}</h6>
        <p class="marketplace-link">{__("marketplace_find_more", ["[href]" => $config.resources.marketplace_url])}</p>
    </div>
{/capture}

{capture name="upload_addon"}
    {include file="views/addons/components/upload_addon.tpl"}
{/capture}

<div class="items-container" id="addons_list">
{hook name="addons:manage"}

<div class="tabs cm-j-tabs clear">
    <ul class="nav nav-tabs">
        <li id="tab_installed_addons" class="cm-js active"><a>{__("installed_addons")}</a></li>
        <li id="tab_browse_all_available_addons" class="cm-js"><a>{__("browse_all_available_addons")}</a></li>
    </ul>
</div>

<div class="cm-tabs-content">
    <div id="content_tab_installed_addons">
        {include file="views/addons/components/addons_list.tpl" show_installed=true}
        <p class="no-items hidden">{__("no_data")}</p>
    </div>
    <div id="content_tab_browse_all_available_addons">
        {include file="views/addons/components/addons_list.tpl"}
        <p class="no-items hidden">{__("no_data")}</p>
    </div>
</div>

{/hook}
<!--addons_list--></div>

{capture name="adv_buttons"}
    {hook name="addons:adv_buttons"}
    {if !$runtime.company_id && !"RESTRICTED_ADMIN"|defined}
        {include file="common/popupbox.tpl" id="upload_addon" text=__("upload_addon") title=__("upload_addon") content=$smarty.capture.upload_addon act="general" link_class="cm-dialog-auto-size" icon="icon-plus" link_text=""}
    {/if}
    {/hook}
{/capture}

{/capture}
{include file="common/mainbox.tpl" title=__("addons") content=$smarty.capture.mainbox sidebar=$smarty.capture.sidebar adv_buttons=$smarty.capture.adv_buttons}
