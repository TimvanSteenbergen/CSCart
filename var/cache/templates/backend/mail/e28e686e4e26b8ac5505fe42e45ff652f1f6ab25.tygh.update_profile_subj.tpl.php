<?php /* Smarty version Smarty-3.1.18, created on 2014-11-24 17:20:04
         compiled from "/var/www/html/workspace/cscart/design/backend/mail/templates/profiles/update_profile_subj.tpl" */ ?>
<?php /*%%SmartyHeaderCode:173040153454733e94dbb2f1-77519154%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e28e686e4e26b8ac5505fe42e45ff652f1f6ab25' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/mail/templates/profiles/update_profile_subj.tpl',
      1 => 1413383298,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '173040153454733e94dbb2f1-77519154',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'company_data' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54733e94dce2f7_47847611',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54733e94dce2f7_47847611')) {function content_54733e94dce2f7_47847611($_smarty_tpl) {?><?php
fn_preload_lang_vars(array('update_profile_notification'));
?>
<?php echo $_smarty_tpl->tpl_vars['company_data']->value['company_name'];?>
: <?php echo $_smarty_tpl->__("update_profile_notification");?>
<?php }} ?>
