{assign var="columns" value=4}
{if !$wishlist_is_empty}

    {script src="js/tygh/exceptions.js"}

    {assign var="show_hr" value=false}
    {assign var="location" value="cart"}
{/if}
{if $products}
    {include file="blocks/list_templates/grid_list.tpl" 
        columns=$columns
        show_empty=true
        show_trunc_name=true 
        show_old_price=true 
        show_price=true 
        show_clean_price=true 
        show_list_discount=true
        no_pagination=true
        no_sorting=true
        show_add_to_cart=false
        is_wishlist=true}
{else}
    {math equation="100 / x" x=$columns|default:"2" assign="cell_width"}
    <div class="grid-list {if $wishlist_is_empty}wish-list-empty{/if}">
        {assign var="iteration" value=0}
        {capture name="iteration"}{$iteration}{/capture}
            {hook name="wishlist:view"}
            {/hook}
        {assign var="iteration" value=$smarty.capture.iteration}
        {if $iteration == 0 || $iteration % $columns != 0}
            {math assign="empty_count" equation="c - it%c" it=$iteration c=$columns}
            {section loop=$empty_count name="empty_rows"}
                <div class="ty-column{$columns}">
                    <div class="ty-product-empty">
                        <span class="ty-product-empty__text">{__("empty")}</span>
                    </div>
                </div>
            {/section}
        {/if}
    </div>
{/if}

{if !$wishlist_is_empty}
    <div class="buttons-container ty-wish-list__buttons">
        {include file="buttons/button.tpl" but_text=__("clear_wishlist") but_href="wishlist.clear" but_meta="ty-btn__tertiary"}
        {include file="buttons/continue_shopping.tpl" but_href=$continue_url|fn_url but_role="text"}
    </div>
{else}
    <div class="buttons-container ty-wish-list__buttons ty-wish-list__continue">
        {include file="buttons/continue_shopping.tpl" but_href=$continue_url|fn_url but_role="text"}
    </div>
{/if}

{capture name="mainbox_title"}{__("wishlist_content")}{/capture}
