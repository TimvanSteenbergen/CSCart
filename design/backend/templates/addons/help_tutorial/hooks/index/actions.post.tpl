{if ($runtime.controller == "block_manager" && $runtime.mode == "manage" || $runtime.controller == "themes" && $runtime.mode == "manage" || $runtime.controller == "store_import" && $runtime.mode == "index")}
<div class="help-tutorial clearfix">
    <span class="help-tutorial-link cm-external-click" id="help_tutorial_link" data-ca-scroll="main_column">
        <span class="help-tutorial-show"><i class="help-tutorial-icon icon-question-sign"></i>{__("help_tutorial.need_help")}</span>
        <span class="help-tutorial-close"><i class="help-tutorial-icon icon-remove"></i>{__("close")}</span>
    </span>
</div>
{/if}