<div class="clearfix cm-field-container">
    <fieldset class="rating" id="{$rate_id}">
        {foreach from =""|fn_get_discussion_ratings item="title" key="val"}
        {$item_rate_id = "`$rate_id`_`$val`"}
        <input type="radio" id="{$item_rate_id}" name="{$rate_name}" value="{$val}" /><label for="{$item_rate_id}" title="{$title}">{$title}</label>
        {/foreach}
    </fieldset>
</div>