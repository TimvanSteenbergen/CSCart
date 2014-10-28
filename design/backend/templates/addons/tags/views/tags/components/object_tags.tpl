<div id="content_tags">

{script src="js/addons/tags/tags_autocomplete.js"}

    <div class="control-group {if $allow_save}cm-no-hide-input{/if}">
        <label class="control-label">{__("my_tags")}:</label>
        <div class="controls">
            <ul id="my_tags">
                <input type="hidden" id="object_id" value={$object_id} />
                <input type="hidden" id="object_type" value={$object_type} />
                <input type="hidden" name="{$input_name}[tags][]" value="" />
                <input type="hidden" id="object_name" value="{$input_name}[tags][]" />
                {foreach from=$object.tags.user item="tag" name="tags"}<li>{$tag.tag}</li>{/foreach}
            </ul>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label">{__("popular_tags")}:</label>
        <div class="controls">
            <div class="text-type-value">
                {if $object.tags.popular}
                    {foreach from=$object.tags.popular item="tag" name="tags"}
                        {$tag.tag}({$tag.popularity}) {if !$smarty.foreach.tags.last},{/if}
                    {/foreach}
                {else}
                    {__("none")}
                {/if}
            </div>
        </div>
    </div>

</div>
