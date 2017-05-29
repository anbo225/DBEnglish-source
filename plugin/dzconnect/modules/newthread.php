<?php
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
} 
// var_dump($GLOBALS); die();
global $_G;
// $tid = isset($GLOBALS['tid']) ? (int)$GLOBALS['tid'] : 0;
// $pid = isset($GLOBALS['pid']) ? (int)$GLOBALS['pid'] : 0;
$tid = isset($param['param'][2]['tid']) ? (int)$param['param'][2]['tid'] : 0;
$pid = isset($param['param'][2]['pid']) ? (int)$param['param'][2]['pid'] : 0;
if ($tid >= 1 && $pid >= 1 && isset($_POST['syncList'])) {
	if (!empty($_POST['syncList'])) {
		include_once DZC_PLUGDIR . '/load.php';
		dsetcookie('last_sync_list_' . $_G['uid'], $_POST['syncList'], 2592000);
		dzc_newthread_sync($tid, $pid, (string)$GLOBALS['subject'], str_replace('attachimg]', 'attach]', (string)$GLOBALS['message']));
	} else {
		dsetcookie('last_sync_list_' . $_G['uid'], 1, 604800);
	} 
} 

?>