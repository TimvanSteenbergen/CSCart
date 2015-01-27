<?php /* Smarty version Smarty-3.1.18, created on 2014-10-27 17:31:48
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/views/addons/manage.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1486515992544e4944b09d92-67951694%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '53753fde659c2e192de3f866a27ae01258b0c397' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/views/addons/manage.tpl',
      1 => 1413383302,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '1486515992544e4944b09d92-67951694',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'config' => 0,
    'runtime' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_544e4944b40931_04523319',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544e4944b40931_04523319')) {function content_544e4944b40931_04523319($_smarty_tpl) {?><?php if (!is_callable('smarty_function_script')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/function.script.php';
if (!is_callable('smarty_block_hook')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/block.hook.php';
?><?php
fn_preload_lang_vars(array('marketplace','marketplace_find_more','installed_addons','browse_all_available_addons','no_data','no_data','upload_addon','upload_addon','addons'));
?>
<?php echo $_smarty_tpl->getSubTemplate ("views/profiles/components/profiles_scripts.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('states'=>fn_get_all_states(1)), 0);?>


<?php echo smarty_function_script(array('src'=>"js/tygh/tabs.js"),$_smarty_tpl);?>

<?php echo smarty_function_script(array('src'=>"js/tygh/filter_table.js"),$_smarty_tpl);?>

<?php echo smarty_function_script(array('src'=>"js/tygh/fileuploader_scripts.js"),$_smarty_tpl);?>



    <script type="text/javascript">
        (function(_, $) {
            $(document).ready(function(){
                var search_field = $("#elm_addon");
                var search_clear = $("#elm_addon_clear");

                // Init plugin
                search_field.ceFilterTable({
                    table: ".table-addons",
                    empty: ".no-items"
                });

                // Clear input
                search_clear.on("click", function() {
                    search_field.val("").trigger("input");
                    search_clear.addClass("hidden");
                });
                
                // Show clear button if search filed isn't empty
                search_field.on("keyup input", function() {
                    if(search_field.val().length > 0) {
                        search_clear.removeClass("hidden");
                    } else {
                        search_clear.addClass("hidden");
                    }
                });

            });
        }(Tygh, Tygh.$));
    </script>


<?php $_smarty_tpl->_capture_stack[0][] = array("mainbox", null, null); ob_start(); ?>

<?php $_smarty_tpl->_capture_stack[0][] = array("sidebar", null, null); ob_start(); ?>
    <?php echo $_smarty_tpl->getSubTemplate ("views/addons/components/addons_search_form.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('dispatch'=>"addons.manage"), 0);?>

    <hr>
    <div class="sidebar-row marketplace">
        <h6><?php echo $_smarty_tpl->__("marketplace");?>
</h6>
        <p class="marketplace-link"><?php echo $_smarty_tpl->__("marketplace_find_more",array("[href]"=>$_smarty_tpl->tpl_vars['config']->value['resources']['marketplace_url']));?>
</p>
    </div>
<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>

<?php $_smarty_tpl->_capture_stack[0][] = array("upload_addon", null, null); ob_start(); ?>
    <?php echo $_smarty_tpl->getSubTemplate ("views/addons/components/upload_addon.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>

<div class="items-container" id="addons_list">
<?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"addons:manage")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"addons:manage"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>


<div class="tabs cm-j-tabs clear">
    <ul class="nav nav-tabs">
        <li id="tab_installed_addons" class="cm-js active"><a><?php echo $_smarty_tpl->__("installed_addons");?>
</a></li>
        <li id="tab_browse_all_available_addons" class="cm-js"><a><?php echo $_smarty_tpl->__("browse_all_available_addons");?>
</a></li>
    </ul>
</div>

<div class="cm-tabs-content">
    <div id="content_tab_installed_addons">
        <?php echo $_smarty_tpl->getSubTemplate ("views/addons/components/addons_list.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('show_installed'=>true), 0);?>

        <p class="no-items hidden"><?php echo $_smarty_tpl->__("no_data");?>
</p>
    </div>
    <div id="content_tab_browse_all_available_addons">
        <?php echo $_smarty_tpl->getSubTemplate ("views/addons/components/addons_list.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

        <p class="no-items hidden"><?php echo $_smarty_tpl->__("no_data");?>
</p>
    </div>
</div>

<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"addons:manage"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

<!--addons_list--></div>

<?php $_smarty_tpl->_capture_stack[0][] = array("adv_buttons", null, null); ob_start(); ?>
    <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"addons:adv_buttons")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"addons:adv_buttons"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

    <?php if (!$_smarty_tpl->tpl_vars['runtime']->value['company_id']&&!defined("RESTRICTED_ADMIN")) {?>
        <?php echo $_smarty_tpl->getSubTemplate ("common/popupbox.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('id'=>"upload_addon",'text'=>__("upload_addon"),'title'=>__("upload_addon"),'content'=>Smarty::$_smarty_vars['capture']['upload_addon'],'act'=>"general",'link_class'=>"cm-dialog-auto-size",'icon'=>"icon-plus",'link_text'=>''), 0);?>

    <?php }?>
    <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"addons:adv_buttons"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>

<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
<?php echo $_smarty_tpl->getSubTemplate ("common/mainbox.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('title'=>__("addons"),'content'=>Smarty::$_smarty_vars['capture']['mainbox'],'sidebar'=>Smarty::$_smarty_vars['capture']['sidebar'],'adv_buttons'=>Smarty::$_smarty_vars['capture']['adv_buttons']), 0);?>

<?php }} ?>
