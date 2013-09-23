<?php
//  ------------------------------------------------------------------------ //
// 本模組由 tad 製作
// 製作日期：2011-11-03
// $Id:$
// ------------------------------------------------------------------------- //

/*-----------引入檔案區--------------*/
$xoopsOption['template_main'] = "tad_cal_adm_main.html";
include_once "header.php";
include_once "../function.php";

/*-----------function區--------------*/
//tad_cal_cate編輯表單
function tad_cal_cate_form($cate_sn=""){
	global $xoopsDB,$xoopsUser,$xoopsTpl;
	include_once(XOOPS_ROOT_PATH."/class/xoopsformloader.php");
	//include_once(XOOPS_ROOT_PATH."/class/xoopseditor/xoopseditor.php");

	//抓取預設值
	if(!empty($cate_sn)){
		$DBV=get_tad_cal_cate($cate_sn);
	}else{
		$DBV=array();
	}

	//預設值設定


	//設定「cate_sn」欄位預設值
	$cate_sn=(!isset($DBV['cate_sn']))?"":$DBV['cate_sn'];

	//設定「cate_title」欄位預設值
	$cate_title=(!isset($DBV['cate_title']))?_MA_TADCAL_NEW_CALENDAR:$DBV['cate_title'];

	//設定「cate_sort」欄位預設值
	$cate_sort=(!isset($DBV['cate_sort']))?tad_cal_cate_max_sort():$DBV['cate_sort'];

	//設定「cate_enable」欄位預設值
	$cate_enable=(!isset($DBV['cate_enable']))?"":$DBV['cate_enable'];

	//設定「cate_handle」欄位預設值
	$cate_handle=(!isset($DBV['cate_handle']))?"":$DBV['cate_handle'];

	//設定「enable_group」欄位預設值
	$enable_group=(!isset($DBV['enable_group']))?"":explode(",",$DBV['enable_group']);
  
	//設定「enable_upload_group」欄位預設值
  $enable_upload_group=(!isset($DBV['enable_upload_group']))?array('1'):explode(",",$DBV['enable_upload_group']);

	//設定「google_id」欄位預設值
	$google_id=(!isset($DBV['google_id']))?"":$DBV['google_id'];

	//設定「google_pass」欄位預設值
	$google_pass=(!isset($DBV['google_pass']))?"":$DBV['google_pass'];
	
	//設定「cate_bgcolor」欄位預設值
	$cate_bgcolor=(!isset($DBV['cate_bgcolor']))?"rgb(120,177,255)":$DBV['cate_bgcolor'];
	
	//設定「cate_color」欄位預設值
	$cate_color=(!isset($DBV['cate_color']))?"rgb(255,255,255)":$DBV['cate_color'];

	$op=(empty($cate_sn))?"insert_tad_cal_cate":"update_tad_cal_cate";
	//$op="replace_tad_cal_cate";
	
	
	//可見群組
	$SelectGroup_name = new XoopsFormSelectGroup("", "enable_group", false,$enable_group, 3, true);
	$SelectGroup_name->addOption("", _MA_TADCAL_ALL_OK, false);
	$enable_group = $SelectGroup_name->render();

  //可上傳群組
  $SelectGroup_name = new XoopsFormSelectGroup("", "enable_upload_group", false,$enable_upload_group, 3, true);
  $enable_upload_group = $SelectGroup_name->render();
  
  
	if(!file_exists(TADTOOLS_PATH."/formValidator.php")){
   redirect_header("index.php",3, _MA_NEED_TADTOOLS);
  }
  include_once TADTOOLS_PATH."/formValidator.php";
  $formValidator= new formValidator("#myForm",true);
  $formValidator_code=$formValidator->render();


	$xoopsTpl->assign('next_op',$op);
	$xoopsTpl->assign('cate_sn',$cate_sn);
	$xoopsTpl->assign('cate_handle',$cate_handle);
	$xoopsTpl->assign('enable_upload_group',$enable_upload_group);
	$xoopsTpl->assign('enable_group',$enable_group);
	$xoopsTpl->assign('cate_enable1',chk($cate_enable,'1','1'));
	$xoopsTpl->assign('cate_enable0',chk($cate_enable,'0'));
	$xoopsTpl->assign('cate_sort',$cate_sort);
	$xoopsTpl->assign('cate_color',$cate_color);
	$xoopsTpl->assign('cate_bgcolor',$cate_bgcolor);
	$xoopsTpl->assign('cate_title',$cate_title);
	$xoopsTpl->assign('formValidator_code',$formValidator_code);
	$xoopsTpl->assign('google_id',$google_id);
	$xoopsTpl->assign('google_pass',$google_pass);
	$xoopsTpl->assign('op','tad_cal_cate_form');
}

