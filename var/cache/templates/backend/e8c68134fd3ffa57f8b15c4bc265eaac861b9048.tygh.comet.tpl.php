<?php /* Smarty version Smarty-3.1.18, created on 2014-10-27 16:10:19
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/common/comet.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1277991849544e362ba82f96-12791332%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e8c68134fd3ffa57f8b15c4bc265eaac861b9048' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/common/comet.tpl',
      1 => 1413383301,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '1277991849544e362ba82f96-12791332',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_544e362ba86dc8_11117891',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544e362ba86dc8_11117891')) {function content_544e362ba86dc8_11117891($_smarty_tpl) {?><?php
fn_preload_lang_vars(array('processing'));
?>
<a id="comet_container_controller" data-backdrop="static" data-keyboard="false" href="#comet_control" data-toggle="modal" class="hide"></a>

<div class="modal hide fade" id="comet_control" tabindex="-1" role="dialog" aria-labelledby="comet_title" aria-hidden="true">
    <div class="modal-header">
        <h3 id="comet_title"><?php echo $_smarty_tpl->__("processing");?>
</h3>
    </div>
    <div class="modal-body">
        <p></p>
        <div class="progress progress-striped active">
            
            <div class="bar" style="width: 0%;"></div>
        </div>
    </div>
</div><?php }} ?>
