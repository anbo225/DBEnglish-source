<?php
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
} 
// var_dump($GLOBALS); die();
global $_G;
// $tid = isset($_G['gp_tid']) ? (int)$_G['gp_tid'] : 0;
// $pid = isset($GLOBALS['pid']) ? (int)$GLOBALS['pid'] : 0;
$tid = isset($param['param'][2]['tid']) ? (int)$param['param'][2]['tid'] : 0;
$pid = isset($param['param'][2]['pid']) ? (int)$param['param'][2]['pid'] : 0;
$code = array($tid, $pid, $param['param'], $GLOBALS['subject'], $GLOBALS['message']);
if ($tid >= 1 && $pid >= 1) {
	include_once DZC_PLUGDIR . '/load.php';
	dzc_reply_sync($tid, $pid, (string)$GLOBALS['message']);
} 

?>