//匯入Google行事曆
function tad_cal_add_gcal_form(){
	global $xoopsDB,$xoopsUser,$xoopsTpl;

	$xoopsTpl->assign('op','tad_cal_add_gcal_form');
	$xoopsTpl->assign('curl_init',function_exists('curl_init'));
}



//新增資料到tad_cal_cate中
function insert_tad_cal_cate(){
	global $xoopsDB,$xoopsUser;

	$myts =& MyTextSanitizer::getInstance();
	$_POST['cate_title']=$myts->addSlashes($_POST['cate_title']);
	$_POST['google_id']=$myts->addSlashes($_POST['google_id']);
	$_POST['google_pass']=$myts->addSlashes($_POST['google_pass']);
	
	if(empty($_POST['enable_group']) or in_array("",$_POST['enable_group'])){
    $enable_group="";
  }else{
    $enable_group=implode(",",$_POST['enable_group']);
  }

  if(empty($_POST['enable_upload_group'])){
    $enable_upload_group="1";
  }else{
    $enable_upload_group=implode(",",$_POST['enable_upload_group']);
  }

	$sql = "insert into ".$xoopsDB->prefix("tad_cal_cate")."
	(`cate_title` , `cate_sort` , `cate_enable` , `cate_handle` , `enable_group` , `enable_upload_group` , `google_id` , `google_pass`,`cate_color`)
	values('{$_POST['cate_title']}' , '{$_POST['cate_sort']}' , '{$_POST['cate_enable']}' , '{$_POST['cate_handle']}' , '{$enable_group}' , '{$enable_upload_group}' , '{$_POST['google_id']}' , '{$_POST['google_pass']}','rgb(0,0,0)')";
	$xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

	//取得最後新增資料的流水編號
	$cate_sn=$xoopsDB->getInsertId();
	
	//自動給顏色碼
	$color=num2color($cate_sn);
	$sql="update ".$xoopsDB->prefix("tad_cal_cate")." set `cate_bgcolor`='{$color}' where `cate_sn`='$cate_sn'";
	$xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
	
	return $cate_sn;
}

//更新tad_cal_cate某一筆資料
function update_tad_cal_cate($cate_sn=""){
	global $xoopsDB,$xoopsUser;


	$myts =& MyTextSanitizer::getInstance();
	$_POST['cate_title']=$myts->addSlashes($_POST['cate_title']);
	$_POST['google_id']=$myts->addSlashes($_POST['google_id']);
	$_POST['google_pass']=$myts->addSlashes($_POST['google_pass']);

	if(empty($_POST['enable_group']) or in_array("",$_POST['enable_group'])){
    $enable_group="";
  }else{
    $enable_group=implode(",",$_POST['enable_group']);
  }

  if(empty($_POST['enable_upload_group'])){
    $enable_upload_group="1";
  }else{
    $enable_upload_group=implode(",",$_POST['enable_upload_group']);
  }

	$sql = "update ".$xoopsDB->prefix("tad_cal_cate")." set
	 `cate_title` = '{$_POST['cate_title']}' ,
	 `cate_sort` = '{$_POST['cate_sort']}' ,
	 `cate_enable` = '{$_POST['cate_enable']}' ,
	 `cate_handle` = '{$_POST['cate_handle']}' ,
	 `enable_group` = '{$enable_group}' ,
	 `enable_upload_group` = '{$enable_upload_group}' ,
	 `google_id` = '{$_POST['google_id']}' ,
	 `google_pass` = '{$_POST['google_pass']}',
	 `cate_bgcolor` = '{$_POST['cate_bgcolor']}',
   `cate_color` = '{$_POST['cate_color']}'
	where cate_sn='$cate_sn'";
	$xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
	return $cate_sn;
}

