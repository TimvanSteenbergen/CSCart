<?php /* Smarty version Smarty-3.1.18, created on 2015-01-27 21:13:56
         compiled from "/var/www/html/workspace/cscart/design/themes/responsive/templates/addons/hybrid_auth/hooks/auth_info/extra.post.tpl" */ ?>
<?php /*%%SmartyHeaderCode:134292808354c7d5645b1d92-13655575%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '640e27d5483d63f695546e44beb915ebd49900b4' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/themes/responsive/templates/addons/hybrid_auth/hooks/auth_info/extra.post.tpl',
      1 => 1414411816,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '134292808354c7d5645b1d92-13655575',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'runtime' => 0,
    'auth' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54c7d5645da780_94674550',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54c7d5645da780_94674550')) {function content_54c7d5645da780_94674550($_smarty_tpl) {?><?php if (!is_callable('smarty_function_set_id')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/function.set_id.php';
?><?php
fn_preload_lang_vars(array('text_hybrid_auth.connect_social_title','text_hybrid_auth.connect_social','text_hybrid_auth.specify_email_title','text_hybrid_auth.specify_email','text_hybrid_auth.connect_social_title','text_hybrid_auth.connect_social','text_hybrid_auth.specify_email_title','text_hybrid_auth.specify_email'));
?>
<?php if ($_smarty_tpl->tpl_vars['runtime']->value['customization_mode']['design']=="Y"&&@constant('AREA')=="C") {?><?php $_smarty_tpl->_capture_stack[0][] = array("template_content", null, null); ob_start(); ?><?php if ($_smarty_tpl->tpl_vars['runtime']->value['controller']=="auth") {?>
    <?php if ($_smarty_tpl->tpl_vars['runtime']->value['mode']=="connect_social") {?>
    <h4 class="ty-login-info__title"><?php echo $_smarty_tpl->__("text_hybrid_auth.connect_social_title");?>
</h4>
    <div class="ty-login-info__txt"><?php echo $_smarty_tpl->__("text_hybrid_auth.connect_social");?>
</div>
    <?php }?>
    <?php if ($_smarty_tpl->tpl_vars['runtime']->value['mode']=="specify_email") {?>
        <h4 class="ty-login-info__title"><?php echo $_smarty_tpl->__("text_hybrid_auth.specify_email_title");?>
</h4>
        <div class="ty-login-info__txt"><?php echo $_smarty_tpl->__("text_hybrid_auth.specify_email");?>
</div>
    <?php }?>
<?php }?>

<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?><?php if (trim(Smarty::$_smarty_vars['capture']['template_content'])) {?><?php if ($_smarty_tpl->tpl_vars['auth']->value['area']=="A") {?><span class="cm-template-box template-box" data-ca-te-template="addons/hybrid_auth/hooks/auth_info/extra.post.tpl" id="<?php echo smarty_function_set_id(array('name'=>"addons/hybrid_auth/hooks/auth_info/extra.post.tpl"),$_smarty_tpl);?>
"><div class="cm-template-icon icon-edit ty-icon-edit hidden"></div><?php echo Smarty::$_smarty_vars['capture']['template_content'];?>
<!--[/tpl_id]--></span><?php } else { ?><?php echo Smarty::$_smarty_vars['capture']['template_content'];?>
<?php }?><?php }?><?php } else { ?><?php if ($_smarty_tpl->tpl_vars['runtime']->value['controller']=="auth") {?>
    <?php if ($_smarty_tpl->tpl_vars['runtime']->value['mode']=="connect_social") {?>
    <h4 class="ty-login-info__title"><?php echo $_smarty_tpl->__("text_hybrid_auth.connect_social_title");?>
</h4>
    <div class="ty-login-info__txt"><?php echo $_smarty_tpl->__("text_hybrid_auth.connect_social");?>
</div>
    <?php }?>
    <?php if ($_smarty_tpl->tpl_vars['runtime']->value['mode']=="specify_email") {?>
        <h4 class="ty-login-info__title"><?php echo $_smarty_tpl->__("text_hybrid_auth.specify_email_title");?>
</h4>
        <div class="ty-login-info__txt"><?php echo $_smarty_tpl->__("text_hybrid_auth.specify_email");?>
</div>
    <?php }?>
<?php }?>

<?php }?><?php }} ?>
