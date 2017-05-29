<?php
define('DZC_VERSION', '1.0.9'); // 版本
define('DZC_PLUGDIR', dirname(__FILE__));
define('DZC_ROOT', DZC_PLUGDIR . '/../../../');

if (!defined('IN_DISCUZ')) {
	require_once DZC_ROOT . 'source/class/class_core.php';
	$discuz = &discuz_core :: instance();
	$discuz -> init(); 
	// } else {
	// $discuz = &discuz_core :: instance();
} 

global $_G, $_DZC_LANG, $_DZC_TIPS, $_DZC_SHARE_TITLE;

@include DZC_PLUGDIR . '/set.data.php';

define("BJTIMESTAMP", ($_DZC_SET['minutes'] && $_DZC_SET['char']) ? time() + ($_DZC_SET['char'] * $_DZC_SET['minutes'] * 60) : time()); //服务器时间校正
define('OS_DZ_VERSION', substr($_G['setting']['version'], 1)); // 版本
define('OS_DZ_CHARSET', str_replace("-", "", strtoupper($_G['charset']))); // 编码
define('OS_DZ_NAMR', $_G['setting']['bbname']);
define('OS_DZ_URL', rtrim(str_replace('source/plugin/dzconnect', '', $_G['siteurl']), '/'));
define('DZC_PLUGURL', OS_DZ_URL . '/source/plugin/dzconnect');
define('DZC_SSO', OS_DZ_URL . '/plugin.php?id=dzconnect:');
define('DZC_LOGIN', DZC_PLUGURL . '/login.php');
// 判断语言
function _dzc_lang() {
	$charset = strtolower(OS_DZ_CHARSET);
	if ($charset == 'utf8') {
		global $_G;
		if ($_G['config']['output']['language'] == 'zh_tw') { // 繁体
			return $charset . '_TC';
		} 
	} 
	return $charset;
} 

require DZC_PLUGDIR . '/lang/' . _dzc_lang() . '.php';

include DZC_PLUGDIR . '/functions.php';
include DZC_PLUGDIR . '/discuz.func.php';

?>