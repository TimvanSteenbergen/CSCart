<?php /* Smarty version Smarty-3.1.18, created on 2014-11-24 17:22:56
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/views/product_options/manage.tpl" */ ?>
<?php /*%%SmartyHeaderCode:31727717154733f40f27413-82345269%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '5fd9961c847b3cdb854cd17062e5200e780750fc' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/views/product_options/manage.tpl',
      1 => 1413383305,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '31727717154733f40f27413-82345269',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'object' => 0,
    'runtime' => 0,
    'product_data' => 0,
    'view_mode' => 0,
    'position' => 0,
    'extra' => 0,
    'product_options' => 0,
    'po' => 0,
    'product_id' => 0,
    'allow_save' => 0,
    'query_delete_product_id' => 0,
    'details' => 0,
    'hide_for_vendor' => 0,
    'status' => 0,
    'query_product_id' => 0,
    'href_delete' => 0,
    'delete_target_id' => 0,
    'additional_class' => 0,
    'link_text' => 0,
    'non_editable' => 0,
    'select_language' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54733f4109e797_42405192',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54733f4109e797_42405192')) {function content_54733f4109e797_42405192($_smarty_tpl) {?><?php if (!is_callable('smarty_function_script')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/function.script.php';
?><?php
fn_preload_lang_vars(array('new_option','add_option','new_option','add_option','apply_to_products','global','edit','view','view','editing_option','no_data','options'));
?>
<?php echo smarty_function_script(array('src'=>"js/tygh/tabs.js"),$_smarty_tpl);?>


    <script type="text/javascript">
    function fn_check_option_type(value, tag_id)
    {
        var id = tag_id.replace('option_type_', '').replace('elm_', '');
        Tygh.$('#tab_option_variants_' + id).toggleBy(!(value == 'S' || value == 'R' || value == 'C'));
        Tygh.$('#required_options_' + id).toggleBy(!(value == 'I' || value == 'T' || value == 'F'));
        Tygh.$('#extra_options_' + id).toggleBy(!(value == 'I' || value == 'T'));
        Tygh.$('#file_options_' + id).toggleBy(!(value == 'F'));

        if (value == 'C') {
            var t = Tygh.$('table', '#content_tab_option_variants_' + id);
            Tygh.$('.cm-non-cb', t).switchAvailability(true); // hide obsolete columns
            Tygh.$('tbody:gt(1)', t).switchAvailability(true); // hide obsolete rows

        } else if (value == 'S' || value == 'R') {
            var t = Tygh.$('table', '#content_tab_option_variants_' + id);
            Tygh.$('.cm-non-cb', t).switchAvailability(false); // show all columns
            Tygh.$('tbody', t).switchAvailability(false); // show all rows
            Tygh.$('#box_add_variant_' + id).show(); // show "add new variants" box

        } else if (value == 'I' || value == 'T') {
            Tygh.$('#extra_options_' + id).show(); // show "add new variants" box
        }
    }
    </script>


<?php $_smarty_tpl->_capture_stack[0][] = array("mainbox", null, null); ob_start(); ?>

    <?php if ($_smarty_tpl->tpl_vars['object']->value=="global") {?>
        <?php $_smarty_tpl->tpl_vars["select_languages"] = new Smarty_variable(true, null, 0);?>
        <?php $_smarty_tpl->tpl_vars["delete_target_id"] = new Smarty_variable("pagination_contents", null, 0);?>
    <?php } else { ?>
        <?php $_smarty_tpl->tpl_vars["delete_target_id"] = new Smarty_variable("product_options_list", null, 0);?>
    <?php }?>

    <?php echo $_smarty_tpl->getSubTemplate ("common/pagination.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>


    <?php if (!($_smarty_tpl->tpl_vars['runtime']->value['company_id']&&$_smarty_tpl->tpl_vars['product_data']->value['shared_product']=="Y"&&$_smarty_tpl->tpl_vars['runtime']->value['company_id']!=$_smarty_tpl->tpl_vars['product_data']->value['company_id'])) {?>
        <?php $_smarty_tpl->_capture_stack[0][] = array("toolbar", null, null); ob_start(); ?>
            <?php $_smarty_tpl->_capture_stack[0][] = array("add_new_picker", null, null); ob_start(); ?>
                <?php if ($_smarty_tpl->tpl_vars['product_data']->value) {?>
                    <?php echo $_smarty_tpl->getSubTemplate ("views/product_options/update.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('option_id'=>"0",'company_id'=>$_smarty_tpl->tpl_vars['product_data']->value['company_id'],'disable_company_picker'=>true), 0);?>

                <?php } else { ?>
                    <?php echo $_smarty_tpl->getSubTemplate ("views/product_options/update.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('option_id'=>"0"), 0);?>

                <?php }?>
            <?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
            <?php if ($_smarty_tpl->tpl_vars['object']->value=="product") {?>
                <?php $_smarty_tpl->tpl_vars["position"] = new Smarty_variable("pull-right", null, 0);?>
            <?php }?>
            <?php if ($_smarty_tpl->tpl_vars['view_mode']->value=="embed") {?>
                <?php echo $_smarty_tpl->getSubTemplate ("common/popupbox.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('id'=>"add_new_option",'text'=>__("new_option"),'link_text'=>__("add_option"),'act'=>"general",'content'=>Smarty::$_smarty_vars['capture']['add_new_picker'],'meta'=>$_smarty_tpl->tpl_vars['position']->value,'icon'=>"icon-plus"), 0);?>


            <?php } else { ?>
                <?php echo $_smarty_tpl->getSubTemplate ("common/popupbox.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('id'=>"add_new_option",'text'=>__("new_option"),'title'=>__("add_option"),'act'=>"general",'content'=>Smarty::$_smarty_vars['capture']['add_new_picker'],'meta'=>$_smarty_tpl->tpl_vars['position']->value,'icon'=>"icon-plus"), 0);?>

            <?php }?>

        <?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
        <?php echo $_smarty_tpl->tpl_vars['extra']->value;?>

    <?php }?>
        <?php if ($_smarty_tpl->tpl_vars['object']->value!="global") {?>
            <div class="btn-toolbar clearfix cm-toggle-button">
                <?php echo Smarty::$_smarty_vars['capture']['toolbar'];?>

            </div>
        <?php } else { ?>
            <?php $_smarty_tpl->_capture_stack[0][] = array("buttons", null, null); ob_start(); ?>
                <?php if ($_smarty_tpl->tpl_vars['product_options']->value&&$_smarty_tpl->tpl_vars['object']->value=="global") {?>
                    <?php echo $_smarty_tpl->getSubTemplate ("buttons/button.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('but_text'=>__("apply_to_products"),'but_role'=>"action",'but_href'=>"product_options.apply"), 0);?>

                <?php }?>
            <?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
            <?php $_smarty_tpl->_capture_stack[0][] = array("adv_buttons", null, null); ob_start(); ?>
                <?php echo Smarty::$_smarty_vars['capture']['toolbar'];?>

            <?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
        <?php }?>

        <div class="items-container" id="product_options_list">
            <?php if ($_smarty_tpl->tpl_vars['product_options']->value) {?>
            <table width="100%" class="table table-middle table-objects">
                <tbody>
                    <?php  $_smarty_tpl->tpl_vars["po"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["po"]->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['product_options']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["po"]->key => $_smarty_tpl->tpl_vars["po"]->value) {
$_smarty_tpl->tpl_vars["po"]->_loop = true;
?>
                        <?php if ($_smarty_tpl->tpl_vars['object']->value=="product"&&!$_smarty_tpl->tpl_vars['po']->value['product_id']) {?>
                            <?php ob_start();?><?php echo $_smarty_tpl->__("global");?>
<?php $_tmp1=ob_get_clean();?><?php $_smarty_tpl->tpl_vars["details"] = new Smarty_variable("(".$_tmp1.")", null, 0);?>
                            <?php $_smarty_tpl->tpl_vars["query_product_id"] = new Smarty_variable('', null, 0);?>
                        <?php } else { ?>
                            <?php $_smarty_tpl->tpl_vars["details"] = new Smarty_variable('', null, 0);?>
                            <?php $_smarty_tpl->tpl_vars["query_product_id"] = new Smarty_variable("&product_id=".((string)$_smarty_tpl->tpl_vars['product_id']->value), null, 0);?>
                        <?php }?>

                        <?php if ($_smarty_tpl->tpl_vars['object']->value=="product") {?>
                            <?php if (!$_smarty_tpl->tpl_vars['po']->value['product_id']) {?>
                                <?php $_smarty_tpl->tpl_vars["query_product_id"] = new Smarty_variable("&object=".((string)$_smarty_tpl->tpl_vars['object']->value), null, 0);?>
                            <?php } else { ?>
                                <?php $_smarty_tpl->tpl_vars["query_product_id"] = new Smarty_variable("&product_id=".((string)$_smarty_tpl->tpl_vars['product_id']->value)."&object=".((string)$_smarty_tpl->tpl_vars['object']->value), null, 0);?>
                            <?php }?>
                            <?php $_smarty_tpl->tpl_vars["query_delete_product_id"] = new Smarty_variable("&product_id=".((string)$_smarty_tpl->tpl_vars['product_id']->value), null, 0);?>
                            <?php $_smarty_tpl->tpl_vars["allow_save"] = new Smarty_variable(fn_allow_save_object($_smarty_tpl->tpl_vars['product_data']->value,"products"), null, 0);?>
                        <?php } else { ?>
                            <?php $_smarty_tpl->tpl_vars["query_product_id"] = new Smarty_variable('', null, 0);?>
                            <?php $_smarty_tpl->tpl_vars["query_delete_product_id"] = new Smarty_variable('', null, 0);?>
                            <?php $_smarty_tpl->tpl_vars["allow_save"] = new Smarty_variable(fn_allow_save_object($_smarty_tpl->tpl_vars['po']->value,"product_options"), null, 0);?>
                        <?php }?>

                        <?php if (fn_allowed_for("MULTIVENDOR")) {?>
                            <?php if ($_smarty_tpl->tpl_vars['allow_save']->value) {?>
                                <?php $_smarty_tpl->tpl_vars["link_text"] = new Smarty_variable($_smarty_tpl->__("edit"), null, 0);?>
                                <?php $_smarty_tpl->tpl_vars["additional_class"] = new Smarty_variable("cm-no-hide-input", null, 0);?>
                                <?php $_smarty_tpl->tpl_vars["hide_for_vendor"] = new Smarty_variable(false, null, 0);?>
                            <?php } else { ?>
                                <?php $_smarty_tpl->tpl_vars["link_text"] = new Smarty_variable($_smarty_tpl->__("view"), null, 0);?>
                                <?php $_smarty_tpl->tpl_vars["additional_class"] = new Smarty_variable('', null, 0);?>
                                <?php $_smarty_tpl->tpl_vars["hide_for_vendor"] = new Smarty_variable(true, null, 0);?>
                            <?php }?>
                        <?php }?>

                        <?php $_smarty_tpl->tpl_vars["status"] = new Smarty_variable($_smarty_tpl->tpl_vars['po']->value['status'], null, 0);?>
                        <?php $_smarty_tpl->tpl_vars["href_delete"] = new Smarty_variable("product_options.delete?option_id=".((string)$_smarty_tpl->tpl_vars['po']->value['option_id']).((string)$_smarty_tpl->tpl_vars['query_delete_product_id']->value), null, 0);?>

                        <?php if (fn_allowed_for("ULTIMATE")) {?>
                            <?php $_smarty_tpl->tpl_vars["non_editable"] = new Smarty_variable(false, null, 0);?>
                            <?php if ($_smarty_tpl->tpl_vars['runtime']->value['company_id']&&(($_smarty_tpl->tpl_vars['product_data']->value['shared_product']=="Y"&&$_smarty_tpl->tpl_vars['runtime']->value['company_id']!=$_smarty_tpl->tpl_vars['product_data']->value['company_id'])||($_smarty_tpl->tpl_vars['object']->value=="global"&&$_smarty_tpl->tpl_vars['runtime']->value['company_id']!=$_smarty_tpl->tpl_vars['po']->value['company_id']))) {?>
                                <?php $_smarty_tpl->tpl_vars["link_text"] = new Smarty_variable($_smarty_tpl->__("view"), null, 0);?>
                                <?php $_smarty_tpl->tpl_vars["href_delete"] = new Smarty_variable(false, null, 0);?>
                                <?php $_smarty_tpl->tpl_vars["non_editable"] = new Smarty_variable(true, null, 0);?>
                                <?php $_smarty_tpl->tpl_vars["is_view_link"] = new Smarty_variable(true, null, 0);?>
                            <?php }?>
                        <?php }?>

                        <?php ob_start();?><?php echo $_smarty_tpl->__("editing_option");?>
<?php $_tmp2=ob_get_clean();?><?php echo $_smarty_tpl->getSubTemplate ("common/object_group.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('no_table'=>true,'id'=>$_smarty_tpl->tpl_vars['po']->value['option_id'],'id_prefix'=>"_product_option_",'details'=>$_smarty_tpl->tpl_vars['details']->value,'text'=>$_smarty_tpl->tpl_vars['po']->value['option_name'],'hide_for_vendor'=>$_smarty_tpl->tpl_vars['hide_for_vendor']->value,'status'=>$_smarty_tpl->tpl_vars['status']->value,'table'=>"product_options",'object_id_name'=>"option_id",'href'=>"product_options.update?option_id=".((string)$_smarty_tpl->tpl_vars['po']->value['option_id']).((string)$_smarty_tpl->tpl_vars['query_product_id']->value),'href_delete'=>$_smarty_tpl->tpl_vars['href_delete']->value,'delete_target_id'=>$_smarty_tpl->tpl_vars['delete_target_id']->value,'header_text'=>$_tmp2.": ".((string)$_smarty_tpl->tpl_vars['po']->value['option_name']),'skip_delete'=>!$_smarty_tpl->tpl_vars['allow_save']->value,'additional_class'=>$_smarty_tpl->tpl_vars['additional_class']->value,'prefix'=>"product_options",'link_text'=>$_smarty_tpl->tpl_vars['link_text']->value,'non_editable'=>$_smarty_tpl->tpl_vars['non_editable']->value,'company_object'=>$_smarty_tpl->tpl_vars['po']->value), 0);?>

                <?php } ?>
                </tbody>
            </table>
            <?php } else { ?>
                <p class="no-items"><?php echo $_smarty_tpl->__("no_data");?>
</p>
            <?php }?>
            <!--product_options_list--></div>
    <?php echo $_smarty_tpl->getSubTemplate ("common/pagination.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>


<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>

<?php if ($_smarty_tpl->tpl_vars['object']->value=="product") {?>
    <?php echo Smarty::$_smarty_vars['capture']['mainbox'];?>

<?php } else { ?>
    <?php echo $_smarty_tpl->getSubTemplate ("common/mainbox.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('title'=>__("options"),'content'=>Smarty::$_smarty_vars['capture']['mainbox'],'buttons'=>Smarty::$_smarty_vars['capture']['buttons'],'adv_buttons'=>Smarty::$_smarty_vars['capture']['adv_buttons'],'select_language'=>$_smarty_tpl->tpl_vars['select_language']->value), 0);?>

<?php }?>
<?php }} ?>
