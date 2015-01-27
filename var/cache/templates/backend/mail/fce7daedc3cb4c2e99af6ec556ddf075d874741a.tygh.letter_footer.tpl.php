<?php /* Smarty version Smarty-3.1.18, created on 2014-11-24 17:17:13
         compiled from "/var/www/html/workspace/cscart/design/backend/mail/templates/common/letter_footer.tpl" */ ?>
<?php /*%%SmartyHeaderCode:192660292354733de90c0d28-56248305%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'fce7daedc3cb4c2e99af6ec556ddf075d874741a' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/mail/templates/common/letter_footer.tpl',
      1 => 1413383298,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '192660292354733de90c0d28-56248305',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'settings' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54733de90c7188_88685590',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54733de90c7188_88685590')) {function content_54733de90c7188_88685590($_smarty_tpl) {?><?php
fn_preload_lang_vars(array('admin_text_letter_footer'));
?>
<p>
    <?php echo $_smarty_tpl->__("admin_text_letter_footer",array("[company_name]"=>$_smarty_tpl->tpl_vars['settings']->value['Company']['company_name']));?>

</p>
</body>
</html><?php }} ?>
