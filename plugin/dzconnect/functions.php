<?php
/**
 * 通用函数
 */
if (!function_exists('mb_substr')) {
	function mb_substr($str, $start = 0, $length = 0, $encode = 'utf-8') {
		$encode_len = ($encode == 'utf-8') ? 3 : 2;
		for($byteStart = $i = 0; $i < $start; ++$i) {
			$byteStart += ord($str{$byteStart}) < 128 ? 1 : $encode_len;
			if ($str{$byteStart} == '') return '';
		} 
		for($i = 0, $byteLen = $byteStart; $i < $length; ++$i)
		$byteLen += ord($str{$byteLen}) < 128 ? 1 : $encode_len;
		return substr($str, $byteStart, $byteLen - $byteStart);
	} 
} 
if (!function_exists('mb_strlen')) {
	function mb_strlen($str, $encode = 'utf-8') {
		return ($encode == 'utf-8') ? strlen(utf8_decode($str)) : strlen($str);
	} 
} 
if (!function_exists('mb_strimwidth')) {
	function mb_strimwidth($str, $start, $width, $trimmarker, $encode = 'utf-8') {
		return mb_substr($str, $start, $width, $encode) . $trimmarker;
	} 
} 
// 使用键名比较计算数组的交集 array_intersect_key  < 5.1.0
if (!function_exists('array_intersect_key')) {
	function array_intersect_key($isec, $keys) {
		$argc = func_num_args();
		if ($argc > 2) {
			for ($i = 1; !empty($isec) && $i < $argc; $i++) {
				$arr = func_get_arg($i);
				foreach (array_keys($isec) as $key) {
					if (!isset($arr[$key])) {
						unset($isec[$key]);
					} 
				} 
			} 
			return $isec;
		} else {
			$res = array();
			foreach (array_keys($isec) as $key) {
				if (isset($keys[$key])) {
					$res[$key] = $isec[$key];
				} 
			} 
			return $res;
		} 
	} 
} 
// 使用指定的键和值填充数组 < 5.2.0
if (!function_exists('array_fill_keys')) {
	function array_fill_keys($keys, $value) {
		foreach($keys as $v) {
			$ret[$v] = $value;
		} 
		return $ret;
	} 
} 
// 字符长度(一个汉字代表一个字符，两个字母代表一个字符)
if (!function_exists('wp_strlen')) {
	function wp_strlen($text) {
		$a = mb_strlen($text, 'utf-8');
		$b = strlen($text);
		$c = $b / 3 ;
		$d = ($a + $b) / 4;
		if ($a == $b) { // 纯英文、符号、数字
			return $b / 2;
		} elseif ($a == $c) { // 纯中文
			return $a;
		} elseif ($a != $c) { // 混合
			return $d;
		} 
	} 
} 
// 含有中文的url使用urlencode
function url_utf8($url) {
	if (strlen($url) == mb_strlen(urldecode($url), 'utf-8')) {
		return $url;
	} else {
		return urlencode($url);
	}
} 
// 截取字数
if (!function_exists('wp_status')) {
	function wp_status($content, $url, $length, $num = '') {
		if ($num) {
			$temp_length = (wp_strlen($content)) + (wp_strlen($url));
		} else {
			$temp_length = (mb_strlen($content, 'utf-8')) + (mb_strlen($url, 'utf-8'));
		}
		if ($url) {
			$length = $length - 4; // ' - '
			$url = ' ' . $url;
		} 
		if ($temp_length > $length) {
			$chars = $length - 3 - mb_strlen($url, 'utf-8'); // '...'
			if ($num) {
				$chars = $length - wp_strlen($url);
				$str = mb_substr($content, 0, $chars, 'utf-8');
				preg_match_all("/([\x{0000}-\x{00FF}]){1}/u", $str, $half_width); // 半角字符
				$chars = $chars + count($half_width[0]) / 2;
			} 
			$content = mb_substr($content, 0, $chars, 'utf-8');
			$content = $content . "...";
		} 
		$status = $content . $url;
		return trim($status);
	} 
} 

function dzc_check_value($v) {
	return $v ? (int)$v : 0;
} 

function dzc_check_array($v) {
	return (is_array($v) && ($v[0] || $v[1])) ? $v : '';
} 

function cw_writeToFile($dFile, $config, $variable) {
	$code = "<?php\n//" . date('Y-m-d H:i:s') . " Created\n%s=%s;\n?>";
	return file_put_contents($dFile, sprintf($code, $variable, var_export($config, 1))) ? true : false;
} 

