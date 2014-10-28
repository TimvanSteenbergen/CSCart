<?php /* Smarty version Smarty-3.1.18, created on 2014-10-27 16:10:19
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/menu.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1493619408544e362b6c7598-48302208%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c5d5d07b5f006a2d357c04d12772d00389627c4f' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/menu.tpl',
      1 => 1413383301,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '1493619408544e362b6c7598-48302208',
  'function' => 
  array (
    'menu_attrs' => 
    array (
      'parameter' => 
      array (
        'attrs' => 
        array (
        ),
      ),
      'compiled' => '',
    ),
  ),
  'variables' => 
  array (
    'attrs' => 0,
    'attr' => 0,
    'value' => 0,
    'runtime' => 0,
    'settings' => 0,
    'name' => 0,
    'auth' => 0,
    'config' => 0,
    'company_name' => 0,
    'navigation' => 0,
    'first_level_title' => 0,
    'm' => 0,
    'second_level' => 0,
    'second_level_title' => 0,
    'sm' => 0,
    'subitem_title' => 0,
    'languages' => 0,
    'currencies' => 0,
    'secondary_currency' => 0,
    'user_info' => 0,
    'id_prefix' => 0,
    'onclick' => 0,
    'sticky_scroll' => 0,
    'sticky_padding' => 0,
  ),
  'has_nocache_code' => 0,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_544e362b807212_78719365',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544e362b807212_78719365')) {function content_544e362b807212_78719365($_smarty_tpl) {?><?php if (!is_callable('smarty_block_hook')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/block.hook.php';
if (!is_callable('smarty_modifier_truncate')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/modifier.truncate.php';
?><?php
fn_preload_lang_vars(array('all_vendors','view_storefront','storefront_url_not_defined','manage_stores','vendor','manage_stores','view_storefront','vendor','manage_vendors','signed_in_as','edit_profile','sign_out','feedback_values','send_feedback','search_tooltip','go'));
?>
<?php if (defined("THEMES_PANEL")) {?>
    <?php $_smarty_tpl->tpl_vars['sticky_scroll'] = new Smarty_variable(5, null, 0);?>
    <?php $_smarty_tpl->tpl_vars['sticky_padding'] = new Smarty_variable(36, null, 0);?>
<?php } else { ?>
    <?php $_smarty_tpl->tpl_vars['sticky_scroll'] = new Smarty_variable(41, null, 0);?>
    <?php $_smarty_tpl->tpl_vars['sticky_padding'] = new Smarty_variable(0, null, 0);?>
<?php }?>

<?php if (!function_exists('smarty_template_function_menu_attrs')) {
    function smarty_template_function_menu_attrs($_smarty_tpl,$params) {
    $saved_tpl_vars = $_smarty_tpl->tpl_vars;
    foreach ($_smarty_tpl->smarty->template_functions['menu_attrs']['parameter'] as $key => $value) {$_smarty_tpl->tpl_vars[$key] = new Smarty_variable($value);};
    foreach ($params as $key => $value) {$_smarty_tpl->tpl_vars[$key] = new Smarty_variable($value);}?>
    <?php  $_smarty_tpl->tpl_vars['value'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['value']->_loop = false;
 $_smarty_tpl->tpl_vars['attr'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['attrs']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['value']->key => $_smarty_tpl->tpl_vars['value']->value) {
$_smarty_tpl->tpl_vars['value']->_loop = true;
 $_smarty_tpl->tpl_vars['attr']->value = $_smarty_tpl->tpl_vars['value']->key;
?>
        <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['attr']->value, ENT_QUOTES, 'UTF-8');?>
="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['value']->value, ENT_QUOTES, 'UTF-8');?>
" 
    <?php } ?>
<?php $_smarty_tpl->tpl_vars = $saved_tpl_vars;
foreach (Smarty::$global_tpl_vars as $key => $value) if(!isset($_smarty_tpl->tpl_vars[$key])) $_smarty_tpl->tpl_vars[$key] = $value;}}?>

<div class="navbar-admin-top">
    <!--Navbar-->
    <div class="navbar navbar-inverse" id="header_navbar">
        <div class="navbar-inner">

        <?php if ($_smarty_tpl->tpl_vars['runtime']->value['company_data']['company']) {?>
            <?php $_smarty_tpl->tpl_vars['name'] = new Smarty_variable($_smarty_tpl->tpl_vars['runtime']->value['company_data']['company'], null, 0);?>
        <?php } else { ?>
            <?php $_smarty_tpl->tpl_vars['name'] = new Smarty_variable($_smarty_tpl->tpl_vars['settings']->value['Company']['company_name'], null, 0);?>
        <?php }?>

        <?php if (fn_allowed_for("ULTIMATE")) {?>
            <div class="nav-ult">
                <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"menu:storefront_icon")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"menu:storefront_icon"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

                <?php if (!$_smarty_tpl->tpl_vars['runtime']->value['company_data']['company_id']) {?>
                    <?php $_smarty_tpl->tpl_vars["name"] = new Smarty_variable($_smarty_tpl->__("all_vendors"), null, 0);?>
                <?php }?>
                <?php if ($_smarty_tpl->tpl_vars['runtime']->value['company_data']['storefront']) {?>
                    <a href="<?php echo htmlspecialchars(fn_url("?company_id=".((string)$_smarty_tpl->tpl_vars['runtime']->value['company_data']['company_id']),"C"), ENT_QUOTES, 'UTF-8');?>
" target="_blank" class="brand" title="<?php echo $_smarty_tpl->__("view_storefront");?>
">
                        <i class="icon-shopping-cart icon-white"></i>
                    </a>
                <?php } else { ?>
                    <a class="brand" title="<?php echo $_smarty_tpl->__("storefront_url_not_defined");?>
"><i class="icon-shopping-cart icon-white cm-tooltip"></i></a>
                <?php }?>
                <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"menu:storefront_icon"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

                <?php if ($_smarty_tpl->tpl_vars['runtime']->value['companies_available_count']>1) {?>
                    <ul class="nav">
                    <?php $_smarty_tpl->_capture_stack[0][] = array("extra_content", null, null); ob_start(); ?>
                        <?php if (fn_check_view_permissions("companies.manage","GET")) {?>
                            <li class="divider"></li>
                            <li><a href="<?php echo htmlspecialchars(fn_url("companies.manage?switch_company_id=0"), ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("manage_stores");?>
...</a></li>
                        <?php }?>
                    <?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>

                    <?php echo $_smarty_tpl->getSubTemplate ("common/ajax_select_object.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('data_url'=>"companies.get_companies_list?show_all=Y&action=href",'text'=>$_smarty_tpl->tpl_vars['name']->value,'id'=>"top_company_id",'type'=>"list",'extra_content'=>Smarty::$_smarty_vars['capture']['extra_content']), 0);?>


                    </ul>
                <?php } else { ?>
                    <ul class="nav">
                        <?php if ($_smarty_tpl->tpl_vars['auth']->value['company_id']) {?>
                            <li class="dropdown">
                                <a href="<?php echo htmlspecialchars(fn_url("companies.update?company_id=".((string)$_smarty_tpl->tpl_vars['runtime']->value['company_id'])), ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("vendor");?>
: <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['runtime']->value['company_data']['company'], ENT_QUOTES, 'UTF-8');?>
</a>
                            </li>
                        <?php } else { ?>
                            <?php if (fn_check_view_permissions("companies.manage","GET")) {?>
                                <li class="dropdown vendor-submenu">
                                    <a class=" dropdown-toggle" data-toggle="dropdown">
                                        <span><?php echo htmlspecialchars(smarty_modifier_truncate($_smarty_tpl->tpl_vars['name']->value,60,"...",true), ENT_QUOTES, 'UTF-8');?>
</span><b class="caret"></b>
                                    </a>
                                    <ul class="dropdown-menu" id="top_company_id_ajax_select_object">
                                        <li><a href="<?php echo htmlspecialchars(fn_url("companies.manage?switch_company_id=0"), ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("manage_stores");?>
...</a></li>
                                    </ul>
                                </li>
                            <?php }?>
                        <?php }?>
                    </ul>
                <?php }?>
            </div>
        <?php }?>

        <?php if (fn_allowed_for("MULTIVENDOR")&&!$_smarty_tpl->tpl_vars['runtime']->value['simple_ultimate']) {?>
            <ul class="nav">
                <a href="<?php echo htmlspecialchars(htmlspecialchars(fn_url($_smarty_tpl->tpl_vars['config']->value['http_location']), ENT_QUOTES, 'UTF-8', true), ENT_QUOTES, 'UTF-8');?>
" target="_blank" class="brand" title="<?php echo $_smarty_tpl->__("view_storefront");?>
">
                    <i class="icon-shopping-cart icon-white"></i>
                </a>
                <a href="<?php echo htmlspecialchars(fn_url(''), ENT_QUOTES, 'UTF-8');?>
" class="brand"><?php echo htmlspecialchars(smarty_modifier_truncate($_smarty_tpl->tpl_vars['settings']->value['Company']['company_name'],60,"...",true), ENT_QUOTES, 'UTF-8');?>
</a>
                <?php if ($_smarty_tpl->tpl_vars['runtime']->value['customization_mode']['live_editor']) {?>
                    <?php $_smarty_tpl->tpl_vars["company_name"] = new Smarty_variable($_smarty_tpl->tpl_vars['runtime']->value['company_data']['company'], null, 0);?>
                <?php } else { ?>
                    <?php $_smarty_tpl->tpl_vars["company_name"] = new Smarty_variable(smarty_modifier_truncate($_smarty_tpl->tpl_vars['runtime']->value['company_data']['company'],43,"...",true), null, 0);?>
                <?php }?>

                <?php if ($_smarty_tpl->tpl_vars['auth']->value['company_id']) {?>
                    <li class="dropdown">
                        <a href="<?php echo htmlspecialchars(fn_url("companies.update?company_id=".((string)$_smarty_tpl->tpl_vars['runtime']->value['company_id'])), ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("vendor");?>
: <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['runtime']->value['company_data']['company'], ENT_QUOTES, 'UTF-8');?>
</a>
                    </li>
                <?php } else { ?>
                    <?php if (fn_check_view_permissions("companies.get_companies_list","GET")) {?>
                        <?php $_smarty_tpl->_capture_stack[0][] = array("extra_content", null, null); ob_start(); ?>
                            <li class="divider"></li>
                            <li><a href="<?php echo htmlspecialchars(fn_url("companies.manage?switch_company_id=0"), ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("manage_vendors");?>
...</a></li>
                        <?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>

                        <?php echo $_smarty_tpl->getSubTemplate ("common/ajax_select_object.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('data_url'=>"companies.get_companies_list?show_all=Y&action=href",'text'=>$_smarty_tpl->tpl_vars['company_name']->value,'id'=>"top_company_id",'type'=>"list",'extra_content'=>Smarty::$_smarty_vars['capture']['extra_content']), 0);?>

                    <?php } else { ?>
                        <li class="dropdown">
                            <a class="unedited-element"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['company_name']->value, ENT_QUOTES, 'UTF-8');?>
</a>
                        </li>
                    <?php }?>
                <?php }?>
            </ul>
        <?php }?>
            <ul class="nav hover-show navbar-right" aria-labelledby="dropdownMenu">
            <?php if ($_smarty_tpl->tpl_vars['auth']->value['user_id']&&$_smarty_tpl->tpl_vars['navigation']->value['static']) {?>

                <?php  $_smarty_tpl->tpl_vars['m'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['m']->_loop = false;
 $_smarty_tpl->tpl_vars['first_level_title'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['navigation']->value['static']['top']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['m']->key => $_smarty_tpl->tpl_vars['m']->value) {
$_smarty_tpl->tpl_vars['m']->_loop = true;
 $_smarty_tpl->tpl_vars['first_level_title']->value = $_smarty_tpl->tpl_vars['m']->key;
?>
                    <li class="dropdown dropdown-top-menu-item<?php if ($_smarty_tpl->tpl_vars['first_level_title']->value==$_smarty_tpl->tpl_vars['navigation']->value['selected_tab']) {?> active<?php }?>">
                        <a id="elm_menu_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['first_level_title']->value, ENT_QUOTES, 'UTF-8');?>
" href="#" class="dropdown-toggle <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['first_level_title']->value, ENT_QUOTES, 'UTF-8');?>
" data-toggle="dropdown">
                            <?php echo $_smarty_tpl->__($_smarty_tpl->tpl_vars['first_level_title']->value);?>

                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu">
                            <?php  $_smarty_tpl->tpl_vars["second_level"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["second_level"]->_loop = false;
 $_smarty_tpl->tpl_vars['second_level_title'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['m']->value['items']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["second_level"]->key => $_smarty_tpl->tpl_vars["second_level"]->value) {
$_smarty_tpl->tpl_vars["second_level"]->_loop = true;
 $_smarty_tpl->tpl_vars['second_level_title']->value = $_smarty_tpl->tpl_vars["second_level"]->key;
?>
                                <li class="<?php if ($_smarty_tpl->tpl_vars['second_level']->value['subitems']) {?>dropdown-submenu<?php }?><?php if ($_smarty_tpl->tpl_vars['second_level_title']->value==$_smarty_tpl->tpl_vars['navigation']->value['subsection']) {?> active<?php }?> <?php if ($_smarty_tpl->tpl_vars['second_level']->value['is_promo']) {?>cm-promo-popup<?php }?> <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['second_level']->value['attrs']['class'], ENT_QUOTES, 'UTF-8');?>
" <?php smarty_template_function_menu_attrs($_smarty_tpl,array('attrs'=>$_smarty_tpl->tpl_vars['second_level']->value['attrs']['main']));?>
>
                                    <?php if ($_smarty_tpl->tpl_vars['second_level']->value['type']=="title") {?>
                                        <a id="elm_menu_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['first_level_title']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['second_level_title']->value, ENT_QUOTES, 'UTF-8');?>
" <?php if ($_smarty_tpl->tpl_vars['second_level']->value['attrs']['class_href']) {?>class="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['second_level']->value['attrs']['class_href'], ENT_QUOTES, 'UTF-8');?>
"<?php }?> <?php smarty_template_function_menu_attrs($_smarty_tpl,array('attrs'=>$_smarty_tpl->tpl_vars['second_level']->value['attrs']['href']));?>
><?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['second_level']->value['title'])===null||$tmp==='' ? $_smarty_tpl->__($_smarty_tpl->tpl_vars['second_level_title']->value) : $tmp), ENT_QUOTES, 'UTF-8');?>
</a>
                                    <?php } elseif ($_smarty_tpl->tpl_vars['second_level']->value['type']!="divider") {?>
                                        <a id="elm_menu_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['first_level_title']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['second_level_title']->value, ENT_QUOTES, 'UTF-8');?>
" <?php if ($_smarty_tpl->tpl_vars['second_level']->value['attrs']['class_href']) {?>class="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['second_level']->value['attrs']['class_href'], ENT_QUOTES, 'UTF-8');?>
"<?php }?> href="<?php echo htmlspecialchars(fn_url($_smarty_tpl->tpl_vars['second_level']->value['href']), ENT_QUOTES, 'UTF-8');?>
" <?php smarty_template_function_menu_attrs($_smarty_tpl,array('attrs'=>$_smarty_tpl->tpl_vars['second_level']->value['attrs']['href']));?>
><?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['second_level']->value['title'])===null||$tmp==='' ? $_smarty_tpl->__($_smarty_tpl->tpl_vars['second_level_title']->value) : $tmp), ENT_QUOTES, 'UTF-8');?>
</a>
                                    <?php }?>
                                    <?php if ($_smarty_tpl->tpl_vars['second_level']->value['subitems']) {?>
                                        <ul class="dropdown-menu">
                                            <?php  $_smarty_tpl->tpl_vars['sm'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['sm']->_loop = false;
 $_smarty_tpl->tpl_vars['subitem_title'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['second_level']->value['subitems']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['sm']->key => $_smarty_tpl->tpl_vars['sm']->value) {
$_smarty_tpl->tpl_vars['sm']->_loop = true;
 $_smarty_tpl->tpl_vars['subitem_title']->value = $_smarty_tpl->tpl_vars['sm']->key;
?>
                                                <li class="<?php if ($_smarty_tpl->tpl_vars['sm']->value['active']) {?>active<?php }?> <?php if ($_smarty_tpl->tpl_vars['sm']->value['is_promo']) {?>cm-promo-popup<?php }?> <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['second_level']->value['attrs']['class'], ENT_QUOTES, 'UTF-8');?>
" <?php smarty_template_function_menu_attrs($_smarty_tpl,array('attrs'=>$_smarty_tpl->tpl_vars['sm']->value['attrs']['main']));?>
>
                                                    <?php if ($_smarty_tpl->tpl_vars['sm']->value['type']=="title") {?>
                                                        <?php echo $_smarty_tpl->__($_smarty_tpl->tpl_vars['subitem_title']->value);?>

                                                    <?php } elseif ($_smarty_tpl->tpl_vars['sm']->value['type']!="divider") {?>
                                                        <a id="elm_menu_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['first_level_title']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['second_level_title']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['subitem_title']->value, ENT_QUOTES, 'UTF-8');?>
" href="<?php echo htmlspecialchars(fn_url($_smarty_tpl->tpl_vars['sm']->value['href']), ENT_QUOTES, 'UTF-8');?>
" <?php smarty_template_function_menu_attrs($_smarty_tpl,array('attrs'=>$_smarty_tpl->tpl_vars['sm']->value['attrs']['href']));?>
><?php echo $_smarty_tpl->__($_smarty_tpl->tpl_vars['subitem_title']->value);?>
</a>
                                                    <?php }?>
                                                </li>
                                                <?php if ($_smarty_tpl->tpl_vars['sm']->value['type']=="divider") {?>
                                                    <li class="divider"></li>
                                                <?php }?>
                                            <?php } ?>
                                        </ul>
                                    <?php }?>
                                </li>
                                <?php if ($_smarty_tpl->tpl_vars['second_level']->value['type']=="divider") {?>
                                    <li class="divider"></li>
                                <?php }?>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
            <?php }?>
                <!-- end navbar-->

            <?php if ($_smarty_tpl->tpl_vars['auth']->value['user_id']) {?>

                <?php if (sizeof($_smarty_tpl->tpl_vars['languages']->value)>1||sizeof($_smarty_tpl->tpl_vars['currencies']->value)>1) {?>
                    <li class="divider-vertical"></li>
                <?php }?>

                <!--language-->
                <?php if (!fn_allowed_for("ULTIMATE:FREE")) {?>
                    <?php if (sizeof($_smarty_tpl->tpl_vars['languages']->value)>1) {?>
                        <?php echo $_smarty_tpl->getSubTemplate ("common/select_object.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('style'=>"dropdown",'link_tpl'=>fn_link_attach($_smarty_tpl->tpl_vars['config']->value['current_url'],"sl="),'items'=>$_smarty_tpl->tpl_vars['languages']->value,'selected_id'=>@constant('CART_LANGUAGE'),'display_icons'=>true,'key_name'=>"name",'key_selected'=>"lang_code",'class'=>"languages"), 0);?>

                    <?php }?>
                <?php }?>
                <!--end language-->

                <!--Curriencies-->
                <?php if (sizeof($_smarty_tpl->tpl_vars['currencies']->value)>1) {?>
                <?php echo $_smarty_tpl->getSubTemplate ("common/select_object.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('style'=>"dropdown",'link_tpl'=>fn_link_attach($_smarty_tpl->tpl_vars['config']->value['current_url'],"currency="),'items'=>$_smarty_tpl->tpl_vars['currencies']->value,'selected_id'=>$_smarty_tpl->tpl_vars['secondary_currency']->value,'display_icons'=>false,'key_name'=>"description",'key_selected'=>"currency_code"), 0);?>

                <?php }?>
                <!--end curriencies-->

                <li class="divider-vertical"></li>

                <!-- user menu -->
                <li class="dropdown dropdown-top-menu-item">
                    <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"index:top_links")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"index:top_links"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

                        <a class="dropdown-toggle" data-toggle="dropdown">
                            <i class="icon-white icon-user"></i>
                            <b class="caret"></b>
                        </a>
                    <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"index:top_links"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

                    <ul class="dropdown-menu pull-right">
                        <li class="disabled">
                            <a><strong><?php echo $_smarty_tpl->__("signed_in_as");?>
</strong><br><?php if ($_smarty_tpl->tpl_vars['settings']->value['General']['use_email_as_login']=="Y") {?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['user_info']->value['email'], ENT_QUOTES, 'UTF-8');?>
<?php } else { ?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['user_info']->value['user_login'], ENT_QUOTES, 'UTF-8');?>
<?php }?></a>
                        </li>
                        <li class="divider"></li>
                        <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"menu:profile")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"menu:profile"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

                        <li><a href="<?php echo htmlspecialchars(fn_url("profiles.update?user_id=".((string)$_smarty_tpl->tpl_vars['auth']->value['user_id'])), ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("edit_profile");?>
</a></li>
                        <li><a href="<?php echo htmlspecialchars(fn_url("auth.logout"), ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("sign_out");?>
</a></li>
                        <?php if (!$_smarty_tpl->tpl_vars['runtime']->value['company_id']) {?>
                            <li class="divider"></li>
                            <li>
                                <?php echo $_smarty_tpl->getSubTemplate ("common/popupbox.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('id'=>"group".((string)$_smarty_tpl->tpl_vars['id_prefix']->value)."feedback",'edit_onclick'=>$_smarty_tpl->tpl_vars['onclick']->value,'text'=>__("feedback_values"),'act'=>"link",'picker_meta'=>"cm-clear-content",'link_text'=>__("send_feedback",array("[product]"=>@constant('PRODUCT_NAME'))),'content'=>Smarty::$_smarty_vars['capture']['update_block'],'href'=>"feedback.prepare",'no_icon_link'=>true,'but_name'=>"dispatch[feedback.send]",'opener_ajax_class'=>"cm-ajax"), 0);?>

                            </li>
                        <?php }?>
                        <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"menu:profile"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

                    </ul>
                </li>
                <!--end user menu -->
            <?php }?>
            </ul>

        </div>
    <!--header_navbar--></div>

    <!--Subnav-->
    <div class="subnav cm-sticky-scroll" data-ce-top="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['sticky_scroll']->value, ENT_QUOTES, 'UTF-8');?>
" data-ce-padding="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['sticky_padding']->value, ENT_QUOTES, 'UTF-8');?>
" id="header_subnav">
        <!--quick search-->
        <div class="search pull-right">
            <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"index:global_search")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"index:global_search"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

                <form id="global_search" method="get" action="<?php echo htmlspecialchars(fn_url(''), ENT_QUOTES, 'UTF-8');?>
">
                    <input type="hidden" name="dispatch" value="search.results" />
                    <input type="hidden" name="compact" value="Y" />
                    <button class="icon-search cm-tooltip " type="submit" title="<?php echo $_smarty_tpl->__("search_tooltip");?>
" id="search_button"><?php echo $_smarty_tpl->__("go");?>
</button>
                    <label for="gs_text"><a><input type="text" class="cm-autocomplete-off" id="gs_text" name="q" value="<?php echo htmlspecialchars($_REQUEST['q'], ENT_QUOTES, 'UTF-8');?>
" /></a></label>
                </form>
            <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"index:global_search"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>


        </div>
        <!--end quick search-->

        <!-- quick menu -->
        <?php echo $_smarty_tpl->getSubTemplate ("common/quick_menu.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

        <!-- end quick menu -->

        <ul class="nav hover-show nav-pills">
            <li><a href="<?php echo htmlspecialchars(fn_url(''), ENT_QUOTES, 'UTF-8');?>
" class="home"><i class="icon-home"></i></a></li>
        <?php if ($_smarty_tpl->tpl_vars['auth']->value['user_id']&&$_smarty_tpl->tpl_vars['navigation']->value['static']) {?>
            <?php  $_smarty_tpl->tpl_vars['m'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['m']->_loop = false;
 $_smarty_tpl->tpl_vars['first_level_title'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['navigation']->value['static']['central']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['m']->key => $_smarty_tpl->tpl_vars['m']->value) {
$_smarty_tpl->tpl_vars['m']->_loop = true;
 $_smarty_tpl->tpl_vars['first_level_title']->value = $_smarty_tpl->tpl_vars['m']->key;
?>
                <li class="dropdown <?php if ($_smarty_tpl->tpl_vars['first_level_title']->value==$_smarty_tpl->tpl_vars['navigation']->value['selected_tab']) {?> active<?php }?> ">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" >
                        <?php echo $_smarty_tpl->__($_smarty_tpl->tpl_vars['first_level_title']->value);?>

                        <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu">
                        <?php  $_smarty_tpl->tpl_vars["second_level"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["second_level"]->_loop = false;
 $_smarty_tpl->tpl_vars['second_level_title'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['m']->value['items']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["second_level"]->key => $_smarty_tpl->tpl_vars["second_level"]->value) {
$_smarty_tpl->tpl_vars["second_level"]->_loop = true;
 $_smarty_tpl->tpl_vars['second_level_title']->value = $_smarty_tpl->tpl_vars["second_level"]->key;
?>
                            <li class="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['second_level_title']->value, ENT_QUOTES, 'UTF-8');?>
<?php if ($_smarty_tpl->tpl_vars['second_level']->value['subitems']) {?> dropdown-submenu<?php }?><?php if ($_smarty_tpl->tpl_vars['second_level_title']->value==$_smarty_tpl->tpl_vars['navigation']->value['subsection']&&$_smarty_tpl->tpl_vars['first_level_title']->value==$_smarty_tpl->tpl_vars['navigation']->value['selected_tab']) {?> active<?php }?>" <?php smarty_template_function_menu_attrs($_smarty_tpl,array('attrs'=>$_smarty_tpl->tpl_vars['second_level']->value['attrs']['main']));?>
><a class="<?php if ($_smarty_tpl->tpl_vars['second_level']->value['is_promo']) {?>cm-promo-popup<?php }?> <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['second_level']->value['attrs']['class'], ENT_QUOTES, 'UTF-8');?>
" <?php if (!$_smarty_tpl->tpl_vars['second_level']->value['is_promo']) {?>href="<?php echo htmlspecialchars(fn_url($_smarty_tpl->tpl_vars['second_level']->value['href']), ENT_QUOTES, 'UTF-8');?>
"<?php }?> <?php smarty_template_function_menu_attrs($_smarty_tpl,array('attrs'=>$_smarty_tpl->tpl_vars['second_level']->value['attrs']['href']));?>
>
                                <span><?php echo $_smarty_tpl->__($_smarty_tpl->tpl_vars['second_level_title']->value);?>
<?php if ($_smarty_tpl->tpl_vars['second_level']->value['attrs']['class']=='is-addon') {?><i class="icon-is-addon"></i><?php }?></span>
                                <?php if ($_smarty_tpl->__($_smarty_tpl->tpl_vars['second_level']->value['description'])!="_".((string)$_smarty_tpl->tpl_vars['second_level_title']->value)."_menu_description") {?><?php if ($_smarty_tpl->tpl_vars['settings']->value['Appearance']['show_menu_descriptions']=="Y") {?><span class="hint"><?php echo $_smarty_tpl->__($_smarty_tpl->tpl_vars['second_level']->value['description']);?>
</span><?php }?><?php }?></a>

                                <?php if ($_smarty_tpl->tpl_vars['second_level']->value['subitems']) {?>
                                    <ul class="dropdown-menu">
                                        <?php  $_smarty_tpl->tpl_vars['sm'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['sm']->_loop = false;
 $_smarty_tpl->tpl_vars['subitem_title'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['second_level']->value['subitems']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['sm']->key => $_smarty_tpl->tpl_vars['sm']->value) {
$_smarty_tpl->tpl_vars['sm']->_loop = true;
 $_smarty_tpl->tpl_vars['subitem_title']->value = $_smarty_tpl->tpl_vars['sm']->key;
?>
                                            <li class="<?php if ($_smarty_tpl->tpl_vars['sm']->value['active']) {?>active<?php }?> <?php if ($_smarty_tpl->tpl_vars['sm']->value['is_promo']) {?>cm-promo-popup<?php }?> <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['second_level']->value['attrs']['class'], ENT_QUOTES, 'UTF-8');?>
" <?php smarty_template_function_menu_attrs($_smarty_tpl,array('attrs'=>$_smarty_tpl->tpl_vars['sm']->value['attrs']['main']));?>
><a href="<?php echo htmlspecialchars(fn_url($_smarty_tpl->tpl_vars['sm']->value['href']), ENT_QUOTES, 'UTF-8');?>
" <?php smarty_template_function_menu_attrs($_smarty_tpl,array('attrs'=>$_smarty_tpl->tpl_vars['sm']->value['attrs']['href']));?>
><?php echo $_smarty_tpl->__($_smarty_tpl->tpl_vars['subitem_title']->value);?>
</a></li>
                                        <?php } ?>
                                    </ul>
                                <?php }?>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
        <?php }?>
        </ul>
    <!--header_subnav--></div>
</div>

<script type="text/javascript">
    (function(_, $) {
        $('.navbar-right li').hover(function() {
            var pagePosition = $(".admin-content").offset();
            var adminContentWidth = 1240;

            if($(this).hasClass('dropdown-submenu')) {
                var elmPosition = $(this).find('.dropdown-menu').offset().left + $(this).find('.dropdown-menu').width();
                if((elmPosition - pagePosition.left) > adminContentWidth) {
                    $(this).find('.dropdown-menu').addClass('dropdown-menu-to-right');
                }
            }
        }, function() {
            $(this).find('.dropdown-menu').removeClass('dropdown-menu-to-right');
        });
    }(Tygh, Tygh.$));
</script>
<?php }} ?>
