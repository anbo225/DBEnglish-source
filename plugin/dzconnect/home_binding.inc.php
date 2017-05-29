<?php
if (!defined('IN_DISCUZ')) {
	exit('ACCESS DENIED');
} 
if (!$_G['uid']) return;
include_once dirname(__FILE__) . '/load.php';
@include dirname(__FILE__) . '/set.weixin.php';
if (!$weixin['qrcode'] || !$weixin['account']) {
	$hide_weixin = 1;
}
$tag = $_G['gp_tag'] ? $_G['gp_tag'] : $_GET['tag'];
if (is_array($_DZC_SET)) {
	if (empty($_DZC_SET['login_list']) && !empty($_DZC_SET['sync_list'])) {
		$hide_login = 1;
		if ($tag == '') $tag = 'sync';
	} elseif (empty($_DZC_SET['sync_list']) && !empty($_DZC_SET['login_list'])) {
		$hide_sync = 1;
	} elseif (empty($_DZC_SET['login_list']) && empty($_DZC_SET['sync_list'])) {
		$hide_sync = $hide_login = 1;
		$tag = 'weixin';
	} 
} 
if ($tag == '') {
	$user = dzc_getUserAll();
	$weibo_list = array('qzone', 'sina', 'qq', 'renren', 'kaixin001', 'sohu', 'douban', 'taobao', 'alipay', 'baidu', 'google', '360', 'tianya', 'twitter', 'facebook', 'msn', 'yahoo');
	if (!empty($_DZC_SET['login_list'])) {
		$weibo = array_flip(array_intersect($weibo_list, explode(',', $_DZC_SET['login_list'])));
		if ($user) $weibo = array_sort_key($weibo, $user); // 已绑定的排前面
	} else {
		$weibo = array();
	} 
	$bind_list = '';
	foreach ($weibo as $k => $v) {
		if ($xx = $user[$k]) {
			$u = unserialize($xx['userdata']);
			$bind_list .= "<tr><td>$_DZC_LANG[$k]</td><td><span class=\"xi2\">";
			if ($u['url']) {
				$bind_list .= "<a href=\"{$u['url']}\" target=\"_blank\"><b>{$u['name']}</b></a>";
			} elseif ($u['name']) {
				$bind_list .= "<b>{$u['name']}</b>";
			} else {
				$bind_list .= "<b>{$xx['openid']}</b>";
			}
			$bind_list .= "</span></td><td><a href=\"plugin.php?id=dzconnect:login&act=delete&go={$k}\">{$_DZC_LANG['unbind']}</a></td></tr>";
		} else {
			$bind_list .= "<tr><td>$_DZC_LANG[$k]</td><td>-</td><td class=\"xi2\"><a href=\"plugin.php?id=dzconnect:login&go={$k}\">{$_DZC_LANG['bind']}{$_DZC_LANG[$k]}</a></td></tr>";
		} 
	} 
} elseif ($tag == 'sync') {
	$user = dzc_get_account();
	$weibo_list = array('qzone', 'sina', 'qq', 'renren', 'kaixin001', 'sohu', 'douban', 'tianya', 'twitter', 'facebook');
	if (!empty($_DZC_SET['sync_list'])) {
		$weibo = array_flip(array_intersect($weibo_list, explode(',', $_DZC_SET['sync_list'])));
		if ($user) $weibo = array_sort_key($weibo, $user); // 已绑定的排前面
	} else {
		$weibo = array();
	} 
	$bind_list = '';
	foreach ($weibo as $k => $v) {
		if ($u = $user[$k]) {
			$bind_list .= "<tr><td>$_DZC_LANG[$k]</td>";
			$bind_list .= $u['url'] ? "<td><span class=\"xi2\"><a href=\"{$u['url']}\" target=\"_blank\"><b>{$u['name']}</b></a></span></td>" : "<td><span class=\"xi2\"><b>{$u['name']}</b></span></td>";
			if ($u['expires_in']) {
				$bind_list .= (time() < $u['expires_in']) ? "<td>{$_DZC_LANG['expires_in']}" . date('Y-m-d', $u['expires_in']) : "<td style=\"color:red\">" . $_DZC_LANG['expired'];
			} else {
				$bind_list .= '<td>' . $_DZC_LANG['permanent'];
			} 
			$bind_list .= "<br /><span class=\"xi2\"><a href=\"plugin.php?id=dzconnect:bind&go={$k}\">{$_DZC_LANG['update_authorization']}</a></span></td>";
			$bind_list .= "<td><a href=\"plugin.php?id=dzconnect:bind&act=delete&go={$k}\">{$_DZC_LANG['unbind']}</a></td></tr>";
		} else {
			$bind_list .= "<tr><td>$_DZC_LANG[$k]</td><td>-</td><td>-<br />&nbsp;</td><td class=\"xi2\"><a href=\"plugin.php?id=dzconnect:bind&go={$k}\">{$_DZC_LANG['bind']}{$_DZC_LANG[$k]}</a></td></tr>";
		} 
	} 
} elseif ($tag == 'weixin') {
	if (!$hide_weixin) {
		$user = dzc_getUser('weixin');
		$weixin_bind = $user['openid'];
	} else {
		exit($_DZC_LANG['home_weixin_error']);
	}
} else {
	exit('ACCESS DENIED');
} 
