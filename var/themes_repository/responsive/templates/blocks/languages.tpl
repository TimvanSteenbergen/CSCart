<div id="languages_{$block.block_id}">
    {if $languages && $languages|count > 1}
    {if $dropdown_limit > 0 && $languages|count <= $dropdown_limit}
        <div class="ty-select-wrapper ty-languages">
            {foreach from=$languages key=code item=language}
                <a href="{$config.current_url|fn_link_attach:"sl=`$language.lang_code`"}" title="{__("change_language")}" class="ty-languages__item{if $format == "icon"} ty-languages__icon-link{/if}{if $smarty.const.DESCR_SL == $code} ty-languages__active{/if}"><i class="ty-flag ty-flag-{$language.country_code|lower}"></i>{if $format == "name"}{$language.name}{/if}</a>
            {/foreach}
        </div>
    {else}
        {if $format == "name"}
            {assign var="key_name" value="name"}
        {else}
            {assign var="key_name" value=""}
        {/if}
        <div class="ty-select-wrapper{if $format == "icon"} ty-languages__icon-link{/if}">{include file="common/select_object.tpl" style="graphic" suffix="language" link_tpl=$config.current_url|fn_link_attach:"sl=" items=$languages selected_id=$smarty.const.CART_LANGUAGE display_icons=true key_name=$key_name language_var_name="sl" link_class="hidden-phone hidden-tablet"}</div>
    {/if}
{/if}

<!--languages_{$block.block_id}--></div>