<?php
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
} 

define('DZC_PLUGDIR', dirname(__FILE__));

class plugin_dzconnect {
	var $dzc_p_rootname = 'source/plugin/dzconnect';
	var $pluginid = 'dzconnect'; // 插件名称
	function common() {
		global $_G;
		if (!empty($_GET['sso'])) {
			require DZC_PLUGDIR . '/login.php';
			die();
		}
	}

	function global_header() {
		include template($this->pluginid . ':global_header');
		return $return;
	} 

	/**
	 * 在没有登录的情况下，在页面快速登录左边输出登录按钮
	 * 
	 * @return string 
	 */
	function global_login_extra() {
		global $_G;
		if ($_G['uid'] || !dzconnect_base::get('is_display_login_button') || !dzconnect_base::get('login_list')) {
			return '';
		} 
		$_DZC_LANG = dzconnect_base::L();
		$login_list = explode(',', dzconnect_base::get('login_list'));
		$login_num = count($login_list);
		$login_list = array_slice($login_list, 0, ($login_num == 6) ? 6: 5);
		include template($this->pluginid . ':login_button_global_login_extra');
		return $return;
	} 

	/**
	 * 在没有登录的情况下，在页面右上角输出登录按钮
	 * 
	 * @return string 
	 */
	// function global_cpnav_extra2() {
	// return $return;
	// }
	/**
	 * 论坛版块快速发表处显示登录按钮
	 * 
	 * @return string 
	 */
	function global_login_text() {
		global $_G;
		if ($_G['uid'] || !dzconnect_base::get('is_display_login_button_in_fastpost_box') || !dzconnect_base::get('login_list')) {
			return '';
		} 
		$_DZC_LANG = dzconnect_base::L();
		$login_list = explode(',', dzconnect_base::get('login_list'));
		$login_num = count($login_list);
		$login_list = array_slice($login_list, 0, ($login_num == 5) ? 5: 4);
		include template($this->pluginid . ':login_button_global_login_text');
		return $return;
	} 
	/**
	 * 登陆后,在右上角登录状态嵌入点（第二行第一位）
	 */
	function global_usernav_extra3(){
		global $_G;
		$return = '';
		if ($_G['uid'] && dzconnect_base::get('is_display_weixin_button') && !dzconnect_base::getUid('weixin')) {
			$_DZC_LANG = dzconnect_base::L();
			include template($this->pluginid . ':global_usernav_extra3');
		} 
		return $return;
	}
} 
/**
 * “member"钩子
 */
class plugin_dzconnect_member extends plugin_dzconnect {
	/**
	 * 注册钩子“快速登录”处增加登录按钮
	 * 
	 * @return string 
	 */
	function register_logging_method() {
		global $_G;
		if ($_G['uid'] || !dzconnect_base::get('login_list')) {
			return '';
		} 
		$_DZC_LANG = dzconnect_base::L();
		$login_list = explode(',', dzconnect_base::get('login_list'));
		include template($this->pluginid . ':logging_method');
		return $return;
	} 

	/**
	 * 登录钩子“快速登录”处增加登录按钮
	 * 
	 * @return string 
	 */
	function logging_method() {
		global $_G;
		if ($_G['uid'] || !dzconnect_base::get('login_list')) {
			return '';
		} 
		$_DZC_LANG = dzconnect_base::L();
		$login_list = explode(',', dzconnect_base::get('login_list'));
		include template($this->pluginid . ':logging_method');
		return $return;
	} 
} 

/**
 * 论坛forum钩子
 */
