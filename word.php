<?php
include_once "header.php";


$page_title="{$_POST['start']}~{$_POST['end']}"._MD_TADCAL_SIMPLE_CAL;
require_once XOOPS_ROOT_PATH."/modules/tadtools/PHPWord.php";
$PHPWord = new PHPWord();
$PHPWord->setDefaultFontSize(12);     //設定預設字型大小
$sectionStyle = array('orientation' => 'portrait');

$section = $PHPWord->createSection($sectionStyle);

$fontStyle = array('color'=>'000000', 'size'=>20, 'bold'=>true);
$PHPWord->addTitleStyle( 1, $fontStyle );
$section->addTitle( $page_title, 1);

$styleTable = array('borderColor'=>'000000', 'borderSize'=>6, 'cellMargin'=>50);
$styleFirstRow = array('bgColor'=>'66BBFF'); //首行樣式
$PHPWord->addTableStyle('myTable', $styleTable, $styleFirstRow); //建立表格樣式
$table = $section->addTable('myTable');//建立表格

$table->addRow(); //新增一列
$table->addCell(2000, $cellStyle)->addText(_MD_TADCAL_SIMPLE_DATE);
$table->addCell(11000, $cellStyle)->addText(_MD_TADCAL_SIMPLE_EVENT);

$cellStyle =array(); //儲存格樣式（設定項：valign、textDirection、bgColor、borderTopSize、borderTopColor、borderLeftSize、borderLeftColor、borderRightSize、borderRightColor、borderBottomSize、borderBottomColor）


//取得目前使用者可讀的群組
$ok_cate_arr=chk_cate_power('enable_group');

if(!empty($_POST['cate_sn'])){
  foreach($_POST['cate_sn'] as $cate_sn){
    $ok_arr[]=in_array($cate_sn , $ok_cate_arr);
  }
}else{
  $ok_arr[]=$ok_cate_arr;
}

$all_ok_cate=implode(",",$ok_arr);
$and_ok_cate=empty($all_ok_cate)?"and cate_sn='0'":"and cate_sn in($all_ok_cate)";
$and_ok_cate2=empty($all_ok_cate)?"and a.sn='0'":"and b.cate_sn in($all_ok_cate)";

$even_start=$_REQUEST['start'];
$even_end=$_REQUEST['end'];


//抓出事件
$sql = "select * from ".$xoopsDB->prefix("tad_cal_event")." where `start` >= '$even_start' and `end` <= '$even_end' $and_ok_cate  order by `start` , `sequence`";
//die($sql);

$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
$i=0;
while($all=$xoopsDB->fetchArray($result)){
//以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
  foreach($all as $k=>$v){
    $$k=$v;
  }
  if(!empty($recurrence))continue;
  $start=substr($start,0,10);
  $all_event[$start][$sn]=$title;
}

//抓出重複事件
$sql = "select a.*,b.title,b.cate_sn from ".$xoopsDB->prefix("tad_cal_repeat")." as a join ".$xoopsDB->prefix("tad_cal_event")." as b on a.sn=b.sn where a.`start` >= '$even_start' and a.`end` <= '$even_end' $and_ok_cate2  order by a.`start`";
//die($sql);
$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

while($all=$xoopsDB->fetchArray($result)){
//以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
  foreach($all as $k=>$v){
    $$k=$v;
  }

  $start=substr($start,0,10);
  $all_event[$start][$sn]=$title;
}


foreach($all_event as $start=>$arr){
  $table->addRow(); //新增一列
  $table->addCell(2000, $cellStyle)->addText($start); //新增一格
  $cell="";
  foreach($arr as $sn=>$title){
    $cell[]=$title;
  }
  $content=implode("\n",$cell);
  $table->addCell(11000, $cellStyle)->addText($content); //新增一格
}


header('Content-Type: application/vnd.ms-word');
header("Content-Disposition: attachment;filename={$page_title}.docx");
header('Cache-Control: max-age=0');
$objWriter = PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
$objWriter->save('php://output');
?>