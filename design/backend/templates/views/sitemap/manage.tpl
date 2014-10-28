{script src="js/tygh/tabs.js"}
{capture name="mainbox"}
<form action="{""|fn_url}" method="post" name="sitemap_form">

<div class="items-container cm-sortable" data-ca-sortable-table="sitemap_sections" data-ca-sortable-id-name="section_id" id="manage_sitemap_list">
{if $sitemap_sections}
<table class="table table-middle table-objects table-striped">
{foreach from=$sitemap_sections item=section}
    {include file="common/object_group.tpl"
        id=$section.section_id
        text=$section.section
        href="sitemap.update?section_id=`$section.section_id`"
        href_delete="sitemap.delete_section?section_id=`$section.section_id`"
        table="sitemap_sections"
        object_id_name="section_id"
        delete_target_id="manage_sitemap_list"
        status=$section.status
        additional_class="cm-sortable-row cm-sortable-id-`$section.section_id`"
        no_table=true
        is_view_link=false
        header_text="{__("editing_sitemap_section")}: `$section.section`"
        draggable=true}
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_items")}</p>
{/if}
<!--manage_sitemap_list--></div>
</form>
{/capture}

{capture name="adv_buttons"}
    {capture name="add_new_picker"}
        {include file="views/sitemap/update.tpl" section=[]}
    {/capture}

    {include file="common/popupbox.tpl" id="add_new_site_map_section" text=__("new_site_map_section") content=$smarty.capture.add_new_picker title=__("add_site_map_section") act="general" icon="icon-plus"}
{/capture}

{include file="common/mainbox.tpl" title=__("sitemap") content=$smarty.capture.mainbox adv_buttons=$smarty.capture.adv_buttons select_languages=true}