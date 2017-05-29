<?php
include_once dirname(__FILE__) . '/load.php';

if (!$_G['adminid']) {
	return false;
}

$myurl = $_G['siteurl'] . '/sina.php';

$offset = 10;
$step = intval($_G['gp_s']) * $offset; 

// 检查新浪数据表是否存在
$xl_tb = DB :: table("xwb_bind_info");
$sql = "show tables like '$xl_tb'";
$r = DB :: result_first($sql);
if ($r != $xl_tb) {
	showMsg($_DZC_LANG['sina_no_exists']);
} 
// 检查是否成功安装连接微博
$dl_tb = DB :: table("dzc_user_bind");
$sql = "show tables like '$dl_tb'";
$r = DB :: result_first($sql);
if ($r != $dl_tb) {
	showMsg($_DZC_LANG['dzconnect_no_exists']);
} 

// 检查新浪表字段是否正常
$sql = "desc " . DB :: table("xwb_bind_info");
$q = DB :: query($sql);
while ($arr = DB :: fetch($q)) {
	$field[] = $arr['Field'];
} 
if (!(in_array('sina_uid', $field) && in_array('uid', $field))) {
	showMsg($_DZC_LANG['sina_table_false']);
} 

// 开始转换
$sql = "select uid,sina_uid from " . DB :: table("xwb_bind_info") . " limit $step,$offset";
$q = DB :: query($sql);
while ($a = DB :: fetch($q)) {
	$ret[$a['sina_uid']] = $a['uid'];
} 
empty($ret) && showMsg($_DZC_LANG['sina_switch_success']); 

$name = 'sina';
$n = 0;
foreach($ret as $k => $v) {
	$uid = dzc_getUid($name, $k);
	if (!$uid) {
		dzc_addUser($name, $k, '', $v);
	} else {
		continue;
	}
	$n++;
} 

$s = $step / $offset;
$s += 1;
$n = $n + (int) $_G['gp_n'];
showMsg($_DZC_LANG['success_update'] . $n . $_DZC_LANG['records'], $myurl . '?s=' . $s .'&n=' . $n);

function showMsg($msg, $url = false) {
	global $_G, $_DZC_LANG;
	echo '<div id="messagetext" class="alert_info"><p>' . $msg . '</p>';
	if ($url != false) {
		echo '<p class="alert_btnleft"><a href="' . $url . '">' . $_DZC_LANG['jump_tip'] . '</a></p>';
		echo '<script type="text/javascript" reload="1">setTimeout("window.location.href =\'' . $url . '\';", 3000);</script>';
	} 
	echo '</div>';
	exit();
} 

?>
