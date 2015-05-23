CREATE TABLE tad_cbox (
  `sn` mediumint(9) unsigned NOT NULL  auto_increment ,
  `publisher` varchar(255) NOT NULL default '' ,
  `msg` text NOT NULL ,
  `post_date` datetime NOT NULL default '0000-00-00 00:00:00' ,
  `ip` varchar(255) NOT NULL default '' ,
  `only_root` enum('0','1') NOT NULL default '0' ,
  `root_msg` text NOT NULL ,
  `uid` mediumint(8) unsigned NOT NULL default 0 ,
  `box_sn` smallint(6) unsigned NOT NULL default 0 ,
  `re_sn` mediumint(9) unsigned NOT NULL default 0 ,
  PRIMARY KEY  (`sn`),
  KEY `box_sn` (`box_sn`)
) ENGINE=MyISAM;