if (!function_exists('filter_value')) {
	function filter_value($v) { // array_filter $callback
		if (is_array($v)) $v = $v[0];
		if ($v !== "") {
			return true;
		} 
		return false;
	} 
} 
// 使用键名比较计算数组的交集，再加上剩下的数组
function array_sort_key($a, $b) {
	return array_intersect_key($a, $b) + $a;
}
// 字符集转换。mb_convert_encoding和iconv函数必须有一
function convertEncoding($source, $in, $out) {
	$in = strtoupper($in);
	$out = strtoupper($out);
	if ($in == "UTF8") {
		$in = "UTF-8";
	} 
	if ($out == "UTF8") {
		$out = "UTF-8";
	} 
	if ($in == $out) {
		return $source;
	} 
	if (function_exists('iconv')) {
		return iconv($in, $out . "//IGNORE", $source);
	} elseif (function_exists('mb_convert_encoding')) {
		return mb_convert_encoding($source, $out, $in);
	} 
	return $source;
} 
// utf8转为其他编码,包括数组
function utf82iconv($source) {
	if (OS_DZ_CHARSET == 'UTF8') {
		return $source;
	} elseif (is_array($source)) { // 数组
		return eval('return ' . convertEncoding(var_export($source, true) . ';', "UTF-8", OS_DZ_CHARSET));
	} else {
		return convertEncoding($source, "UTF-8", OS_DZ_CHARSET);
	}
} 
// 其他编码转为utf8,包括数组
function iconv2utf8($source) {
	if (OS_DZ_CHARSET == 'UTF8') {
		return $source;
	} elseif (is_array($source)) {
		return eval('return ' . convertEncoding(var_export($source, true) . ';', OS_DZ_CHARSET, "UTF-8"));
	} else {
		return convertEncoding($source, OS_DZ_CHARSET, "UTF-8");
	}
} 
// GBK转为其他编码
function gbk2iconv($source) {
	return (OS_DZ_CHARSET == 'GBK') ? $source : convertEncoding($source, "GBK", OS_DZ_CHARSET);
} 
// UTF8转GBK
if (!function_exists('utf82gbk')) {
	function utf82gbk($source) {
		return convertEncoding($source, "UTF-8", "GBK");
	} 
} 
// 过滤html
if (!function_exists('wp_replace')) {
	function wp_replace($str) {
		$a = array('&#160;', '&#038;', '&#8211;', '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&#8230;', '&amp;', '&lt;', '&gt', '&ldquo;', '&rdquo;', '&nbsp;', 'Posted by Wordmobi');
		$b = array(' ', '&', '-', '‘', '’', '“', '”', '…', '&', '<', '>', '“', '”', ' ', '');
		$str = str_replace($a, $b, strip_tags($str));
		return trim($str);
	} 
} 
// 判断
if (!function_exists('ifabc')) {
	function ifab($a, $b) {
		return $a ? $a : $b;
	} 
	function ifb($a, $b) {
		return $a ? $b : '';
	} 
	function ifac($a, $b, $c) {
		return $a ? $a : ($b ? $c : '');
	} 
	function ifabc($a, $b, $c) {
		return $a ? $a : ($b ? $b : $c);
	} 
	function ifold($str, $old, $new) { // 以旧换新
		return (empty($str) || $str == $old) ? $new : $str;
	} 
} 
function class_http($url, $params) {
	class_exists('classOAuthV2') or require(dirname(__FILE__) . '/class/oauth2.php');
	$params['method'] = 'POST';
	$params['user-agent'] = 'Discuz!/' . OS_DZ_VERSION . '; ' . OS_DZ_URL;
	$http = new Class_Http();
	return $http->request($url, $params);
} 
function get_url_contents($url, $timeout = 10, $params = array()) {
	class_exists('classOAuthV2') or require(dirname(__FILE__) . '/class/oauth2.php');
	$params['timeout'] = $timeout;
	$params['user-agent'] = 'Discuz!/' . OS_DZ_VERSION . '; ' . OS_DZ_URL;
	$http = new Class_Http();
	return $http->get($url, $params);
} 
// SESSION
function set_c_session($key, $value) {
	$_SESSION['CONNECT_WEIBO_S'][$key] = $value;
} 
function get_c_session($key) {
	if (isset($_SESSION['CONNECT_WEIBO_S'][$key])) {
		return $_SESSION['CONNECT_WEIBO_S'][$key];
	} 
} 
function del_c_session($key) {
	if (isset($key)) {
		if (isset($_SESSION['CONNECT_WEIBO_S'][$key])) {
			unset($_SESSION['CONNECT_WEIBO_S'][$key]);
		} 
	} else {
		if (isset($_SESSION['CONNECT_WEIBO_S'])) {
			unset($_SESSION['CONNECT_WEIBO_S']);
		} 
	}
} 

