<?php
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
} 

include_once dirname(__FILE__) . '/load.php';
// 写入起始设置
$setData = array('version' => DZC_VERSION);
cw_updateSetData($setData);

?>