class plugin_dzconnect_forum extends plugin_dzconnect {
	/**
	 * 发帖回帖截获钩子：（X2 showmessage()执行时调用）截获pid和tid，用于同步帖子内容到微博
	 * 
	 * @param array $param X2在showmessage传入的参数集合
	 */
	function post_dzconnect_aftersubmit_message($param) {
		global $_G;
		if (!$_G['uid']) {
			return;
		} elseif ($_G['gp_action'] == 'newthread' && isset($param['param'][2]['tid']) && ($param['param'][2]['tid'] > 0) && dzconnect_base::get('is_synctopic_toweibo')) { // 发主题贴
			$result = 1;
		} elseif ($_G['gp_action'] == 'reply' && isset($param['param'][2]['pid']) && ($param['param'][2]['pid'] > 0) && dzconnect_base::get('is_syncreply_toweibo')) { // 发回复
			$result = 2;
		} else {
			return;
		} 

		switch ($result) {
			case 1:
				require DZC_PLUGDIR . '/modules/newthread.php';
				break;
			case 2:
				require DZC_PLUGDIR . '/modules/reply.php';
				break;
			default:
				break;
		} 
	} 
	/**
	 * 发主题帖界面：显示复选框“同步到微博”
	 * 
	 * @return string 
	 */
	function post_middle_output() {
		global $_G;
		$return = '';
		if (!$_G['uid'] || $_G['gp_action'] !== 'newthread' || !dzconnect_base::get('is_synctopic_toweibo') || !dzconnect_base::get('sync_list')) {
			return $return;
		} 
		require DZC_PLUGDIR . '/modules/sync_button.php';
		return $return;
	} 
    /**
     * 论坛版块快速发表处：显示复选框“同步到微博”
     */
	function forumdisplay_fastpost_btn_extra_output() {
		global $_G;
		$return = '';
		if (!$_G['uid'] || !dzconnect_base::get('is_synctopic_toweibo') || !dzconnect_base::get('sync_list')) {
			return $return;
		} 
		require DZC_PLUGDIR . '/modules/sync_button.php';
		return $return;
	} 
} 

/**
 * 群组group钩子
 */
class plugin_dzconnect_group extends plugin_dzconnect {
	/**
	 * 发帖回帖截获钩子：（X2 showmessage()执行时调用）截获pid和tid，用于同步帖子内容到微博
	 * 
	 * @param array $param X2在showmessage传入的参数集合
	 */
	function post_dzconnect_aftersubmit_message($param) {
		global $_G;
		if (!$_G['uid']) {
			return;
		} elseif ($_G['gp_action'] == 'newthread' && isset($param['param'][2]['tid']) && ($param['param'][2]['tid'] > 0) && dzconnect_base::get('is_synctopic_toweibo')) { // 发主题贴
			$result = 1;
		} elseif ($_G['gp_action'] == 'reply' && isset($param['param'][2]['pid']) && ($param['param'][2]['pid'] > 0) && dzconnect_base::get('is_syncreply_toweibo')) { // 发回复
			$result = 2;
		} else {
			return;
		} 

		switch ($result) {
			case 1:
				require DZC_PLUGDIR . '/modules/newthread.php';
				break;
			case 2:
				require DZC_PLUGDIR . '/modules/reply.php';
				break;
			default:
				break;
		} 
	} 
	/**
	 * 发主题帖界面：显示复选框“同步到微博”
	 * 
	 * @return string 
	 */
	function post_middle_output() {
		global $_G;
		$return = '';
		if (!$_G['uid'] || $_G['gp_action'] !== 'newthread' || !dzconnect_base::get('is_synctopic_toweibo') || !dzconnect_base::get('sync_list')) {
			return $return;
		} 
		require DZC_PLUGDIR . '/modules/sync_button.php';
		return $return;
	} 
    /**
     * 论坛版块快速发表处：显示复选框“同步到微博”
     */
	function forumdisplay_fastpost_btn_extra_output() {
		global $_G;
		$return = '';
		if (!$_G['uid'] || !dzconnect_base::get('is_synctopic_toweibo') || !dzconnect_base::get('sync_list')) {
			return $return;
		} 
		require DZC_PLUGDIR . '/modules/sync_button.php';
		return $return;
	} 
} 

