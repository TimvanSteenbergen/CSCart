{split data=$categories size=$columns|default:"3" assign="splitted_categories"}
{math equation="floor(100/x)" x=$columns|default:"3" assign="cell_width"}

<table class="table-width subcategories">
{foreach from=$splitted_categories item="scats"}
    <tr>
        {foreach from=$scats item="category"}
            {if $category}
                <td class="center valign-top {if $category.main_pair}with-image{/if}" style="width: {$cell_width}%">
                    <p class="margin-bottom">
                        <a href="{"categories.view?category_id=`$category.category_id`"|fn_url}" class="strong">
                        {if $category.main_pair}
                            {include file="common/image.tpl"
                                show_detailed_link=false
                                images=$category.main_pair
                                no_ids=true
                                image_id="category_image"
                                image_width=$settings.Thumbnails.category_lists_thumbnail_width
                                image_height=$settings.Thumbnails.category_lists_thumbnail_height
                            }
                        {/if}
                        <strong>{$category.category}</strong>
                        </a>
                    </p>
                </td>
            {else}
                <td style="width: {$cell_width}%">&nbsp;</td>
            {/if}
        {/foreach}
    </tr>
{/foreach}
</table>

{capture name="mainbox_title"}{$title}{/capture}