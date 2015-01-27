<div class="sidebar-row">
    <ul class="unstyled list-with-btns">
        {foreach from=$settings item="option"}

        {if $option.parent_id}
            {$parent_item = $settings[$option.parent_id]}
        {else}
            {$parent_item = ""}
        {/if}


        <li class="{if $parent_item && $parent_item.value != "Y"}hidden{/if} cm-switch-container">
            <div class="list-description">
                {$option.description}
            </div>
            <div class="switch switch-mini cm-switch-change list-btns" {if $parent_item}data-ca-switch-parent-id="elm_{$parent_item.name}"{/if} id="elm_{$option.name}">
                <input type="checkbox" name="settings[{$option.object_id}]" value="1" {if $option.value == "Y"}checked="checked"{/if}/>
            </div>
        </li>
        {/foreach}           
    </ul>
</div>
<script type="text/javascript">
    (function (_, $) {
        $(_.doc).on('switch-change', '.cm-switch-change', function (e, data) {

            var self = $(this);
            var req = {};
            req[data.el.prop('name')] = data.value ? 'Y' : 'N';
            $.ceAjax('request', fn_url("settings.update"), {
                data: req,
                callback: function(){
                    
                    var sw = $('.cm-switch-change').filter('[data-ca-switch-parent-id=' + self.prop('id') + ']');
                    if (sw.length) {
                        sw.closest('.cm-switch-container').toggle();
                    }
                }
            });
        });
    }(Tygh, Tygh.$));
</script>