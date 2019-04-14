<?php

//區塊主函式 (待辦事項(tad_cal_list))
function tad_cal_list($options)
{
    global $xoopsDB, $xoopsUser, $xoopsTpl, $xoTheme;
    $xoTheme->addStylesheet('modules/tadtools/css/vertical_menu.css');

    require_once XOOPS_ROOT_PATH . '/modules/tad_cal/function_block.php';

    //取得目前使用者可讀的群組
    $ok_cate_arr = chk_tad_cal_cate_power('enable_group');
    $all_ok_cate = implode(',', $ok_cate_arr);
    $and_ok_cate = empty($all_ok_cate) ? "and cate_sn='0'" : "and cate_sn in($all_ok_cate)";
    $and_ok_cate2 = empty($all_ok_cate) ? "and a.sn='0'" : "and b.cate_sn in($all_ok_cate)";

    $even_start = date('Y-m-d 00:00:00');
    $even_end = date('Y-m-d 23:59:59', strtotime("+{$options[0]} days"));

    //抓出事件
    $sql = 'select * from ' . $xoopsDB->prefix('tad_cal_event') . " where `start` >= '$even_start' and `end` <= '$even_end' $and_ok_cate order by `start` , `sequence`";
    //die($sql);

    $result = $xoopsDB->query($sql) or web_error($sql, __FILE__, __LINE__);
    $i = 0;
    $all_event = [];
    while (false !== ($all = $xoopsDB->fetchArray($result))) {
        //以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
        foreach ($all as $k => $v) {
            $$k = $v;
        }
        if (!empty($recurrence)) {
            continue;
        }

        $start = mb_substr($start, 0, 10);
        $all_event[$start][$sn] = $title;
    }

    //抓出重複事件
    $sql = 'select a.*,b.title,b.cate_sn from ' . $xoopsDB->prefix('tad_cal_repeat') . ' as a join ' . $xoopsDB->prefix('tad_cal_event') . " as b on a.sn=b.sn where a.`start` >= '$even_start' and a.`end` <= '$even_end' $and_ok_cate2 order by a.`start`";
    //die($sql);
    $result = $xoopsDB->queryF($sql) or web_error($sql, __FILE__, __LINE__);

    while (false !== ($all = $xoopsDB->fetchArray($result))) {
        //以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
        foreach ($all as $k => $v) {
            $$k = $v;
        }

        $start = mb_substr($start, 0, 10);
        $all_event[$start][$sn] = $title;
    }

    $content = $event = [];
    $i = 0;
    if (is_array($all_event)) {
        foreach ($all_event as $start => $arr) {
            $j = 0;
            foreach ($arr as $sn => $title) {
                $event[$j]['sn'] = $sn;
                $event[$j]['title'] = $title;
                $j++;
            }
            $content[$i]['start'] = $start;
            $content[$i]['event'] = $event;
            $event = [];
            $i++;
        }
    }

    $block['content'] = $content;

    return $block;
}

//區塊編輯函式
function tad_cal_list_edit($options)
{
    $form = "
    <ol class='my-form'>
        <li class='my-row'>
            <lable class='my-label'>" . _MB_TADCAL_TAD_CAL_LIST_EDIT_BITEM0 . "</lable>
            <div class='my-content'>
                <input type='text' class='my-input' name='options[0]' value='{$options[0]}' size=6>
            </div>
        </li>
    </ol>";

    return $form;
}