/**
 * SNS相关函数
 */
// 默认key
function default_openkey() {
	$default_key = array('sina' => array('351762339', 'dac1c15f89eaad49f5d96fc09ddb672f', 2),
		'qq' => array('d05d3c9c3d3748b09f231ef6d991d3ac', 'e049e5a4c656a76206e55c91b96805e8', 1)
		);
	return $default_key;
} 
// 自定义key
function custom_openkey() {
	return default_openkey();
} 

/**
 * 分享按钮
 */
// 社会化分享按钮，共54个
function wp_social_share_title() {
	global $_DZC_SHARE_TITLE;
	return $_DZC_SHARE_TITLE;
}  

/**
 * SNS函数
 */
// 腾讯微博
function wp_update_t_qq($appkey, $token, $status, $value = "") {
	if (!$token['access_token']) return;
	$to = new qqweiboAPP($appkey[0], $appkey[1], $token['access_token'], '', $token['openid']);
	$result = $to -> update($status, $value);
	return $result['data']['id'];
} 
// 新浪微博
function wp_update_t_sina($appkey, $token, $status, $value = "") {
	$to = new sinaweiboAPP($appkey[0], $appkey[1], $token['access_token']);
	$result = $to -> update($status, $value);
	return $result;
} 
// 转播一条微博
function wp_repost_t_sina($appkey, $token, $sid, $text) {
	$to = new sinaweiboAPP($appkey[0], $appkey[1], $token['access_token']);
	$result = $to -> repost($sid, $text);
	return $result;
} 
function wp_repost_t_qq($appkey, $token, $sid, $text) {
	if (!$token['access_token']) return;
	$to = new qqweiboAPP($appkey[0], $appkey[1], $token['access_token'], '', $token['openid']);
	$result = $to -> repost($sid, $text);
	return $result;
} 
// 对一条微博信息进行评论
function wp_comment_t_sina($appkey, $token, $sid, $text) {
	$to = new sinaweiboAPP($appkey[0], $appkey[1], $token['access_token']);
	$result = $to -> comment($sid, $text);
	return $result;
} 
function wp_comment_t_qq($appkey, $token, $sid, $text) {
	if (!$token['access_token']) return;
	$to = new qqweiboAPP($appkey[0], $appkey[1], $token['access_token'], '', $token['openid']);
	$result = $to -> comment($sid, $text);
	return $result;
} 
// 评论微博
function batch_comment_weibo($account, $mid, $comment, $url = '') {
	require_once dirname(__FILE__) . '/class/oauth2.php';
	$openkeys = custom_openkey();
	$comment = wp_status($comment, $url, 140, 1);
	if ($account['sina']) { // 新浪微博 /140*
		wp_comment_t_sina($openkeys['sina'], $account['sina'], $mid['sina'], $comment);
	}
	if ($account['qq']) { // 腾讯微博 /140*
		wp_comment_t_qq($openkeys['qq'], $account['qq'], $mid['qq'], $comment);
	}
}
// 发微博 V3.2.2
function batch_post_weibo($account, $content, $value = '', $url = '', $title = '') {
	global $_G;
	@ini_set("max_execution_time", 120);
	$text = $title ? $title . ' | ' . $content : $content;
	if (is_array($value)) {
		if ($value[0]) {
			$purl = $value[0]; // 图片
			$picture = array('image', $purl);
		} 
		if ($value[1]) { // 视频
			$vurl = $value[1];
		} 
	} 
	if ($url) {
		// 处理完毕输出链接
		$url_utf8 = trim($vurl . ' ' . str_replace('&', '%26', $url));
		$postlink = trim($vurl . ' ' . $url); 
		// 截取字数
		if ($url_utf8 == $postlink) { // url不含中文
			$status2= wp_status($text, $postlink, 140, 1);
		} else {
			$status2 = wp_status($text, $url_utf8, 140, 1); //新浪/腾讯
		} 
	} else {
		$status2 = wp_status($text, $vurl, 140, 1);
	} 
	// 开始同步
	require_once dirname(__FILE__) . '/class/oauth2.php';
	$openkeys = custom_openkey();
	$output = array();
	if ($account['sina']) { // 新浪微博 /140*
		$ms = wp_update_t_sina($openkeys['sina'], $account['sina'], $status2, $picture);
		$output['sina'] = $ms['mid'];
	} 
	if ($account['qq']) { // 腾讯微博 /140*
		$output['qq'] = wp_update_t_qq($openkeys['qq'], $account['qq'], $status2, $value);
	} 
	return $output;
} 
