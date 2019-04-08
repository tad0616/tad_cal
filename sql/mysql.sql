CREATE TABLE `tad_cal_cate` (
  `cate_sn` smallint(6) unsigned NOT NULL auto_increment COMMENT '行事曆編號',
  `cate_title` varchar(255) NOT NULL COMMENT '行事曆標題',
  `cate_sort` smallint(6) unsigned NOT NULL COMMENT '行事曆排序',
  `cate_enable` enum('1','0') NOT NULL COMMENT '是否啟用',
  `cate_handle` varchar(255) NOT NULL COMMENT '遠端行事曆',
  `enable_group` varchar(255) NOT NULL COMMENT '可讀群組',
  `enable_upload_group` varchar(255) NOT NULL COMMENT '可寫群組',
  `google_id` varchar(255) NOT NULL COMMENT 'google帳號',
  `google_pass` varchar(255) NOT NULL COMMENT 'google密碼',
  `cate_bgcolor` varchar(255) NOT NULL COMMENT '背景顏色',
  `cate_color` varchar(255) NOT NULL COMMENT '前景顏色',
  PRIMARY KEY (`cate_sn`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `tad_cal_event` (
  `sn` smallint(6) unsigned NOT NULL auto_increment COMMENT '事件編號',
  `title` varchar(255) NOT NULL COMMENT '事件標題',
  `start` datetime NOT NULL COMMENT '事件起始時間',
  `end` datetime NOT NULL COMMENT '事件結束時間',
  `recurrence` text NOT NULL COMMENT '事件重複',
  `location` varchar(255) NOT NULL COMMENT '事件地點',
  `kind` varchar(255) NOT NULL COMMENT '事件種類',
  `details` text NOT NULL COMMENT '事件內容',
  `etag` varchar(255) NOT NULL COMMENT '事件etag',
  `id` varchar(255) NOT NULL COMMENT '事件id',
  `sequence` tinyint(3) unsigned NOT NULL COMMENT '事件排序',
  `uid` mediumint(8) unsigned NOT NULL COMMENT '發布者',
  `cate_sn` smallint(6) unsigned NOT NULL COMMENT '所屬行事曆',
  `allday` ENUM( '0', '1' ) NOT NULL COMMENT '整天事件',
  `tag` varchar( 255 ) NOT NULL COMMENT '標籤',
  `last_update` DATETIME NOT NULL COMMENT '最後更新時間',
  PRIMARY KEY (`sn`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `tad_cal_repeat` (
  `sn` smallint(5) unsigned NOT NULL COMMENT '事件編號',
  `start` datetime NOT NULL COMMENT '事件起始時間',
  `end` datetime NOT NULL COMMENT '事件結束時間',
  `allday` ENUM( '0', '1' ) NOT NULL COMMENT '整天事件',
  UNIQUE KEY `start` (`sn`,`start`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;