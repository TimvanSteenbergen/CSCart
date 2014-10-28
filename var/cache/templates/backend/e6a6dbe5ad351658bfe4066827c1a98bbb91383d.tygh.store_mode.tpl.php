<?php /* Smarty version Smarty-3.1.18, created on 2014-10-27 16:10:19
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/views/settings/store_mode.tpl" */ ?>
<?php /*%%SmartyHeaderCode:86916242544e362ba8ce86-12298026%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e6a6dbe5ad351658bfe4066827c1a98bbb91383d' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/views/settings/store_mode.tpl',
      1 => 1413383305,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '86916242544e362ba8ce86-12298026',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'show' => 0,
    'store_mode_errors' => 0,
    'message' => 0,
    'config' => 0,
    'store_mode' => 0,
    'store_mode_license' => 0,
    'ldelim' => 0,
    'rdelim' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_544e362bae3213_65128454',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544e362bae3213_65128454')) {function content_544e362bae3213_65128454($_smarty_tpl) {?><?php
fn_preload_lang_vars(array('store_mode','choose_your_store_mode','full','text_store_mode_full','license_number','please_enter_license_here','trial','trial_mode_ult_disabled','trial_mode_mve_disabled','text_store_mode_trial','free','text_store_mode_free','select'));
?>
<?php if ($_smarty_tpl->tpl_vars['show']->value) {?>
    <a id="store_mode" class="cm-dialog-opener cm-dialog-auto-size hidden cm-dialog-non-closable" data-ca-target-id="store_mode_dialog"></a>
<?php }?>

<div class="hidden" title="<?php echo $_smarty_tpl->__("store_mode");?>
" id="store_mode_dialog">
    <?php if ($_smarty_tpl->tpl_vars['store_mode_errors']->value) {?>
        <div class="alert alert-error notification-content">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <?php  $_smarty_tpl->tpl_vars["message"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["message"]->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['store_mode_errors']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["message"]->key => $_smarty_tpl->tpl_vars["message"]->value) {
$_smarty_tpl->tpl_vars["message"]->_loop = true;
?>
            <strong><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['message']->value['title'], ENT_QUOTES, 'UTF-8');?>
:</strong> <?php echo $_smarty_tpl->tpl_vars['message']->value['text'];?>
<br>
        <?php } ?>
        </div>
    <?php }?>

    <form name="store_mode_form" action="<?php echo htmlspecialchars(fn_url(''), ENT_QUOTES, 'UTF-8');?>
" method="post">
    <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['config']->value['current_url'], ENT_QUOTES, 'UTF-8');?>
">
    
        <p class="choice-text"><?php echo $_smarty_tpl->__("choose_your_store_mode");?>
:</p>

            <ul class="store-mode inline">
                <li class="clickable <?php if ($_smarty_tpl->tpl_vars['store_mode_errors']->value) {?> type-error<?php }?> item<?php if ($_smarty_tpl->tpl_vars['store_mode']->value=="full") {?> active<?php }?>">
                    <label for="store_mode_radio_full" class="radio">
                        <input type="radio" id="store_mode_radio_full" name="store_mode" value="full" <?php if (!$_smarty_tpl->tpl_vars['store_mode']->value||$_smarty_tpl->tpl_vars['store_mode']->value=="full") {?>checked="checked"<?php }?> class="cm-switch-class"><?php echo $_smarty_tpl->__("full");?>
</label>
                    <p><?php echo $_smarty_tpl->__("text_store_mode_full");?>
</p>
                    <label for=""><?php echo $_smarty_tpl->__("license_number");?>
:</label>
                    <input type="text" name="license_number" class="<?php if ($_smarty_tpl->tpl_vars['store_mode_errors']->value) {?> type-error<?php }?>" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['store_mode_license']->value, ENT_QUOTES, 'UTF-8');?>
" placeholder="<?php echo $_smarty_tpl->__("please_enter_license_here");?>
">
                </li>

                <li class="<?php if ($_smarty_tpl->tpl_vars['store_mode']->value=="trial") {?>active<?php } elseif ($_smarty_tpl->tpl_vars['store_mode']->value) {?>disabled<?php }?>">
                    <label for="store_mode_radio_trial" class="radio">
                        <input type="radio" id="store_mode_radio_trial" name="store_mode" value="trial" <?php if ($_smarty_tpl->tpl_vars['store_mode']->value=="trial") {?>checked="checked"<?php }?> <?php if ($_smarty_tpl->tpl_vars['store_mode']->value!=''&&$_smarty_tpl->tpl_vars['store_mode']->value!="trial") {?>disabled="disabled"<?php }?>><?php echo $_smarty_tpl->__("trial");?>
</label>
                    <?php if ($_smarty_tpl->tpl_vars['store_mode']->value!=''&&$_smarty_tpl->tpl_vars['store_mode']->value!="trial") {?>
                        <?php if (fn_allowed_for("ULTIMATE")) {?>
                            <p><?php echo $_smarty_tpl->__("trial_mode_ult_disabled");?>
</p>
                        <?php } else { ?>
                            <p><?php echo $_smarty_tpl->__("trial_mode_mve_disabled");?>
</p>
                        <?php }?>
                    <?php } else { ?>
                        <p><?php echo $_smarty_tpl->__("text_store_mode_trial");?>
</p>
                    <?php }?>
                </li>

                <?php if (fn_allowed_for("ULTIMATE")) {?>
                    <li class="clickable <?php if ($_smarty_tpl->tpl_vars['store_mode']->value=="free") {?>active<?php }?>">
                        <label class="radio" for="store_mode_radio_free">
                            <input type="radio" id="store_mode_radio_free" name="store_mode" value="free" <?php if ($_smarty_tpl->tpl_vars['store_mode']->value=="free") {?>checked="checked"<?php }?>><?php echo $_smarty_tpl->__("free");?>
</label>
                        <p><?php echo $_smarty_tpl->__("text_store_mode_free");?>
</p>
                    </li>
                <?php }?>
            </ul>

        <div class="buttons-container">            
            <input name="dispatch[settings.change_store_mode]" type="submit" value="<?php echo $_smarty_tpl->__("select");?>
" class="btn btn-primary">
        </div>
    </form>
</div>

<script type="text/javascript">
Tygh.$(document).ready(function()<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['ldelim']->value, ENT_QUOTES, 'UTF-8');?>

    <?php if ($_smarty_tpl->tpl_vars['show']->value) {?>
        Tygh.$('#store_mode').trigger('click');
    <?php }?>

    Tygh.$(document).on('click', '#store_mode_dialog li:not(.disabled)', function(){
        $('#store_mode_dialog li').removeClass('active');
        $(this).addClass('active').find('input[type="radio"]').prop('checked', true);
    });
<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['rdelim']->value, ENT_QUOTES, 'UTF-8');?>
);
</script>
<?php }} ?>
