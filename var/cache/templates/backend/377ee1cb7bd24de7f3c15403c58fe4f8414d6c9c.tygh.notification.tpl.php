<?php /* Smarty version Smarty-3.1.18, created on 2014-10-27 16:10:19
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/common/notification.tpl" */ ?>
<?php /*%%SmartyHeaderCode:436047036544e362b1dd746-16739811%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '377ee1cb7bd24de7f3c15403c58fe4f8414d6c9c' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/common/notification.tpl',
      1 => 1413383301,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '436047036544e362b1dd746-16739811',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'message' => 0,
    'key' => 0,
    'view_mode' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_544e362b20d960_54655789',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544e362b20d960_54655789')) {function content_544e362b20d960_54655789($_smarty_tpl) {?><?php if (!defined("AJAX_REQUEST")) {?>

<?php $_smarty_tpl->_capture_stack[0][] = array("notification_content", null, null); ob_start(); ?>
<?php  $_smarty_tpl->tpl_vars["message"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["message"]->_loop = false;
 $_smarty_tpl->tpl_vars["key"] = new Smarty_Variable;
 $_from = fn_get_notifications(''); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["message"]->key => $_smarty_tpl->tpl_vars["message"]->value) {
$_smarty_tpl->tpl_vars["message"]->_loop = true;
 $_smarty_tpl->tpl_vars["key"]->value = $_smarty_tpl->tpl_vars["message"]->key;
?><?php if ($_smarty_tpl->tpl_vars['message']->value['type']=="I") {?><div class="cm-notification-content cm-notification-content-extended notification-content-extended <?php if ($_smarty_tpl->tpl_vars['message']->value['message_state']=="I") {?> cm-auto-hide<?php }?>" data-ca-notification-key="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['key']->value, ENT_QUOTES, 'UTF-8');?>
"><h1><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['message']->value['title'], ENT_QUOTES, 'UTF-8');?>
<span class="cm-notification-close close <?php if ($_smarty_tpl->tpl_vars['message']->value['message_state']=="S") {?> cm-notification-close-ajax<?php }?>"></span></h1><div class="notification-body-extended"><?php echo $_smarty_tpl->tpl_vars['message']->value['message'];?>
</div></div><?php } else { ?><div class="alert cm-notification-content<?php if ($_smarty_tpl->tpl_vars['message']->value['type']=="N") {?> alert-success<?php } elseif ($_smarty_tpl->tpl_vars['message']->value['type']=="W") {?> alert-warning<?php } elseif ($_smarty_tpl->tpl_vars['message']->value['type']=="E") {?> alert-error<?php } elseif ($_smarty_tpl->tpl_vars['message']->value['type']=="S") {?> alert-info<?php }?> <?php if ($_smarty_tpl->tpl_vars['message']->value['message_state']=="I") {?> cm-auto-hide<?php }?>" id="notification_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['key']->value, ENT_QUOTES, 'UTF-8');?>
" data-ca-notification-key="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['key']->value, ENT_QUOTES, 'UTF-8');?>
"><button type="button" class="close cm-notification-close<?php if ($_smarty_tpl->tpl_vars['message']->value['message_state']=="S") {?> cm-notification-close-ajax<?php }?>" <?php if ($_smarty_tpl->tpl_vars['message']->value['message_state']!="S") {?>data-dismiss="alert"<?php }?>>×</button><strong><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['message']->value['title'], ENT_QUOTES, 'UTF-8');?>
</strong><?php echo $_smarty_tpl->tpl_vars['message']->value['message'];?>
</div><?php }?><?php } ?>
<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>

<?php if ($_smarty_tpl->tpl_vars['view_mode']->value=="simple") {?>
    <?php echo Smarty::$_smarty_vars['capture']['notification_content'];?>

<?php }?>

<div class="cm-notification-container alert-wrap <?php if ($_smarty_tpl->tpl_vars['view_mode']->value=="simple") {?>notification-container-top<?php }?>">
    <?php if ($_smarty_tpl->tpl_vars['view_mode']->value!="simple") {?>
        <?php echo Smarty::$_smarty_vars['capture']['notification_content'];?>

    <?php }?>
</div>

<?php }?>
<?php }} ?>
