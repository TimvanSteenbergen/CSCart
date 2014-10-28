<div class="modal signin-modal">
    <form action="{""|fn_url}" method="post" name="recover_form" class=" cm-skip-check-items cm-check-changes">
        <div class="modal-header">
            <h4><a href="{""|fn_url}">{$settings.Company.company_name|truncate:40:'...':true}</a></h4>
            <span>{__("recover_password")}</span>
        </div>
        <div class="modal-body">
            <p>{__("text_recover_password_notice")}</p>
            <label for="user_login">{__("email")}:</label>
            <input type="text" name="user_email" id="user_login" size="20" value="">
        </div>
        <div class="modal-footer">
            {include file="buttons/button.tpl" but_text=__("reset_password") but_name="dispatch[auth.recover_password]" but_role="button_main"}
        </div>
    </form>
</div>