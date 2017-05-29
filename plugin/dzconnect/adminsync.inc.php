<?php
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
} 
if (!$_G['adminid']) {
	return false;
}
include_once dirname(__FILE__) . '/load.php';
if (isset($_POST['editsubmit'])) {
	$default = array ('version' => DZC_VERSION,
		'sync_list' => ($_POST['sync_list']) ? implode(',', $_POST['sync_list']) : '',
		'is_synctopic_toweibo' => dzc_check_value($_POST['is_synctopic_toweibo']),
		'is_syncdoing_toweibo' => dzc_check_value($_POST['is_syncdoing_toweibo']),
		'is_syncblog_toweibo' => dzc_check_value($_POST['is_syncblog_toweibo']), 
		'is_syncreply_toweibo' => dzc_check_value($_POST['is_syncreply_toweibo']),
		'is_sync_pic' => dzc_check_value($_POST['is_sync_pic'])
		);
	cw_updateSetData($default);
} 
include DZC_PLUGDIR . '/set.data.php';
$set = $_DZC_SET;
$sync_list = array('sina', 'qq');
$sync_list = array_fill_keys($sync_list, 0);
if ($set['sync_list']) {
	$sync_list = array_fill_keys(explode(',', $set['sync_list']), 1) + $sync_list; 
	// $sync_list = array_merge($sync_list, array_fill_keys(explode(',', $set['sync_list']), 1));
} 
$sync_list2 = array('qzone', 'renren', 'kaixin001', 'sohu', 'netease', 'douban', 'tianya', 'twitter', 'facebook');
include template('dzconnect:adminsync');

?>