//列出所有tad_cal_cate資料
function list_tad_cal_cate($show_function=1){
	global $xoopsDB,$xoopsModule,$xoopsTpl;

  //取得資料數
  $sql = "select count(*),cate_sn,max(`last_update`) from ".$xoopsDB->prefix("tad_cal_event")." group by cate_sn";
	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
  while(list($count,$cate_sn,$last_update)=$xoopsDB->fetchRow($result)){
    $counter[$cate_sn]=$count;
    $last[$cate_sn]=$last_update;
  }
    
    
	$sql = "select * from ".$xoopsDB->prefix("tad_cal_cate")." order by cate_sort";

	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

	$function_title=($show_function)?"<th>"._TAD_FUNCTION."</th>":"";

	$all_content="";
  $i=0;
	while($all=$xoopsDB->fetchArray($result)){
	  //以下會產生這些變數： $cate_sn , $cate_title , $cate_sort , $cate_enable , $cate_handle , $enable_group , $enable_upload_group , $google_id , $google_pass, $cate_bgcolor, $cate_color
    foreach($all as $k=>$v){
      $$k=$v;
    }
    

    $g_txt=txt_to_group_name($enable_group,_MA_TADCAL_ALL_OK);
    $gu_txt=txt_to_group_name($enable_upload_group,_MA_TADCAL_ALL_OK);

    $enable=($cate_enable=='1')?_YES:_NO;
    
    if(empty($counter[$cate_sn]))$counter[$cate_sn]=0;
    if($last[$cate_sn]=='0000-00-00 00:00:00')$last[$cate_sn]='';

		
		$all_content[$i]['cate_sn']=$cate_sn;
		$all_content[$i]['goo_tool']=$goo_tool;
		$all_content[$i]['last']=$last[$cate_sn];
		$all_content[$i]['gu_txt']=$gu_txt;
		$all_content[$i]['g_txt']=$g_txt;
		$all_content[$i]['enable']=$enable;
		$all_content[$i]['counter']=$counter[$cate_sn];
		$all_content[$i]['cate_title']=$cate_title;
		$all_content[$i]['google']=$google;
		$all_content[$i]['cate_color']=$cate_color;
		$all_content[$i]['cate_bgcolor']=$cate_bgcolor;
		$all_content[$i]['cate_handle']=$cate_handle;
		$i++;
	}


	$xoopsTpl->assign('bar',$bar);
	$xoopsTpl->assign('all_content',$all_content);
	$xoopsTpl->assign('jquery',get_jquery(true));
}



//刪除tad_cal_cate某筆資料資料
function delete_tad_cal_cate($cate_sn=""){
	global $xoopsDB;
	$sql = "delete from ".$xoopsDB->prefix("tad_cal_cate")." where cate_sn='$cate_sn'";
	$xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
	
	$sql = "delete from ".$xoopsDB->prefix("tad_cal_event")." where cate_sn='$cate_sn'";
	$xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
}



//連到Google行事曆
function link_to_google($id="",$pass=""){
  global $xoopsDB,$xoopsTpl;
  //抓出現有google行事曆
	$sql = "select `cate_title`,`cate_handle` from ".$xoopsDB->prefix("tad_cal_cate")." where `cate_handle`!=''";
	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
	while(list($cate_title , $cate_handle)=$xoopsDB->fetchRow($result)){
    $all_handle[]=$cate_handle;
    $cate_title_arr[$cate_handle]=$cate_title;
  }
  
  
  require "../class/gcalendar.class.php";
  $gmail=new GCalendar($id,$pass);
  $gmail->authenticate();

  $Calendars=$gmail->getOwnCalendars();
  $i=0;
  $all="";
  foreach($Calendars as $j=>$cal){
    $Events=$gmail->getEvents($cal['handle'], 10);
    if(empty($Events['data']['items']))continue;

    $all[$i]['cate_handle']=$cate_handle;
    $all[$i]['cate_title']=$cate_title_arr[$cal['handle']];
    $all[$i]['totalResults']=$Events['data']['totalResults'];
    $all[$i]['cal_title']=$cal['title'];
    $all[$i]['handle']=$cal['handle'];
    $all[$i]['in_array']=in_array($cal['handle'],$all_handle);
    $all[$i]['j']=$j;
    $i++;
  }


  $xoopsTpl->assign('id',$id);
  $xoopsTpl->assign('pass',$pass);
  $xoopsTpl->assign('op','link_to_google');
  $xoopsTpl->assign('all',$all);
  
  
}


