<?php
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
} 

$sql = <<<EOF

CREATE TABLE IF NOT EXISTS pre_dzc_user_bind (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `name` varchar(10) NOT NULL DEFAULT '',
  `openid` varchar(50) NOT NULL,
  `userdata` text NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `uid` (`uid`),
  KEY `userid` (`name`, `openid`)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS pre_dzc_tweets (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tid` bigint(20) unsigned NOT NULL default '0',
  `tweets` text NOT NULL,
  `log` text NOT NULL,
  `type` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `tweet` (`tid`, `type`)
) TYPE=MyISAM;

EOF;

runquery($sql);

$finish = TRUE;

include(dirname(__FILE__). '/install_data.php');

?>