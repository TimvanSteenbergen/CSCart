<div id="content_tags_tab">
    
    {script src="js/addons/tags/tags_autocomplete.js"}

    <div class="control-group my-tags">
        <label>{__("my_tags")}</label>
        {if $auth.user_id}
            <ul id="my_tags">
                <input type="hidden" id="object_id" value={$object_id} />
                <input type="hidden" id="object_type" value={$object_type} />
                {foreach from=$object.tags.user item="tag" name="tags"}<li>{$tag.tag}</li>{/foreach}
            </ul>
        {else}
            {assign var="return_current_url" value=$config.current_url|escape:url}
            <a class="text-button" href="{if $runtime.controller == "auth" && $runtime.mode == "login_form"}{$config.current_url|fn_url}{else}{"auth.login_form?return_url=`$return_current_url`"|fn_url}{/if}">{__("sign_in_to_enter_tags")}</a>
        {/if}
    </div>

    <div class="control-group">
        <label>{__("popular_tags")}</label>
        {if $object.tags.popular}
            <ul class="tag-product clearfix">
                {foreach from=$object.tags.popular item="tag" name="tags"}
                {assign var="tag_name" value=$tag.tag|escape:url}
                    <li>
                        <a href="{"tags.view?tag=`$tag_name`"|fn_url}">
                            {$tag.tag}                                    
                        </a>
                        {assign var="return_current_url" value=$config.current_url|escape:url}
                    </li>
                {/foreach}
            </ul>
        {else}
            {__("none")}
        {/if}
    </div>  
</div>