<?php
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
} 
// var_dump($GLOBALS); die();
global $_G;
$blogid = isset($GLOBALS['newblog']['blogid']) ? (int)$GLOBALS['newblog']['blogid'] : 0;
$friend = isset($GLOBALS['newblog']['friend']) ? (int)$GLOBALS['newblog']['friend'] : 3; // “全站用户可见”的才同步
if ($blogid > 0 && $friend === 0 && isset($_POST['syncList'])) {
	if (!empty($_POST['syncList'])) {
		include_once DZC_PLUGDIR . '/load.php';
		dsetcookie('last_sync_list_' . $_G['uid'], $_POST['syncList'], 2592000);
		dzc_blog_sync($blogid, (string)$GLOBALS['newblog']['subject'], (string)getgpc('message'));
	} else {
		dsetcookie('last_sync_list_' . $_G['uid'], 1, 604800);
	} 
} 

?>