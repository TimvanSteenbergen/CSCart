{if $page_type == $smarty.const.PAGE_TYPE_POLL}
    <div id="content_poll" class="form-horizontal">

        <div class="control-group">
            <label for="poll_show_results" class="control-label">{__("poll_show_results")}:</label>
            <div class="controls">
                <select name="page_data[poll_data][show_results]" id="poll_show_results">
                    <option value="N" {if $page_data.poll.show_results == "N"}selected="selected"{/if}>{__("poll_results_nobody")}</option>
                    <option value="V" {if $page_data.poll.show_results == "V"}selected="selected"{/if}>{__("poll_results_voted")}</option>
                    <option value="E" {if $page_data.poll.show_results == "E"}selected="selected"{/if}>{__("poll_results_everybody")}</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label for="poll_header" class="control-label">{__("poll_header")}:</label>
            <div class="controls">
                <textarea name="page_data[poll_data][header]" id="poll_header" cols="50" rows="5" class="cm-wysiwyg input-textarea-long input-fill">{$page_data.poll.header}</textarea>
            </div>
        </div>

        <div class="control-group">
            <label for="poll_footer" class="control-label">{__("poll_footer")}:</label>
            <div class="controls">
                <textarea name="page_data[poll_data][footer]" id="poll_footer" cols="50" rows="5" class="cm-wysiwyg input-textarea-long input-fill">{$page_data.poll.footer}</textarea>
            </div>
        </div>

        <div class="control-group">
            <label for="poll_results" class="control-label">{__("poll_results")}:</label>
            <div class="controls">
                <textarea name="page_data[poll_data][results]" id="poll_results" cols="50" rows="5" class="cm-wysiwyg input-textarea-long input-fill">{$page_data.poll.results}</textarea>
            </div>
        </div>

    </div>
{/if}