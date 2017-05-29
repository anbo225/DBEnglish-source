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
		'login_list' => ($_POST['login_list']) ? implode(',', $_POST['login_list']) : '',
		'is_display_login_button' => dzc_check_value($_POST['is_display_login_button']),
		'is_display_login_button_in_fastpost_box' => dzc_check_value($_POST['is_display_login_button_in_fastpost_box'])
		);
	cw_updateSetData($default);
} 
include DZC_PLUGDIR . '/set.data.php';
$set = $_DZC_SET;
$login_list = array('sina', 'qq');
$login_list = array_fill_keys($login_list, 0);
if ($set['login_list']) {
	$login_list = array_fill_keys(explode(',', $set['login_list']), 1) + $login_list;
} 
$login_list2 = array('qzone', 'renren', 'kaixin001', 'sohu', 'netease', 'douban', 'taobao', 'alipay', 'baidu', 'google', '360', 'tianya', 'twitter', 'facebook', 'msn', 'yahoo');
include template('dzconnect:adminlogin');

?>