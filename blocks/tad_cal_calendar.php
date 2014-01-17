<?php
//區塊主函式 (小行事曆(tad_cal_calendar))
function tad_cal_calendar($options){

  //引入TadTools的函式庫
  if(!file_exists(XOOPS_ROOT_PATH."/modules/tadtools/tad_function.php")){
   redirect_header("http://www.tad0616.net/modules/tad_uploader/index.php?of_cat_sn=50",3, _TAD_NEED_TADTOOLS);
  }
  include_once XOOPS_ROOT_PATH."/modules/tadtools/tad_function.php";

  $jquery_path = get_jquery(true); //一般只要此行即可

  $block['jquery_path']=$jquery_path;


  return $block;
}

?>