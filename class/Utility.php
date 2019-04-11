<?php namespace XoopsModules\Tad_cal;

/*
 Utility Class Definition

 You may not change or alter any portion of this comment or credits of
 supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit
 authors.

 This program is distributed in the hope that it will be useful, but
 WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @license      http://www.fsf.org/copyleft/gpl.html GNU public license
 * @copyright    https://xoops.org 2001-2017 &copy; XOOPS Project
 * @author       Mamba <mambax7@gmail.com>
 */


/**
 * Class Utility
 */
class Utility
{
    //刪除錯誤的重複欄位及樣板檔
    public static function chk_tad_cal_block()
    {
        global $xoopsDB;
        //die(var_export($xoopsConfig));
        include XOOPS_ROOT_PATH . '/modules/tad_cal/xoops_version.php';

        //先找出該有的區塊以及對應樣板
        foreach ($modversion['blocks'] as $i => $block) {
            $show_func                = $block['show_func'];
            $tpl_file_arr[$show_func] = $block['template'];
            $tpl_desc_arr[$show_func] = $block['description'];
        }

        //找出目前所有的樣板檔
        $sql = "SELECT bid,name,visible,show_func,template FROM `" . $xoopsDB->prefix("newblocks") . "`
    WHERE `dirname` = 'tad_cal' ORDER BY `func_num`";
        $result = $xoopsDB->query($sql);
        while (list($bid, $name, $visible, $show_func, $template) = $xoopsDB->fetchRow($result)) {
            //假如現有的區塊和樣板對不上就刪掉
            if ($template != $tpl_file_arr[$show_func]) {
                $sql = "delete from " . $xoopsDB->prefix("newblocks") . " where bid='{$bid}'";
                $xoopsDB->queryF($sql);

                //連同樣板以及樣板實體檔案也要刪掉
                $sql = "delete from " . $xoopsDB->prefix("tplfile") . " as a
            left join " . $xoopsDB->prefix("tplsource") . "  as b on a.tpl_id=b.tpl_id
            where a.tpl_refid='$bid' and a.tpl_module='tad_cal' and a.tpl_type='block'";
                $xoopsDB->queryF($sql);
            } else {
                $sql = "update " . $xoopsDB->prefix("tplfile") . "
            set tpl_file='{$template}' , tpl_desc='{$tpl_desc_arr[$show_func]}'
            where tpl_refid='{$bid}'";
                $xoopsDB->queryF($sql);
            }
        }

    }

    //修正uid欄位
    public static function chk_uid()
    {
        global $xoopsDB;
        $sql = "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS
  WHERE table_name = '" . $xoopsDB->prefix("tad_cal_event") . "' AND COLUMN_NAME = 'uid'";
        $result     = $xoopsDB->query($sql);
        list($type) = $xoopsDB->fetchRow($result);
        if ($type == 'smallint') {
            return true;
        }

        return false;
    }

    //執行更新
    public static function chk_chk1()
    {
        global $xoopsDB;
        $sql = "ALTER TABLE `" . $xoopsDB->prefix("tad_cal_event") . "` CHANGE `uid` `uid` mediumint(8) unsigned NOT NULL default 0";
        $xoopsDB->queryF($sql) or web_error($sql, __FILE__, __LINE__);
        return true;
    }


    //建立目錄

    public static function mk_dir($dir = "")
    {
        //若無目錄名稱秀出警告訊息
        if (empty($dir)) {
            return;
        }

        //若目錄不存在的話建立目錄
        if (!is_dir($dir)) {
            umask(000);
            //若建立失敗秀出警告訊息
            mkdir($dir, 0777);
        }
    }


//拷貝目錄

    public static function full_copy($source = "", $target = "")
    {
        if (is_dir($source)) {
            @mkdir($target);
            $d = dir($source);
            while (false !== ($entry = $d->read())) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }

                $Entry = $source . '/' . $entry;
                if (is_dir($Entry)) {
                    self::full_copy($Entry, $target . '/' . $entry);
                    continue;
                }
                copy($Entry, $target . '/' . $entry);
            }
            $d->close();
        } else {
            copy($source, $target);
        }
    }



    public static function rename_win($oldfile, $newfile)
    {
        if (!rename($oldfile, $newfile)) {
            if (copy($oldfile, $newfile)) {
                unlink($oldfile);
                return true;
            }
            return false;
        }
        return true;
    }



    public static function delete_directory($dirname)
    {
        if (is_dir($dirname)) {
            $dir_handle = opendir($dirname);
        }

        if (!$dir_handle) {
            return false;
        }

        while ($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                if (!is_dir($dirname . "/" . $file)) {
                    unlink($dirname . "/" . $file);
                } else {
                    self::directory($dirname . '/' . $file);
                }
            }
        }
        closedir($dir_handle);
        rmdir($dirname);
        return true;
    }

}
