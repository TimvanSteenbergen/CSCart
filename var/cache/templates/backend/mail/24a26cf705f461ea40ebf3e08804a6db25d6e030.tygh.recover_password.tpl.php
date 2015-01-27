<?php /* Smarty version Smarty-3.1.18, created on 2014-11-24 17:17:13
         compiled from "/var/www/html/workspace/cscart/design/backend/mail/templates/profiles/recover_password.tpl" */ ?>
<?php /*%%SmartyHeaderCode:117128662454733de90a7b36-39303358%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '24a26cf705f461ea40ebf3e08804a6db25d6e030' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/mail/templates/profiles/recover_password.tpl',
      1 => 1413383298,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '117128662454733de90a7b36-39303358',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'ekey' => 0,
    'zone' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54733de90b75e7_12712905',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54733de90b75e7_12712905')) {function content_54733de90b75e7_12712905($_smarty_tpl) {?><?php
fn_preload_lang_vars(array('text_confirm_passwd_recovery'));
?>
<?php echo $_smarty_tpl->getSubTemplate ("common/letter_header.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>


<?php echo $_smarty_tpl->__("text_confirm_passwd_recovery");?>
:<br /><br />

<a href="<?php echo htmlspecialchars(fn_url("auth.recover_password?ekey=".((string)$_smarty_tpl->tpl_vars['ekey']->value),$_smarty_tpl->tpl_vars['zone']->value,'http'), ENT_QUOTES, 'UTF-8');?>
"><?php echo htmlspecialchars(fn_url("auth.recover_password?ekey=".((string)$_smarty_tpl->tpl_vars['ekey']->value),$_smarty_tpl->tpl_vars['zone']->value,'http'), ENT_QUOTES, 'UTF-8');?>
</a>

<?php echo $_smarty_tpl->getSubTemplate ("common/letter_footer.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>
<?php }} ?>
