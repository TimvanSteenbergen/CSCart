<div id="template_list_menu" class="ty-template-list-menu"><div></div><ul class="ty-float-left"><li></li></ul></div>

<div id="template_editor_content" title="{__("file_editor")}" class="hidden">

    <div class="ty-templates clearfix">
            <div class="ty-templates__tree">
                <h4 class="ty-templates__tree-title">{__("templates_tree")}</h4>
                <div class="ty-templates__tree-wrapper">
                    <ul id="template_list" class="ty-templates__list">
                        <li></li>
                    </ul>
                </div>
            </div>
            <div class="ty-templates__content">
                <div id="template_text" class="ty-templates__text"></div>
            </div>
    </div>

    <div class="ty-templates__buttons buttons-container">
        {include file="buttons/add_close.tpl" is_js=true but_close_text=__("save") but_close_onclick="fn_save_template();" but_onclick="fn_restore_template();" but_text=__("restore_from_repository")}
    </div>

</div>

{script src="js/lib/ace/ace.js"}
{script src="js/tygh/design_mode.js"}

<script type="text/javascript">
var current_url = '{$config.current_url}';
Tygh.tr('text_page_changed', '{__("text_page_changed")|escape:"javascript"}');
Tygh.tr('text_restore_question', '{__("text_restore_question")|escape:"javascript"}');
Tygh.tr('text_template_changed', '{__("text_template_changed")|escape:"javascript"}');
</script>
