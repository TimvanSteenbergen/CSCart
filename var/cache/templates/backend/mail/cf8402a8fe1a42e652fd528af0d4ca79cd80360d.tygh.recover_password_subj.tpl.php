<?php /* Smarty version Smarty-3.1.18, created on 2014-11-24 17:17:13
         compiled from "/var/www/html/workspace/cscart/design/backend/mail/templates/profiles/recover_password_subj.tpl" */ ?>
<?php /*%%SmartyHeaderCode:94117602054733de908b2b3-82209487%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'cf8402a8fe1a42e652fd528af0d4ca79cd80360d' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/mail/templates/profiles/recover_password_subj.tpl',
      1 => 1413383298,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '94117602054733de908b2b3-82209487',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'company_data' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54733de909ac67_12251640',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54733de909ac67_12251640')) {function content_54733de909ac67_12251640($_smarty_tpl) {?><?php
fn_preload_lang_vars(array('recover_password_subj'));
?>
<?php echo $_smarty_tpl->tpl_vars['company_data']->value['company_name'];?>
: <?php echo $_smarty_tpl->__("recover_password_subj");?>
<?php }} ?>
