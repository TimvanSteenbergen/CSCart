<div id="theme_upload_container">
    <form action="{""|fn_url}" method="post" name="addon_upload_form" class="form-horizontal cm-ajax" enctype="multipart/form-data">
        <input type="hidden" name="result_ids" value="theme_upload_container" />

        <fieldset>
        	<div class="control-group">
	            <strong class="text-error">{__("non_writable_directories")}:</strong>
	            <ol class="text-error">
	            {foreach $non_writable as $dir => $perm}
	            	<li>{$dir}</li>
	            {/foreach}
	            </ol>
            </div>

            {include file="common/subheader.tpl" title=__("ftp_server_options")}
            <div class="control-group">
            	<label for="host" class="control-label">{__("host")}:</label>
		        <div class="controls">
		            <input id="host" type="text" name="ftp_access[ftp_hostname]" size="30" value="{$ftp_access.ftp_hostname}" class="input-text" />
		        </div>
            </div>

            <div class="control-group">
            	<label for="login" class="control-label">{__("login")}:</label>
		        <div class="controls">
		            <input id="login" type="text" name="ftp_access[ftp_username]" size="30" value="{$ftp_access.ftp_username}" class="input-text" />
		        </div>
            </div>

            <div class="control-group">
            	<label for="password" class="control-label">{__("password")}:</label>
		        <div class="controls">
		            <input id="password" type="password" name="ftp_access[ftp_password]" size="30" value="{$ftp_access.ftp_password}" class="input-text" />
		        </div>
		    </div>

		    <div class="control-group">
            	<label for="base_path" class="control-label">{__("ftp_directory")}:</label>
		        <div class="controls">
		            <input id="base_path" type="text" name="ftp_access[ftp_directory]" size="30" value="{$ftp_access.ftp_directory|default:$config.http_path}" class="input-text" />
		        </div>
		    </div>

            <div class="buttons-container">
                <a class="cm-dialog-closer cm-cancel tool-link btn">{__("cancel")}</a>
                {include file="buttons/button.tpl" but_role="submit" but_text=__("recheck") but_name="dispatch[themes.recheck]"}
                {include file="buttons/button.tpl" but_role="submit" but_text=__("upload_via_ftp") but_name="dispatch[themes.recheck.ftp_upload]"}
            </div>

        </fieldset>
    </form>
<!--theme_upload_container--></div>