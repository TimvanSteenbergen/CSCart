<div class="snize" id="snize_container"></div>

{if $addons.searchanise.status != 'D'}
    {""|fn_se_check_import_is_done}
    {assign var="se_company_id" value=""|fn_se_get_company_id}
    {assign var="se_parent_private_key" value=$se_company_id|fn_se_get_parent_private_key:$smarty.const.CART_LANGUAGE}
{/if}

{if $settings.Appearance.calendar_date_format == "month_first"}
    {assign var="date_format" value="%k:%M %m/%d/%Y"}
{else}
    {assign var="date_format" value="%k:%M %d/%m/%Y"}
{/if}

{if "HTTPS"|defined}
    {assign var="se_service_url" value=$smarty.const.SE_SERVICE_URL|replace:'http://':'https://'}
{else}
    {assign var="se_service_url" value=$smarty.const.SE_SERVICE_URL}
{/if}

<script type="text/javascript">
    Tygh.$('.btn-toolbar').hide(); // FIXME
</script>

{if $addons.searchanise.status == 'D'}
    <script type="text/javascript">
        SearchaniseAdmin = {ldelim}{rdelim};
        SearchaniseAdmin.host = '{$se_service_url}';
        SearchaniseAdmin.AddonStatus = 'disabled';
    </script>
    <script type="text/javascript" src="{$se_service_url}/js/init.js"></script>

{elseif !"ULTIMATE"|fn_allowed_for}
    {include file="addons/searchanise/settings/components/se_admin_panel.tpl"}
{else}
    {if !$runtime.company_id} {* Is root admin. *}
        {if ""|fn_se_is_registered == false} {* Only root admin can register. *}
            <script type="text/javascript">
                SearchaniseAdmin = {ldelim}{rdelim};
                SearchaniseAdmin.host = '{$se_service_url}';
                SearchaniseAdmin.PrivateKey = '';
                SearchaniseAdmin.ConnectLink = '{"searchanise.signup"|fn_url:'A':'current'}';
                SearchaniseAdmin.AddonStatus = 'enabled';
            </script>
            <script type="text/javascript" src="{$se_service_url}/js/init.js"></script>
        {else}
            {* If active only one store.*}
            {if $runtime.forced_company_id} 
                {include file="addons/searchanise/settings/components/se_admin_panel.tpl"}

            {* After register we always need select a vendor. *}
            {else}
                {include file="common/select_company.tpl" hide_title=true select_id="searchanise_company_select" assign="mb"}
                {$smarty.capture.mainbox nofilter}
            {/if}
        {/if}
    {else} {* Vendor selected *}
        {if ""|fn_se_is_registered == false} {* Only root admin can register. *}
            <p>
                {__("text_se_only_root_can_register")}
                <br /><br /><br /><br />
            </p>
        {else}
            {include file="addons/searchanise/settings/components/se_admin_panel.tpl"}
        {/if}
    {/if}    
{/if}