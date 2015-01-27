<?php /* Smarty version Smarty-3.1.18, created on 2014-10-28 14:22:02
         compiled from "/var/www/html/workspace/cscart/design/themes/responsive/templates/addons/discussion/views/discussion/components/stars.tpl" */ ?>
<?php /*%%SmartyHeaderCode:668805315544f6e4a821871-80817457%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '9e102184b880e5cd3d5791ba23d62edfff32db35' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/themes/responsive/templates/addons/discussion/views/discussion/components/stars.tpl',
      1 => 1414411815,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '668805315544f6e4a821871-80817457',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'runtime' => 0,
    'is_link' => 0,
    'product' => 0,
    'stars' => 0,
    'auth' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_544f6e4a875838_26703826',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544f6e4a875838_26703826')) {function content_544f6e4a875838_26703826($_smarty_tpl) {?><?php if (!is_callable('smarty_function_set_id')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/function.set_id.php';
?><?php if ($_smarty_tpl->tpl_vars['runtime']->value['customization_mode']['design']=="Y"&&@constant('AREA')=="C") {?><?php $_smarty_tpl->_capture_stack[0][] = array("template_content", null, null); ob_start(); ?><span class="ty-nowrap ty-stars">
<?php if ($_smarty_tpl->tpl_vars['is_link']->value) {?><?php if ($_smarty_tpl->tpl_vars['runtime']->value['mode']=="view") {?><a class="cm-external-click" data-ca-scroll="content_discussion" data-ca-external-click-id="discussion"><?php } else { ?><a href="<?php echo htmlspecialchars(fn_url("products.view?product_id=".((string)$_smarty_tpl->tpl_vars['product']->value['product_id'])."&selected_section=discussion#discussion"), ENT_QUOTES, 'UTF-8');?>
"><?php }?><?php }?>
<?php if (isset($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"])) unset($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]);
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['name'] = "full_star";
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['loop'] = is_array($_loop=$_smarty_tpl->tpl_vars['stars']->value['full']) ? count($_loop) : max(0, (int) $_loop); unset($_loop);
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['show'] = true;
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['max'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['loop'];
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['step'] = 1;
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['start'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['step'] > 0 ? 0 : $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['loop']-1;
if ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['show']) {
    $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['total'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['loop'];
    if ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['total'] == 0)
        $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['show'] = false;
} else
    $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['total'] = 0;
if ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['show']):

            for ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['start'], $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration'] = 1;
                 $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration'] <= $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['total'];
                 $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index'] += $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['step'], $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration']++):
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['rownum'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration'];
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index_prev'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index'] - $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['step'];
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index_next'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index'] + $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['step'];
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['first']      = ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration'] == 1);
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['last']       = ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration'] == $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['total']);
?><i class="ty-stars__icon ty-icon-star"></i><?php endfor; endif; ?>
<?php if ($_smarty_tpl->tpl_vars['stars']->value['part']) {?><i class="ty-stars__icon ty-icon-star-half"></i><?php }?>
<?php if (isset($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"])) unset($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]);
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['name'] = "full_star";
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['loop'] = is_array($_loop=$_smarty_tpl->tpl_vars['stars']->value['empty']) ? count($_loop) : max(0, (int) $_loop); unset($_loop);
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['show'] = true;
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['max'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['loop'];
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['step'] = 1;
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['start'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['step'] > 0 ? 0 : $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['loop']-1;
if ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['show']) {
    $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['total'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['loop'];
    if ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['total'] == 0)
        $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['show'] = false;
} else
    $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['total'] = 0;
if ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['show']):

            for ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['start'], $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration'] = 1;
                 $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration'] <= $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['total'];
                 $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index'] += $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['step'], $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration']++):
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['rownum'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration'];
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index_prev'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index'] - $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['step'];
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index_next'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index'] + $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['step'];
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['first']      = ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration'] == 1);
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['last']       = ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration'] == $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['total']);
?><i class="ty-stars__icon ty-icon-star-empty"></i><?php endfor; endif; ?>
<?php if ($_smarty_tpl->tpl_vars['is_link']->value) {?></a><?php }?>
</span><?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?><?php if (trim(Smarty::$_smarty_vars['capture']['template_content'])) {?><?php if ($_smarty_tpl->tpl_vars['auth']->value['area']=="A") {?><span class="cm-template-box template-box" data-ca-te-template="addons/discussion/views/discussion/components/stars.tpl" id="<?php echo smarty_function_set_id(array('name'=>"addons/discussion/views/discussion/components/stars.tpl"),$_smarty_tpl);?>
"><div class="cm-template-icon icon-edit ty-icon-edit hidden"></div><?php echo Smarty::$_smarty_vars['capture']['template_content'];?>
<!--[/tpl_id]--></span><?php } else { ?><?php echo Smarty::$_smarty_vars['capture']['template_content'];?>
<?php }?><?php }?><?php } else { ?><span class="ty-nowrap ty-stars">
<?php if ($_smarty_tpl->tpl_vars['is_link']->value) {?><?php if ($_smarty_tpl->tpl_vars['runtime']->value['mode']=="view") {?><a class="cm-external-click" data-ca-scroll="content_discussion" data-ca-external-click-id="discussion"><?php } else { ?><a href="<?php echo htmlspecialchars(fn_url("products.view?product_id=".((string)$_smarty_tpl->tpl_vars['product']->value['product_id'])."&selected_section=discussion#discussion"), ENT_QUOTES, 'UTF-8');?>
"><?php }?><?php }?>
<?php if (isset($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"])) unset($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]);
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['name'] = "full_star";
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['loop'] = is_array($_loop=$_smarty_tpl->tpl_vars['stars']->value['full']) ? count($_loop) : max(0, (int) $_loop); unset($_loop);
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['show'] = true;
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['max'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['loop'];
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['step'] = 1;
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['start'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['step'] > 0 ? 0 : $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['loop']-1;
if ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['show']) {
    $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['total'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['loop'];
    if ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['total'] == 0)
        $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['show'] = false;
} else
    $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['total'] = 0;
if ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['show']):

            for ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['start'], $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration'] = 1;
                 $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration'] <= $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['total'];
                 $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index'] += $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['step'], $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration']++):
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['rownum'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration'];
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index_prev'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index'] - $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['step'];
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index_next'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index'] + $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['step'];
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['first']      = ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration'] == 1);
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['last']       = ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration'] == $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['total']);
?><i class="ty-stars__icon ty-icon-star"></i><?php endfor; endif; ?>
<?php if ($_smarty_tpl->tpl_vars['stars']->value['part']) {?><i class="ty-stars__icon ty-icon-star-half"></i><?php }?>
<?php if (isset($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"])) unset($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]);
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['name'] = "full_star";
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['loop'] = is_array($_loop=$_smarty_tpl->tpl_vars['stars']->value['empty']) ? count($_loop) : max(0, (int) $_loop); unset($_loop);
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['show'] = true;
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['max'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['loop'];
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['step'] = 1;
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['start'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['step'] > 0 ? 0 : $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['loop']-1;
if ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['show']) {
    $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['total'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['loop'];
    if ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['total'] == 0)
        $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['show'] = false;
} else
    $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['total'] = 0;
if ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['show']):

            for ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['start'], $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration'] = 1;
                 $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration'] <= $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['total'];
                 $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index'] += $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['step'], $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration']++):
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['rownum'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration'];
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index_prev'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index'] - $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['step'];
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index_next'] = $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['index'] + $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['step'];
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['first']      = ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration'] == 1);
$_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['last']       = ($_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['iteration'] == $_smarty_tpl->tpl_vars['smarty']->value['section']["full_star"]['total']);
?><i class="ty-stars__icon ty-icon-star-empty"></i><?php endfor; endif; ?>
<?php if ($_smarty_tpl->tpl_vars['is_link']->value) {?></a><?php }?>
</span><?php }?><?php }} ?>
