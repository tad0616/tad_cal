<?php
//  ------------------------------------------------------------------------ //
// 本模組由 tad 製作
// 製作日期：2008-03-25
// ------------------------------------------------------------------------- //

/*-----------引入檔案區--------------*/
include_once "header.php";
$xoopsOption['template_main'] = "tadcbox_index_tpl.html";
/*-----------function區--------------*/

//列出所有tad_cbox資料
function list_tad_cbox(){
	global $xoopsDB,$xoopsModule,$xoopsModuleConfig,$xoopsUser;


  //引入TadTools的jquery
  if(!file_exists(XOOPS_ROOT_PATH."/modules/tadtools/jquery.php")){
  redirect_header("http://www.tad0616.net/modules/tad_uploader/index.php?of_cat_sn=50",3, _TAD_NEED_TADTOOLS);
  }
  include_once XOOPS_ROOT_PATH."/modules/tadtools/jquery.php";
  $jquery_path = get_jquery();


  $data="
  <div align='center' id='cboxdiv'>
  <iframe frameborder='0' width='100%' height='400' src='".XOOPS_URL."/modules/tad_cbox/show.php?twh=100%' marginheight='2' marginwidth='2' scrolling='auto' allowtransparency='yes' name='cboxmain' style='border:#ababab 1px solid;' id='cboxmain'></iframe><br/>
  <iframe frameborder='0' width='100%' height='150' src='".XOOPS_URL."/modules/tad_cbox/post.php' marginheight='2' marginwidth='2' scrolling='no' allowtransparency='yes' name='cboxform' style='border:#ababab 1px solid;border-top:0px' id='cboxform'></iframe>
  </div>";
	return $data;
}


/*-----------執行動作判斷區----------*/
$_REQUEST['op']=(empty($_REQUEST['op']))?"":$_REQUEST['op'];
switch($_REQUEST['op']){

	default:
	$main=list_tad_cbox();
	break;
}

/*-----------秀出結果區--------------*/

include_once XOOPS_ROOT_PATH."/header.php";
$xoopsTpl->assign( "css" , "<link rel='stylesheet' type='text/css' media='screen' href='".XOOPS_URL."/modules/tad_cbox/module.css' />") ;
$xoopsTpl->assign( "toolbar" , toolbar($interface_menu)) ;
$xoopsTpl->assign( "content" , $main) ;
include_once XOOPS_ROOT_PATH.'/footer.php';

?>
