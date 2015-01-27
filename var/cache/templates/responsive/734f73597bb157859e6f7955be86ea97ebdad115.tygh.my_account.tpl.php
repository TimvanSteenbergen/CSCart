<?php /* Smarty version Smarty-3.1.18, created on 2014-10-28 14:21:59
         compiled from "/var/www/html/workspace/cscart/design/themes/responsive/templates/blocks/my_account.tpl" */ ?>
<?php /*%%SmartyHeaderCode:949503742544f6e47a4f376-49857590%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '734f73597bb157859e6f7955be86ea97ebdad115' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/themes/responsive/templates/blocks/my_account.tpl',
      1 => 1414411814,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '949503742544f6e47a4f376-49857590',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'runtime' => 0,
    'block' => 0,
    'title' => 0,
    'config' => 0,
    'auth' => 0,
    'user_info' => 0,
    'settings' => 0,
    'user_data' => 0,
    'compared_products' => 0,
    'return_current_url' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_544f6e47b97639_22107133',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544f6e47b97639_22107133')) {function content_544f6e47b97639_22107133($_smarty_tpl) {?><?php if (!is_callable('smarty_function_live_edit')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/function.live_edit.php';
if (!is_callable('smarty_block_hook')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/block.hook.php';
if (!is_callable('smarty_function_set_id')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/function.set_id.php';
?><?php
fn_preload_lang_vars(array('profile_details','downloads','orders','view_compare_list','apply_for_vendor_account','track_my_order','track_my_order','order_id','email','go','sign_out','sign_in','register','sign_in','profile_details','downloads','orders','view_compare_list','apply_for_vendor_account','track_my_order','track_my_order','order_id','email','go','sign_out','sign_in','register','sign_in'));
?>
<?php if ($_smarty_tpl->tpl_vars['runtime']->value['customization_mode']['design']=="Y"&&@constant('AREA')=="C") {?><?php $_smarty_tpl->_capture_stack[0][] = array("template_content", null, null); ob_start(); ?>

<?php $_smarty_tpl->_capture_stack[0][] = array("title", null, null); ob_start(); ?>
    <a class="ty-account-info__title" href="<?php echo htmlspecialchars(fn_url("profiles.update"), ENT_QUOTES, 'UTF-8');?>
">
        <i class="ty-icon-user"></i>&nbsp;
        <span class="hidden-phone" <?php echo smarty_function_live_edit(array('name'=>"block:name:".((string)$_smarty_tpl->tpl_vars['block']->value['block_id'])),$_smarty_tpl);?>
><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['title']->value, ENT_QUOTES, 'UTF-8');?>
</span>
        <i class="ty-icon-down-micro ty-account-info__user-arrow"></i>
    </a>
<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>

<div id="account_info_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['block']->value['snapping_id'], ENT_QUOTES, 'UTF-8');?>
">
    <?php $_smarty_tpl->tpl_vars["return_current_url"] = new Smarty_variable(rawurlencode($_smarty_tpl->tpl_vars['config']->value['current_url']), null, 0);?>
    <ul class="ty-account-info">
        <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"profiles:my_account_menu")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"profiles:my_account_menu"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

            <?php if ($_smarty_tpl->tpl_vars['auth']->value['user_id']) {?>
                <?php if ($_smarty_tpl->tpl_vars['user_info']->value['firstname']||$_smarty_tpl->tpl_vars['user_info']->value['lastname']) {?>
                    <li class="ty-account-info__item  ty-account-info__name ty-dropdown-box__item"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['user_info']->value['firstname'], ENT_QUOTES, 'UTF-8');?>
 <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['user_info']->value['lastname'], ENT_QUOTES, 'UTF-8');?>
</li>
                <?php } else { ?>
                    <?php if ($_smarty_tpl->tpl_vars['settings']->value['General']['use_email_as_login']=='Y') {?>
                        <li class="ty-account-info__item ty-dropdown-box__item ty-account-info__name"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['user_info']->value['email'], ENT_QUOTES, 'UTF-8');?>
</li>
                    <?php } else { ?>
                        <li class="ty-account-info__item ty-dropdown-box__item ty-account-info__name"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['user_info']->value['user_login'], ENT_QUOTES, 'UTF-8');?>
</li>
                    <?php }?>
                <?php }?>
                <li class="ty-account-info__item ty-dropdown-box__item"><a class="ty-account-info__a underlined" href="<?php echo htmlspecialchars(fn_url("profiles.update"), ENT_QUOTES, 'UTF-8');?>
" rel="nofollow" ><?php echo $_smarty_tpl->__("profile_details");?>
</a></li>
                <li class="ty-account-info__item ty-dropdown-box__item"><a class="ty-account-info__a underlined" href="<?php echo htmlspecialchars(fn_url("orders.downloads"), ENT_QUOTES, 'UTF-8');?>
" rel="nofollow"><?php echo $_smarty_tpl->__("downloads");?>
</a></li>
            <?php } elseif ($_smarty_tpl->tpl_vars['user_data']->value['firstname']||$_smarty_tpl->tpl_vars['user_data']->value['lastname']) {?>
                <li class="ty-account-info__item  ty-dropdown-box__item ty-account-info__name"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['user_data']->value['firstname'], ENT_QUOTES, 'UTF-8');?>
 <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['user_data']->value['lastname'], ENT_QUOTES, 'UTF-8');?>
</li>
            <?php } elseif ($_smarty_tpl->tpl_vars['settings']->value['General']['use_email_as_login']=='Y'&&$_smarty_tpl->tpl_vars['user_data']->value['email']) {?>
                <li class="ty-account-info__item ty-dropdown-box__item ty-account-info__name"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['user_data']->value['email'], ENT_QUOTES, 'UTF-8');?>
</li>
            <?php } elseif ($_smarty_tpl->tpl_vars['settings']->value['General']['use_email_as_login']!='Y'&&$_smarty_tpl->tpl_vars['user_data']->value['user_login']) {?>
                <li class="ty-account-info__item ty-dropdown-box__item ty-account-info__name"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['user_data']->value['user_login'], ENT_QUOTES, 'UTF-8');?>
</li>
            <?php }?>
            <li class="ty-account-info__item ty-dropdown-box__item"><a class="ty-account-info__a underlined" href="<?php echo htmlspecialchars(fn_url("orders.search"), ENT_QUOTES, 'UTF-8');?>
" rel="nofollow"><?php echo $_smarty_tpl->__("orders");?>
</a></li>
            <?php $_smarty_tpl->tpl_vars["compared_products"] = new Smarty_variable(fn_get_comparison_products(''), null, 0);?>
            <li class="ty-account-info__item ty-dropdown-box__item"><a class="ty-account-info__a underlined" href="<?php echo htmlspecialchars(fn_url("product_features.compare"), ENT_QUOTES, 'UTF-8');?>
" rel="nofollow"><?php echo $_smarty_tpl->__("view_compare_list");?>
<?php if ($_smarty_tpl->tpl_vars['compared_products']->value) {?> (<?php echo htmlspecialchars(count($_smarty_tpl->tpl_vars['compared_products']->value), ENT_QUOTES, 'UTF-8');?>
)<?php }?></a></li>
        <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"profiles:my_account_menu"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>


        <?php if (fn_allowed_for("MULTIVENDOR")&&$_smarty_tpl->tpl_vars['settings']->value['Vendors']['apply_for_vendor']=="Y"&&!$_smarty_tpl->tpl_vars['user_info']->value['company_id']) {?>
            <li class="ty-account-info__item ty-dropdown-box__item"><a class="ty-account-info__a underlined" href="<?php echo htmlspecialchars(fn_url("companies.apply_for_vendor?return_previous_url=".((string)$_smarty_tpl->tpl_vars['return_current_url']->value)), ENT_QUOTES, 'UTF-8');?>
" rel="nofollow"><?php echo $_smarty_tpl->__("apply_for_vendor_account");?>
</a></li>
        <?php }?>
    </ul>

    <?php if ($_smarty_tpl->tpl_vars['settings']->value['Appearance']['display_track_orders']=='Y') {?>
        <div class="ty-account-info__orders updates-wrapper track-orders" id="track_orders_block_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['block']->value['snapping_id'], ENT_QUOTES, 'UTF-8');?>
">
            <form action="<?php echo htmlspecialchars(fn_url(''), ENT_QUOTES, 'UTF-8');?>
" method="get" class="cm-ajax cm-ajax-full-render" name="track_order_quick">
                <input type="hidden" name="result_ids" value="track_orders_block_*" />
                <input type="hidden" name="return_url" value="<?php echo htmlspecialchars((($tmp = @$_REQUEST['return_url'])===null||$tmp==='' ? $_smarty_tpl->tpl_vars['config']->value['current_url'] : $tmp), ENT_QUOTES, 'UTF-8');?>
" />

                <div class="ty-account-info__orders-txt"><?php echo $_smarty_tpl->__("track_my_order");?>
</div>

                <div class="ty-account-info__orders-input ty-control-group ty-input-append">
                    <label for="track_order_item<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['block']->value['snapping_id'], ENT_QUOTES, 'UTF-8');?>
" class="cm-required hidden"><?php echo $_smarty_tpl->__("track_my_order");?>
</label>
                    <input type="text" size="20" class="ty-input-text cm-hint" id="track_order_item<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['block']->value['snapping_id'], ENT_QUOTES, 'UTF-8');?>
" name="track_data" value="<?php echo $_smarty_tpl->__("order_id");?>
<?php if (!$_smarty_tpl->tpl_vars['auth']->value['user_id']) {?>/<?php echo $_smarty_tpl->__("email");?>
<?php }?>" />
                    <?php echo $_smarty_tpl->getSubTemplate ("buttons/go.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('but_name'=>"orders.track_request",'alt'=>__("go")), 0);?>

                    <?php echo $_smarty_tpl->getSubTemplate ("common/image_verification.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('option'=>"use_for_track_orders",'align'=>"left",'sidebox'=>true), 0);?>

                </div>
            </form>
        <!--track_orders_block_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['block']->value['snapping_id'], ENT_QUOTES, 'UTF-8');?>
--></div>
    <?php }?>

    <div class="ty-account-info__buttons buttons-container">
        <?php if ($_smarty_tpl->tpl_vars['auth']->value['user_id']) {?>
            <a href="<?php echo htmlspecialchars(fn_url("auth.logout?redirect_url=".((string)$_smarty_tpl->tpl_vars['return_current_url']->value)), ENT_QUOTES, 'UTF-8');?>
" rel="nofollow" class="ty-btn ty-btn__primary"><?php echo $_smarty_tpl->__("sign_out");?>
</a>
        <?php } else { ?>
            <a href="<?php if ($_smarty_tpl->tpl_vars['runtime']->value['controller']=="auth"&&$_smarty_tpl->tpl_vars['runtime']->value['mode']=="login_form") {?><?php echo htmlspecialchars(fn_url($_smarty_tpl->tpl_vars['config']->value['current_url']), ENT_QUOTES, 'UTF-8');?>
<?php } else { ?><?php echo htmlspecialchars(fn_url("auth.login_form?return_url=".((string)$_smarty_tpl->tpl_vars['return_current_url']->value)), ENT_QUOTES, 'UTF-8');?>
<?php }?>" <?php if ($_smarty_tpl->tpl_vars['settings']->value['Security']['secure_auth']!="Y") {?> data-ca-target-id="login_block<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['block']->value['snapping_id'], ENT_QUOTES, 'UTF-8');?>
" class="cm-dialog-opener cm-dialog-auto-size ty-btn ty-btn__secondary"<?php } else { ?> class="ty-btn ty-btn__primary"<?php }?> rel="nofollow"><?php echo $_smarty_tpl->__("sign_in");?>
</a><a href="<?php echo htmlspecialchars(fn_url("profiles.add"), ENT_QUOTES, 'UTF-8');?>
" rel="nofollow" class="ty-btn ty-btn__primary"><?php echo $_smarty_tpl->__("register");?>
</a>
            <?php if ($_smarty_tpl->tpl_vars['settings']->value['Security']['secure_auth']!="Y") {?>
                <div  id="login_block<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['block']->value['snapping_id'], ENT_QUOTES, 'UTF-8');?>
" class="hidden" title="<?php echo $_smarty_tpl->__("sign_in");?>
">
                    <div class="ty-login-popup">
                        <?php echo $_smarty_tpl->getSubTemplate ("views/auth/login_form.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('style'=>"popup",'id'=>"popup".((string)$_smarty_tpl->tpl_vars['block']->value['snapping_id'])), 0);?>

                    </div>
                </div>
            <?php }?>
        <?php }?>
    </div>
<!--account_info_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['block']->value['snapping_id'], ENT_QUOTES, 'UTF-8');?>
--></div><?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?><?php if (trim(Smarty::$_smarty_vars['capture']['template_content'])) {?><?php if ($_smarty_tpl->tpl_vars['auth']->value['area']=="A") {?><span class="cm-template-box template-box" data-ca-te-template="blocks/my_account.tpl" id="<?php echo smarty_function_set_id(array('name'=>"blocks/my_account.tpl"),$_smarty_tpl);?>
"><div class="cm-template-icon icon-edit ty-icon-edit hidden"></div><?php echo Smarty::$_smarty_vars['capture']['template_content'];?>
<!--[/tpl_id]--></span><?php } else { ?><?php echo Smarty::$_smarty_vars['capture']['template_content'];?>
<?php }?><?php }?><?php } else { ?>

<?php $_smarty_tpl->_capture_stack[0][] = array("title", null, null); ob_start(); ?>
    <a class="ty-account-info__title" href="<?php echo htmlspecialchars(fn_url("profiles.update"), ENT_QUOTES, 'UTF-8');?>
">
        <i class="ty-icon-user"></i>&nbsp;
        <span class="hidden-phone" <?php echo smarty_function_live_edit(array('name'=>"block:name:".((string)$_smarty_tpl->tpl_vars['block']->value['block_id'])),$_smarty_tpl);?>
><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['title']->value, ENT_QUOTES, 'UTF-8');?>
</span>
        <i class="ty-icon-down-micro ty-account-info__user-arrow"></i>
    </a>
<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>

<div id="account_info_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['block']->value['snapping_id'], ENT_QUOTES, 'UTF-8');?>
">
    <?php $_smarty_tpl->tpl_vars["return_current_url"] = new Smarty_variable(rawurlencode($_smarty_tpl->tpl_vars['config']->value['current_url']), null, 0);?>
    <ul class="ty-account-info">
        <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"profiles:my_account_menu")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"profiles:my_account_menu"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

            <?php if ($_smarty_tpl->tpl_vars['auth']->value['user_id']) {?>
                <?php if ($_smarty_tpl->tpl_vars['user_info']->value['firstname']||$_smarty_tpl->tpl_vars['user_info']->value['lastname']) {?>
                    <li class="ty-account-info__item  ty-account-info__name ty-dropdown-box__item"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['user_info']->value['firstname'], ENT_QUOTES, 'UTF-8');?>
 <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['user_info']->value['lastname'], ENT_QUOTES, 'UTF-8');?>
</li>
                <?php } else { ?>
                    <?php if ($_smarty_tpl->tpl_vars['settings']->value['General']['use_email_as_login']=='Y') {?>
                        <li class="ty-account-info__item ty-dropdown-box__item ty-account-info__name"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['user_info']->value['email'], ENT_QUOTES, 'UTF-8');?>
</li>
                    <?php } else { ?>
                        <li class="ty-account-info__item ty-dropdown-box__item ty-account-info__name"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['user_info']->value['user_login'], ENT_QUOTES, 'UTF-8');?>
</li>
                    <?php }?>
                <?php }?>
                <li class="ty-account-info__item ty-dropdown-box__item"><a class="ty-account-info__a underlined" href="<?php echo htmlspecialchars(fn_url("profiles.update"), ENT_QUOTES, 'UTF-8');?>
" rel="nofollow" ><?php echo $_smarty_tpl->__("profile_details");?>
</a></li>
                <li class="ty-account-info__item ty-dropdown-box__item"><a class="ty-account-info__a underlined" href="<?php echo htmlspecialchars(fn_url("orders.downloads"), ENT_QUOTES, 'UTF-8');?>
" rel="nofollow"><?php echo $_smarty_tpl->__("downloads");?>
</a></li>
            <?php } elseif ($_smarty_tpl->tpl_vars['user_data']->value['firstname']||$_smarty_tpl->tpl_vars['user_data']->value['lastname']) {?>
                <li class="ty-account-info__item  ty-dropdown-box__item ty-account-info__name"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['user_data']->value['firstname'], ENT_QUOTES, 'UTF-8');?>
 <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['user_data']->value['lastname'], ENT_QUOTES, 'UTF-8');?>
</li>
            <?php } elseif ($_smarty_tpl->tpl_vars['settings']->value['General']['use_email_as_login']=='Y'&&$_smarty_tpl->tpl_vars['user_data']->value['email']) {?>
                <li class="ty-account-info__item ty-dropdown-box__item ty-account-info__name"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['user_data']->value['email'], ENT_QUOTES, 'UTF-8');?>
</li>
            <?php } elseif ($_smarty_tpl->tpl_vars['settings']->value['General']['use_email_as_login']!='Y'&&$_smarty_tpl->tpl_vars['user_data']->value['user_login']) {?>
                <li class="ty-account-info__item ty-dropdown-box__item ty-account-info__name"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['user_data']->value['user_login'], ENT_QUOTES, 'UTF-8');?>
</li>
            <?php }?>
            <li class="ty-account-info__item ty-dropdown-box__item"><a class="ty-account-info__a underlined" href="<?php echo htmlspecialchars(fn_url("orders.search"), ENT_QUOTES, 'UTF-8');?>
" rel="nofollow"><?php echo $_smarty_tpl->__("orders");?>
</a></li>
            <?php $_smarty_tpl->tpl_vars["compared_products"] = new Smarty_variable(fn_get_comparison_products(''), null, 0);?>
            <li class="ty-account-info__item ty-dropdown-box__item"><a class="ty-account-info__a underlined" href="<?php echo htmlspecialchars(fn_url("product_features.compare"), ENT_QUOTES, 'UTF-8');?>
" rel="nofollow"><?php echo $_smarty_tpl->__("view_compare_list");?>
<?php if ($_smarty_tpl->tpl_vars['compared_products']->value) {?> (<?php echo htmlspecialchars(count($_smarty_tpl->tpl_vars['compared_products']->value), ENT_QUOTES, 'UTF-8');?>
)<?php }?></a></li>
        <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"profiles:my_account_menu"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>


        <?php if (fn_allowed_for("MULTIVENDOR")&&$_smarty_tpl->tpl_vars['settings']->value['Vendors']['apply_for_vendor']=="Y"&&!$_smarty_tpl->tpl_vars['user_info']->value['company_id']) {?>
            <li class="ty-account-info__item ty-dropdown-box__item"><a class="ty-account-info__a underlined" href="<?php echo htmlspecialchars(fn_url("companies.apply_for_vendor?return_previous_url=".((string)$_smarty_tpl->tpl_vars['return_current_url']->value)), ENT_QUOTES, 'UTF-8');?>
" rel="nofollow"><?php echo $_smarty_tpl->__("apply_for_vendor_account");?>
</a></li>
        <?php }?>
    </ul>

    <?php if ($_smarty_tpl->tpl_vars['settings']->value['Appearance']['display_track_orders']=='Y') {?>
        <div class="ty-account-info__orders updates-wrapper track-orders" id="track_orders_block_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['block']->value['snapping_id'], ENT_QUOTES, 'UTF-8');?>
">
            <form action="<?php echo htmlspecialchars(fn_url(''), ENT_QUOTES, 'UTF-8');?>
" method="get" class="cm-ajax cm-ajax-full-render" name="track_order_quick">
                <input type="hidden" name="result_ids" value="track_orders_block_*" />
                <input type="hidden" name="return_url" value="<?php echo htmlspecialchars((($tmp = @$_REQUEST['return_url'])===null||$tmp==='' ? $_smarty_tpl->tpl_vars['config']->value['current_url'] : $tmp), ENT_QUOTES, 'UTF-8');?>
" />

                <div class="ty-account-info__orders-txt"><?php echo $_smarty_tpl->__("track_my_order");?>
</div>

                <div class="ty-account-info__orders-input ty-control-group ty-input-append">
                    <label for="track_order_item<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['block']->value['snapping_id'], ENT_QUOTES, 'UTF-8');?>
" class="cm-required hidden"><?php echo $_smarty_tpl->__("track_my_order");?>
</label>
                    <input type="text" size="20" class="ty-input-text cm-hint" id="track_order_item<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['block']->value['snapping_id'], ENT_QUOTES, 'UTF-8');?>
" name="track_data" value="<?php echo $_smarty_tpl->__("order_id");?>
<?php if (!$_smarty_tpl->tpl_vars['auth']->value['user_id']) {?>/<?php echo $_smarty_tpl->__("email");?>
<?php }?>" />
                    <?php echo $_smarty_tpl->getSubTemplate ("buttons/go.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('but_name'=>"orders.track_request",'alt'=>__("go")), 0);?>

                    <?php echo $_smarty_tpl->getSubTemplate ("common/image_verification.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('option'=>"use_for_track_orders",'align'=>"left",'sidebox'=>true), 0);?>

                </div>
            </form>
        <!--track_orders_block_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['block']->value['snapping_id'], ENT_QUOTES, 'UTF-8');?>
--></div>
    <?php }?>

    <div class="ty-account-info__buttons buttons-container">
        <?php if ($_smarty_tpl->tpl_vars['auth']->value['user_id']) {?>
            <a href="<?php echo htmlspecialchars(fn_url("auth.logout?redirect_url=".((string)$_smarty_tpl->tpl_vars['return_current_url']->value)), ENT_QUOTES, 'UTF-8');?>
" rel="nofollow" class="ty-btn ty-btn__primary"><?php echo $_smarty_tpl->__("sign_out");?>
</a>
        <?php } else { ?>
            <a href="<?php if ($_smarty_tpl->tpl_vars['runtime']->value['controller']=="auth"&&$_smarty_tpl->tpl_vars['runtime']->value['mode']=="login_form") {?><?php echo htmlspecialchars(fn_url($_smarty_tpl->tpl_vars['config']->value['current_url']), ENT_QUOTES, 'UTF-8');?>
<?php } else { ?><?php echo htmlspecialchars(fn_url("auth.login_form?return_url=".((string)$_smarty_tpl->tpl_vars['return_current_url']->value)), ENT_QUOTES, 'UTF-8');?>
<?php }?>" <?php if ($_smarty_tpl->tpl_vars['settings']->value['Security']['secure_auth']!="Y") {?> data-ca-target-id="login_block<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['block']->value['snapping_id'], ENT_QUOTES, 'UTF-8');?>
" class="cm-dialog-opener cm-dialog-auto-size ty-btn ty-btn__secondary"<?php } else { ?> class="ty-btn ty-btn__primary"<?php }?> rel="nofollow"><?php echo $_smarty_tpl->__("sign_in");?>
</a><a href="<?php echo htmlspecialchars(fn_url("profiles.add"), ENT_QUOTES, 'UTF-8');?>
" rel="nofollow" class="ty-btn ty-btn__primary"><?php echo $_smarty_tpl->__("register");?>
</a>
            <?php if ($_smarty_tpl->tpl_vars['settings']->value['Security']['secure_auth']!="Y") {?>
                <div  id="login_block<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['block']->value['snapping_id'], ENT_QUOTES, 'UTF-8');?>
" class="hidden" title="<?php echo $_smarty_tpl->__("sign_in");?>
">
                    <div class="ty-login-popup">
                        <?php echo $_smarty_tpl->getSubTemplate ("views/auth/login_form.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('style'=>"popup",'id'=>"popup".((string)$_smarty_tpl->tpl_vars['block']->value['snapping_id'])), 0);?>

                    </div>
                </div>
            <?php }?>
        <?php }?>
    </div>
<!--account_info_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['block']->value['snapping_id'], ENT_QUOTES, 'UTF-8');?>
--></div><?php }?><?php }} ?>
