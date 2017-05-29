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
		'time_char' => dzc_check_value($_POST['time_char']),
		'time_minutes' => dzc_check_value($_POST['time_minutes'])
		);
	cw_updateSetData($default);
} 
include DZC_PLUGDIR . '/set.data.php';
$set = $_DZC_SET;

include template('dzconnect:admincp');

?>