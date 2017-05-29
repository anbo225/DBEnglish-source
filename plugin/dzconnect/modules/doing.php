<?php
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
} 
// var_dump($GLOBALS); die();
global $_G;
if (isset($_POST['syncList']) && empty($_POST['syncList'])) {
	dsetcookie('last_sync_list_' . $_G['uid'], 1, 604800);
	return;
} 
$newdoid = isset($GLOBALS['newdoid']) ? (int)$GLOBALS['newdoid'] : 0;
$message = !empty($GLOBALS['message']) ? (string)$GLOBALS['message'] : '';
if ($newdoid > 0 && !empty($message)) {
	include_once DZC_PLUGDIR . '/load.php';
	dzc_doing_sync($newdoid, $message);
	if (!empty($_POST['syncList'])) {
		dsetcookie('last_sync_list_' . $_G['uid'], $_POST['syncList'], 2592000);
	} 
} 
