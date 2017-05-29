<?php
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
} 
if (!$_G['adminid']) {
	return false;
}
include_once dirname(__FILE__) . '/load.php';
$token = substr(md5(OS_DZ_URL), 8, 16);
$welcome = sprintf($_DZC_LANG['weixin_welcome'], OS_DZ_NAMR);
$newest = 'n';
$random = 'r';
$nofound = $_DZC_LANG['weixin_nofound'];
$social = wp_social_share_title();
$options = array_keys($social);
include template('dzconnect:adminkey');

?>