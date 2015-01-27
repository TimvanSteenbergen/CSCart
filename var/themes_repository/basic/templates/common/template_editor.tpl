<div id="template_list_menu"><div></div><ul class="float-left"><li></li></ul></div>

<div id="template_editor_content" title="{__("file_editor")}" class="hidden">

    <table class="editor-table table-width">
        <tr class="max-height valign-top">
            <td class="templates-tree max-height">
                <h4>{__("templates_tree")}</h4>
                <div>
                    <ul id="template_list" class="template-list">
                        <li></li>
                    </ul>
                </div>
            </td>
            <td>
                <div id="template_text"></div>
            </td>
        </tr>
    </table>

    <div class="buttons-container">
        {include file="buttons/add_close.tpl" is_js=true but_close_text=__("save") but_close_onclick="fn_save_template();" but_onclick="fn_restore_template();" but_text=__("restore_from_repository")}
    </div>

</div>

{script src="js/lib/ace/ace.js"}
{script src="js/tygh/design_mode.js"}

<script type="text/javascript">
//<![CDATA[
var current_url = '{$config.current_url}';
Tygh.tr('text_page_changed', '{__("text_page_changed")|escape:"javascript"}');
Tygh.tr('text_restore_question', '{__("text_restore_question")|escape:"javascript"}');
Tygh.tr('text_template_changed', '{__("text_template_changed")|escape:"javascript"}');
//]]>
</script>
