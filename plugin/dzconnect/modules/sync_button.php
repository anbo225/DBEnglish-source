<?php
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
} 
include_once DZC_PLUGDIR . '/load.php';
$get_account = dzc_get_account();
$last_sync_list = getcookie('last_sync_list_' . $_G['uid']); 
// var_dump($_G['cookie']);
if ($get_account) {
	if ($last_sync_list) {
		if ($last_sync_list == 1) {
			foreach ($get_account as $k => $v) {
				$addSyncList .= "<a href='javascript:;' onclick='addSyncList(this, \"$k\");' WB='$k' class='dz_m_link dz_m_no_{$k}' title='{$_DZC_LANG[$k]}'></a> ";
			} 
		} else {
			$last_sync_list = explode(',', $last_sync_list);
			foreach ($get_account as $k => $v) {
				if (in_array($k, $last_sync_list)) {
					$addSyncList .= "<a href='javascript:;' onclick='addSyncList(this, \"$k\");' WB='$k' class='dz_m_link dz_m_{$k}' title='{$_DZC_LANG[$k]}'></a> ";
					$inputIconList .= $k . ',';
				} else {
					$addSyncList .= "<a href='javascript:;' onclick='addSyncList(this, \"$k\");' WB='$k' class='dz_m_link dz_m_no_{$k}' title='{$_DZC_LANG[$k]}'></a> ";
				} 
			} 
		} 
	} else {
		foreach ($get_account as $k => $v) {
			$addSyncList .= "<a href='javascript:;' onclick='addSyncList(this, \"$k\");' WB='$k' class='dz_m_link dz_m_{$k}' title='{$_DZC_LANG[$k]}'></a> ";
			$inputIconList .= $k . ',';
		} 
	} 
	$inputIconList = trim($inputIconList, ',');
} 
include template($this -> pluginid . ':sync_button');

?>