class plugin_dzconnect_home extends plugin_dzconnect {
	/**
	 * 家园－日志：显示复选框“同步到微博”
	 */
	function spacecp_blog_middle_output() {
		global $_G;
		$return = '';

		if (!$_G['uid'] || 'blog' != $_G['gp_ac'] || !dzconnect_base::get('is_syncblog_toweibo') || !dzconnect_base::get('sync_list')) {
			return $return;
		} 
		require DZC_PLUGDIR . '/modules/sync_button.php';
		return $return;
	} 
	/**
	 * 家园－日志：同步日志到微博
	 * 
	 * @param array $param DX传递的参数集
	 */
	function spacecp_blog_dzconnect_aftersubmit_message($param) {
		global $_G;

		if (!$_G['uid'] || !in_array($_G['gp_ac'], array('blog', 'comment')) || substr($param['param'][0], -8) != '_success' || !dzconnect_base::get('is_syncblog_toweibo')) {
			return;
		} elseif (getgpc('blogsubmit')) {
			$result = 1;
		} else {
			return;
		} 

		switch ($result) {
			case 1:
				require DZC_PLUGDIR . '/modules/blog.php';
				break;
			default:
				break;
		} 
	} 
	/**
	 * 家园－主页页面：显示复选框“同步到微博”
	 * 
	 * @return string 
	 */
	function space_home_top_output() {
		global $_G;
		$return = '';

		if (!$_G['uid'] || !dzconnect_base::get('is_syncdoing_toweibo') || !dzconnect_base::get('sync_list')) {
			return $return;
		} 
		$currentPage = 'home';
		require DZC_PLUGDIR . '/modules/sync_button.php';
		return $return;
	} 

	/**
	 * 家园－记录：显示复选框“同步到微博”
	 */
	function space_doing_bottom_output() {
		global $_G;
		$return = '';

		if (!$_G['uid'] || !dzconnect_base::get('is_syncdoing_toweibo') || !dzconnect_base::get('sync_list')) {
			return $return;
		} 
		$currentPage = 'doing';
		require DZC_PLUGDIR . '/modules/sync_button.php';
		return $return;
	} 
	/**
	 * 家园－记录：同步记录到微博
	 * 
	 * @param array $param DX2传递的参数集
	 */
	function spacecp_doing_aftersubmit_message($param) {
		global $_G;

		if (!$_G['uid']) {
			return;
		} elseif (getgpc('addsubmit') && isset($param['param'][2]['doid']) && $param['param'][2]['doid'] > 0 && dzconnect_base::get('is_syncdoing_toweibo')) { // 发主题贴
			$result = 1;
		} elseif (getgpc('commentsubmit') && dzconnect_base::get('is_syncreply_toweibo')) { // 发回复
			$result = 2;
		} else {
			return;
		} 

		switch ($result) {
			case 1:
				require DZC_PLUGDIR . '/modules/doing.php';
				break;
			case 2:
				require DZC_PLUGDIR . '/modules/doingcomment.php';
				break;
			default:
				break;
		} 
	} 
} 

class dzconnect_base {
	// 基本配置
	function get($k) {
		@include DZC_PLUGDIR . '/set.data.php';
		if ($_DZC_SET && is_array($_DZC_SET)) {
			return $_DZC_SET[$k];
		} 
	} 
	function L() {
		global $_G; 
		$charset = strtolower(str_replace("-", "", strtolower($_G['charset'])));
		if ($charset == 'utf8') {
			if ($_G['config']['output']['language'] == 'zh_tw') { // 繁体
				$charset .=  '_TC';
			} 
		} 
		// $_DZC_LANG = lang('plugin/dzconnect');
		require DZC_PLUGDIR . '/lang/' . $charset . '.php';
		return $_DZC_LANG;
	} 
	// 根据微博和openid/uid获取UID
	function getUid($name, $mid = '', $uid = '') {
		global $_G;
		if ($mid) {
			$uid = $mid;
			$openid = 'openid';
		} else {
			if (!$uid) $uid = $_G['uid'];
			if ($uid > 0) {
				$openid = 'uid';
			} else {
				return;
			} 
		}
		$query = DB :: fetch_first("SELECT uid FROM " . DB :: table('dzc_user_bind') . " WHERE name = '$name' AND $openid = '$uid'");
		return $query['uid'];
	} 
} 

?>