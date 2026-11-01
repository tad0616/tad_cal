<?php
namespace XoopsModules\Tad_cal;

use XoopsModules\Tadtools\Utility;

/*
Update Class Definition

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
 * Class Update
 */
class Update
{
    //刪除錯誤的重複欄位及樣板檔
    public static function chk_tad_cal_block()
    {
        global $xoopsDB;
        //die(var_export($xoopsConfig));
        require XOOPS_ROOT_PATH . '/modules/tad_cal/xoops_version.php';

        //先找出該有的區塊以及對應樣板
        foreach ($modversion['blocks'] as $i => $block) {
            $show_func = $block['show_func'];
            $tpl_file_arr[$show_func] = $block['template'];
            $tpl_desc_arr[$show_func] = $block['description'];
        }

        //找出目前所有的樣板檔
        $sql = 'SELECT bid,name,visible,show_func,template FROM `' . $xoopsDB->prefix('newblocks') . "`
        WHERE `dirname` = 'tad_cal' ORDER BY `func_num`";
        $result = $xoopsDB->query($sql);
        while (list($bid, $name, $visible, $show_func, $template) = $xoopsDB->fetchRow($result)) {
            //假如現有的區塊和樣板對不上就刪掉
            if ($template != $tpl_file_arr[$show_func]) {
                $sql = 'delete from ' . $xoopsDB->prefix('newblocks') . " where bid='{$bid}'";
                $xoopsDB->queryF($sql);

                //連同樣板以及樣板實體檔案也要刪掉
                $sql = 'delete from ' . $xoopsDB->prefix('tplfile') . ' as a
            left join ' . $xoopsDB->prefix('tplsource') . "  as b on a.tpl_id=b.tpl_id
            where a.tpl_refid='$bid' and a.tpl_module='tad_cal' and a.tpl_type='block'";
                $xoopsDB->queryF($sql);
            } else {
                $sql = 'update ' . $xoopsDB->prefix('tplfile') . "
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
        WHERE table_name = '" . $xoopsDB->prefix('tad_cal_event') . "' AND COLUMN_NAME = 'uid'";
        $result = $xoopsDB->query($sql);
        list($type) = $xoopsDB->fetchRow($result);
        if ('smallint' === $type) {
            return true;
        }

        return false;
    }

    //執行更新
    public static function chk_chk1()
    {
        global $xoopsDB;
        $sql = 'ALTER TABLE `' . $xoopsDB->prefix('tad_cal_event') . '` CHANGE `uid` `uid` mediumint(8) unsigned NOT NULL default 0';
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);

        return true;
    }

    public static function chk_google_sync_tables()
    {
        global $xoopsDB;

        $calendarTable = $xoopsDB->prefix('tad_cal_google_calendar');
        $eventTable = $xoopsDB->prefix('tad_cal_google_event');
        $queries = [
            "CREATE TABLE IF NOT EXISTS `{$calendarTable}` (
                `cate_sn` smallint(6) unsigned NOT NULL COMMENT '行事曆分類編號',
                `calendar_id` varchar(255) NOT NULL COMMENT 'Google 行事曆 ID',
                `refresh_token` text NOT NULL COMMENT '加密後的 Google OAuth refresh token',
                `sync_token` text NOT NULL COMMENT 'Google 增量同步 token',
                `initial_sync` varchar(32) NOT NULL COMMENT '首次同步策略',
                `last_sync` datetime DEFAULT NULL COMMENT '最後同步時間',
                PRIMARY KEY (`cate_sn`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$eventTable}` (
                `cate_sn` smallint(6) unsigned NOT NULL COMMENT '行事曆分類編號',
                `local_sn` smallint(6) unsigned DEFAULT NULL COMMENT '本地事件編號',
                `google_event_id` varchar(255) DEFAULT NULL COMMENT 'Google 事件 ID',
                `google_updated` datetime DEFAULT NULL COMMENT 'Google 事件更新時間',
                `local_baseline` datetime DEFAULT NULL COMMENT '本地事件同步基準時間',
                `sync_pending` tinyint(1) NOT NULL DEFAULT 0 COMMENT '本地待同步狀態',
                UNIQUE KEY `google_event` (`cate_sn`,`google_event_id`),
                UNIQUE KEY `local_event` (`cate_sn`,`local_sn`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8",
        ];

        foreach ($queries as $sql) {
            $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        }

        return true;
    }

}