//新增資料到tad_cal_cate中
function save_google(){
	global $xoopsDB,$xoopsUser;
	
  //抓出現有google行事曆
	$sql = "select `cate_sn`,`cate_handle` from ".$xoopsDB->prefix("tad_cal_cate")." where `cate_handle`!=''";
	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
	while(list($cate_sn , $cate_handle)=$xoopsDB->fetchRow($result)){
    $all_handle[]=$cate_handle;
    $cate_sn_arr[$cate_handle]=$cate_sn;
  }

	$myts =& MyTextSanitizer::getInstance();
	$_POST['google_id']=$myts->addSlashes($_POST['google_id']);
	$_POST['google_pass']=$myts->addSlashes($_POST['google_pass']);
	
	foreach($_POST['handle'] as $i=>$handle){

  	$title=$myts->addSlashes($_POST['title'][$i]);
  	$enable_group="";
    $enable_upload_group="1";
    $sort=tad_cal_cate_max_sort();
    
    if(!in_array($handle,$all_handle)){
      $cate_sn=create_cate($title,$sort,$handle,$enable_group,$enable_upload_group,$_POST['google_id'],$_POST['google_pass']);
    }else{
    	$sql = "update ".$xoopsDB->prefix("tad_cal_cate")." set `cate_title`='{$title}' , `google_id`='{$_POST['google_id']}' , `google_pass`='{$_POST['google_pass']}' where `cate_handle`='{$handle}'";
    	$xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
    	$cate_sn=$cate_sn_arr[$handle];
    }
    import_google($cate_sn);
	}
	return;
}


/*-----------執行動作判斷區----------*/
$op = (!isset($_REQUEST['op']))? "":$_REQUEST['op'];
$cate_sn = (!isset($_REQUEST['cate_sn']))? "":intval($_REQUEST['cate_sn']);

switch($op){
	/*---判斷動作請貼在下方---*/
	
	case "tad_cal_add_gcal_form":
	tad_cal_add_gcal_form();
	break;
	
  //替換資料
  case "replace_tad_cal_cate":
  replace_tad_cal_cate();
  header("location: {$_SERVER['PHP_SELF']}");
  break;

  //新增資料
  case "insert_tad_cal_cate":
  $cate_sn=insert_tad_cal_cate();
  header("location: {$_SERVER['PHP_SELF']}");
  break;

  //更新資料
  case "update_tad_cal_cate":
  update_tad_cal_cate($cate_sn);
  header("location: {$_SERVER['PHP_SELF']}");
  break;
  //輸入表格
  case "tad_cal_cate_form":
  tad_cal_cate_form($cate_sn);
  break;

  //刪除資料
  case "delete_tad_cal_cate":
  delete_tad_cal_cate($cate_sn);
  header("location: {$_SERVER['PHP_SELF']}");
  break;
  
  //連到Google
  case "link_to_google":
  link_to_google($_POST['google_id'],$_POST['google_pass']);
  break;
  
  //儲存Google設定
  case "save_google":
  save_google();
  header("location: {$_SERVER['PHP_SELF']}");
  break;
  
  case "import_google":
  import_google($cate_sn);
  redirect_header($_SERVER['PHP_SELF'],3, _MA_TADCAL_GOOGLE_IMPORT_OK);
  break;
  
  case "tad_cal_all_sync":
  tad_cal_all_sync();
  redirect_header($_SERVER['PHP_SELF'],3, _MA_TADCAL_GOOGLE_IMPORT_OK);
  break;

  //預設動作
  default:
 	list_tad_cal_cate();
  break;

	
	/*---判斷動作請貼在上方---*/
}

/*-----------秀出結果區--------------*/
include_once 'footer.php';
?>
