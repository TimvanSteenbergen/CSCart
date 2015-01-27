<div class="install-addon" id="theme_upload_container">
    <form action="{""|fn_url}" method="post" name="addon_upload_form" class="form-horizontal cm-ajax" enctype="multipart/form-data">
        <input type="hidden" name="result_ids" value="theme_upload_container" />
        <div class="install-addon-wrapper">
            <img class="install-addon-banner" src="{$images_dir}/addon_box.png" width="151px" height="141px" />

            <p class="install-addon-text">{__("install_theme_text", ['[exts]' => implode(',', $config.allowed_pack_exts)])}</p>
            {include file="common/fileuploader.tpl" var_name="theme_pack[0]"}

            <div class="marketplace">
                <p class="marketplace-link"> {__("marketplace_find_more", ["[href]" => $config.resources.marketplace_url])} </p>
            </div>

        </div>

        <div class="buttons-container">
            {include file="buttons/save_cancel.tpl" but_name="dispatch[themes.upload]" cancel_action="close" but_text=__("upload")}
        </div>
    </form>
<!--theme_upload_container--></div>
