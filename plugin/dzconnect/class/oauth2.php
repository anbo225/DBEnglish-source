<?php
class class_http {
	function request($url, $params = array()) {
		if ($http = $params['http']) {
			return $this->$http($url, $params);
		} 
		if (!$this->close_curl()) {
			return $this->curl($url, $params);
		} elseif (!$this->close_socket()) {
			return $this->socket($url, $params);
		} elseif (!$this->close_fopen()) {
			return $this->streams($url, $params);
		} else {
			return "没有可以完成请求的 HTTP 传输器。";
		} 
	} 

	public function get($url, $timeout = 30) {
		if (!$this->close_curl()) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			$content = curl_exec($ch);
			curl_close($ch);
			return $content;
		} else {
			$params = array();
			if (@ini_get('allow_url_fopen')) {
				if (function_exists('file_get_contents')) {
					return file_get_contents($url);
				} 
				if (function_exists('fopen')) {
					$params['http'] = 'streams';
				} 
			} elseif (function_exists('fsockopen')) {
				$params['http'] = 'socket';
			} else {
				return "没有可以完成请求的 HTTP 传输器。";
			} 
			$params += array(
				"method" => 'GET',
				"timeout" => $timeout,
				"user-agent" => 'Connect WEIBO',
				"sslverify" => FALSE,
			);
			return $this->request($url, $params);
		} 
	} 

	function close_curl() {
		if (!extension_loaded('curl')) {
			return "请在php.ini中打开扩展extension=php_curl.dll";
		} else {
			$func_str = '';
			if (!function_exists('curl_init')) {
				$func_str .= "curl_init() ";
			} 
			if (!function_exists('curl_setopt')) {
				$func_str .= "curl_setopt() ";
			} 
			if (!function_exists('curl_exec')) {
				$func_str .= "curl_exec()";
			} 
			if ($func_str)
				return "不支持 $func_str 等函数，请在php.ini里面的disable_functions中删除这些函数的禁用！";
		} 
	} 

	function close_fopen() {
		if (!@ini_get('allow_url_fopen')) {
			return "不能使用fopen() fsockopen() file_get_contents()等函数。请在php.ini中设置allow_url_fopen = On";
		} else {
			if (!function_exists('fopen') && !function_exists('file_get_contents')) {
				return "不支持 fopen() 或者 file_get_contents() 函数，请在php.ini里面的disable_functions中删除这些函数的禁用！";
			} 
		} 
	} 

	function close_socket() {
		if (function_exists('fsockopen')) {
			$fp = 'fsockopen()';
		} elseif (function_exists('pfsockopen')) {
			$fp = 'pfsockopen()';
		} elseif (function_exists('stream_socket_client')) {
			$fp = 'stream_socket_client()';
		} 
		if (!$fp) {
			return "必须支持以下函数中的其中一个： fsockopen() 或者 pfsockopen() 或者 stream_socket_client() 函数，请在php.ini里面的disable_functions中删除这些函数的禁用！";
		} 
	} 

	function sfsockopen($host, $port, $errno, $errstr, $timeout) {
		if (function_exists('fsockopen')) {
			$fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
		} elseif (function_exists('pfsockopen')) {
			$fp = @pfsockopen($host, $port, $errno, $errstr, $timeout);
		} elseif (function_exists('stream_socket_client')) {
			$fp = @stream_socket_client($host . ':' . $port, $errno, $errstr, $timeout);
		} 
		return $fp;
	} 

	function curl($url, $params) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, $params['useragent']);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, $params['timeout']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, ($params['sslverify']) ? $params['sslverify'] : false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		switch ($params['method']) {
			case 'POST':
				curl_setopt($ch, CURLOPT_POST, true);
				if (!empty($params['body'])) {
					curl_setopt($ch, CURLOPT_POSTFIELDS, $params['body']);
				} 
				break;
			case 'DELETE':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if (!empty($params['body'])) {
					$url = $url . $params['body'];
				} 
		} 
		if (!empty($params['headers'])) {
			$headers = array();
			foreach ($params['headers'] as $k => $v) {
				$headers[] = "{$k}: $v";
			} 
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		} 
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		$response = curl_exec($ch);
		curl_close ($ch);
		return $response;
	} 

	function socket($url, $params = array()) {
		$matches = parse_url($url);
		$host = $matches['host']; 
		// Handle SSL connection request
		if ($matches['scheme'] == 'https') {
			$host = 'ssl://' . $host;
			$port = 443;
		} else {
			$port = 80;
		} 
		$ret = '';

		$fp = $this->sfsockopen($host, $port, $errno, $errstr, 15);
		if ($fp) {
			// Set http headers with host, user-agent and content type
			$header = $params['method'] . " " . $url . "  HTTP/1.1\r\n";
			$header .= "Host: " . $matches['host'] . "\r\n";

			if (!isset($params['headers']['Content-Type'])) {
				$params['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
			} 

			foreach ((array) $params['headers'] as $k => $v) {
				$header .= $k . ': ' . $v . "\r\n";
			} 

			if (strtolower($params['method']) == 'post') {
				$header .= "Content-Length: " . strlen($params['body']) . "\r\n";
				$header .= "Connection: close\r\n\r\n";
				$header .= $params['body'];
			} else {
				$header .= "Connection: close\r\n\r\n";
			} 

			fwrite($fp, $header);

			while (!feof($fp)) {
				$ret .= fgets($fp, 4096);
			} 
			fclose($fp);
			if (strrpos($ret, 'Transfer-Encoding: chunked')) {
				$info = explode("\r\n\r\n", $ret);
				$response = explode("\r\n", $info[1]);
				$t = array_slice($response, 1, -1);

				$result = implode('', $t);
			} else {
				$response = explode("\r\n\r\n", $ret);
				$result = $response[1];
			} 
			// 转成utf-8编码
			return iconv("utf-8", "utf-8//ignore", $result);
		} 
	} 

	function streams($url, $params = array()) {
		switch ($params['method']) {
			case 'GET':
				$opts = array('http' => array('method' => 'GET',
						'timeout' => $params['timeout'],
						'header' => 'Accept: application/xrds+xml, */*',
						'ignore_errors' => true,
						));
				$url = $url . ($params['body'] ? '?' . $params['body'] : '');
				break;
			case 'POST':
				if (!isset($params['headers']['Content-Type'])) {
					$params['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
				} 
				$headers = '';
				foreach ($params['headers'] as $k => $v) {
					$headers .= "{$k}: $v\r\n";
				} 
				$opts = array('http' => array('method' => 'POST',
						'timeout' => $params['timeout'],
						'header' => $headers,
						'content' => $params['body'],
						'ignore_errors' => true,
						));
				break;
		} 
		// if ($params['sslverify']) {
		// $opts += array('ssl' => array('verify_peer' => true,
		// 'capath' => $this->capath,
		// 'cafile' => $this->cainfo,
		// ));
		// }
		$context = stream_context_create($opts);

		if (function_exists('file_get_contents')) {
			return file_get_contents($url, false, $context);
		} 

		$handle = @fopen($url, 'r', false, $context);
		if (!$handle)
			return sprintf(('Could not open handle for fopen() to %s'), $url);

		stream_set_timeout($handle, 5);

		$contents = stream_get_contents($handle);
		$meta = stream_get_meta_data($handle);

		fclose($handle);

		return $contents;
	} 
} 

class classoauthv2 {
	public $client_id;
	public $client_secret;
	public $access_token;
	public $refresh_token;
	public $oauth_version;
	public $http_code; // Contains the last HTTP status code returned. 
	public $url; // Contains the last API call.
	public $host; // Set up the API root URL.
	public $root_domain; // root domain
	public $timeout = 30; // Set timeout default.
	public $connecttimeout = 30; // Set connect timeout.
	public $ssl_verifypeer = FALSE; // Verify SSL Cert.
	public $format; // Respons format.
	public $decode_json = TRUE; // Decode returned json data.
	public $http_info; // Contains the last HTTP headers returned.
	public $useragent = 'Connect WEIBO'; // Set the useragnet.
	public $debug = FALSE; // print the debug info
	public static $boundary = ''; // boundary of multipart

	/**
	 * construct OAuth object
	 */
	function __construct($client_id, $client_secret, $access_token = null, $refresh_token = null, $openid = null) {
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		$this->access_token = $access_token;
		$this->refresh_token = $refresh_token;
		$this->openid = $openid; // tencent
	}

	/**
	 * authorize接口
	 *
	 * @param string $url 授权后的回调地址,站外应用需与回调地址一致,站内应用需要填写canvas page的地址
	 * @param string $response_type 支持的值包括 code 和token 默认值为code
	 * @param string $state 用于保持请求和回调的状态。在回调时,会在Query Parameter中回传该参数
	 * @param string $display 授权页面类型 可选范围: 
	 *  - default		默认授权页面		
	 *  - mobile		支持html5的手机		
	 *  - popup			弹窗授权页		
	 *  - wap1.2		wap1.2页面		
	 *  - wap2.0		wap2.0页面		
	 *  - js			js-sdk 专用 授权页面是弹窗，返回结果为js-sdk回掉函数		
	 *  - apponweibo	站内应用专用,站内应用不传display参数,并且response_type为token时,默认使用改display.授权后不会返回access_token，只是输出js刷新站内应用父框架
	 * @return array
	 */

	function getAuthorizeURL( $authorize_url, $url, $response_type = 'code', $state = null, $display = null ) {
		$params = array();
		$params['client_id'] = $this->client_id;
		$params['redirect_uri'] = $url;
		$params['response_type'] = $response_type;
		$params['state'] = $state;
		$params['display'] = $display;
		return $authorize_url . "?" . self :: build_http_query($params);
	}

	/**
	 * access_token接口
	 *
	 * @param string 请求的类型,可以为:code, password, token
	 * @param array $keys 其他参数：
	 *  - 当为$keys['code']时： array('code'=>..., 'redirect_uri'=>...)
	 *  - 当为$keys['password']时： array('username'=>..., 'password'=>...)
	 *  - 当为$keys['token']时： array('refresh_token'=>...)
	 * @return array
	 */
	function getAccessToken( $keys ) {
		$params = array();
		$params['client_id'] = $this->client_id;
		$params['client_secret'] = $this->client_secret;
		if ( $keys['code'] ) {
			$params['grant_type'] = 'authorization_code';
			$params['code'] = $keys['code'];
			$params['redirect_uri'] = $keys['redirect_uri'];
        } elseif ( $keys['token'] ) {
			$params['grant_type'] = 'refresh_token';
			$params['refresh_token'] = $keys['refresh_token'];
		} elseif ( $keys['password'] ) {
			$params['grant_type'] = 'password';
			$params['username'] = $keys['username'];
			$params['password'] = $keys['password'];
		} else {
			return "wrong auth type";
		}

		$response = $this->oAuthRequest($keys['access_token_url'], 'POST', $params);
		if ( $keys['media'] == 'qq' ) { // qqweibo
			parse_str($response, $token);
			if ($token['errorCode']) {
				return array( 'error'=> $token['errorCode'], 'error_description'=> $token['errorMsg'] );
			} 
		} else {
			$token = json_decode($response, true);
		}

		if (is_array($token)) {
			if (!isset($token['error'])) {
				$this -> access_token = $token['access_token'];
				$this -> refresh_token = $token['refresh_token'];
			} 
		} else {
			$token = array('error' => $token ? $token : 'token error', 'other' => $keys['access_token_url']);
		} 
		return $token;
	}

	/**
	 * GET wrappwer for oAuthRequest.
	 *
	 * @return mixed
	 */
	function get($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'GET', $parameters);
		if ($this->decode_json) {
			return json_decode($response, true);
		}
		return $response;
	}

	/**
	 * POST wreapper for oAuthRequest.
	 *
	 * @return mixed
	 */
	function post($url, $parameters = array(), $multi = false) {
		$response = $this->oAuthRequest($url, 'POST', $parameters, $multi );
		if ($this->decode_json) {
			return json_decode($response, true);
		}
		return $response;
	}

	/**
	 * DELTE wrapper for oAuthReqeust.
	 *
	 * @return mixed
	 */
	function delete($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'DELETE', $parameters);
		if ($this->decode_json) {
			return json_decode($response, true);
		}
		return $response;
	}

	/**
	 * Get the header info to store.
	 *
	 * @return int
	 * @ignore
	 */
	function getHeader($ch, $header) {
		$i = strpos($header, ':');
		if (!empty($i)) {
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value = trim(substr($header, $i + 2));
			$this->http_header[$key] = $value;
		}
		return strlen($header);
	}

	/**
	 * @ignore
	 */
	public static function build_http_query_multi($params) {
		if (!$params) return ''; 
		uksort($params, 'strcmp');

		$pairs = array();

		self :: $boundary = $boundary = uniqid('------------------');
		$MPboundary = '--' . $boundary;
		$endMPboundary = $MPboundary . '--';
		$multipartbody = '';

		foreach ($params as $parameter => $value) {
			if (in_array($parameter, array("pic", "image", "picture"))) {
				if (is_array($value)) {
					$content = $value[2];
					$filename = $value[1];
					$mime = $value[0];
				} else {
					$url = ltrim($value , '@'); 
					// $content = file_get_contents($url);
					$content = self :: file_get_contents($url, 10);
					$filename = reset(explode('?' , basename($url)));
				}
				if ($content) {
					$multipartbody .= $MPboundary . "\r\n";
					$multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"' . "\r\n";
					$multipartbody .= "Content-Type: image/unknown\r\n\r\n";
					$multipartbody .= $content . "\r\n";
				} 
			} else {
				$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'content-disposition: form-data; name="' . $parameter . "\"\r\n\r\n";
				$multipartbody .= $value . "\r\n";
			} 
		} 
		$multipartbody .= "$endMPboundary\r\n";
		return $multipartbody;
	} 
	/**
	 * @ignore
	 */
	public static function build_http_query($params) {
		if (!$params) return '';

		uksort($params, 'strcmp');

		$pairs = array();
		foreach ($params as $parameter => $value) {
			if (is_array($value)) {
				natsort($value);
				foreach ($value as $duplicate_value) {
					$pairs[] = $parameter . '=' . $duplicate_value;
				} 
			} else {
				$pairs[] = $parameter . '=' . $value;
			} 
		} 
		return implode('&', $pairs);
	}
	/**
	 * Format and sign an OAuth / API request
	 *
	 * @return string
	 * @ignore
	 */
	function oAuthRequest($url, $method, $parameters, $multi = false) {
		if (strrpos($url, 'http://') !== 0 && strrpos($url, 'https://') !== 0) {
			$url = "{$this->host}{$url}";
		}

		if ($this->format === "json") {
			$url = $url . ".json";
		}

		if ($this->root_domain == "qq.com") { // qq
			$parameters['format'] = 'json';
			$parameters['oauth_consumer_key'] = $this->client_id;
			$parameters['openid'] = $this->openid;
			$parameters['oauth_version'] = $this->oauth_version;
		}

		$headers = array();

		if ( !empty($this->access_token) ) { // 2.0
			$parameters['access_token'] = $this->access_token;
		} 

		switch ($method) {
			case 'GET':
				$url = $url . '?' . self :: build_http_query($parameters);
				// return var_dump($url);
				return $this->http($url, 'GET');
			default:
				if (!$multi && (is_array($parameters) || is_object($parameters)) ) {
					$body = self :: build_http_query($parameters);
				} else {
					$body = self :: build_http_query_multi($parameters);
					$headers['Content-Type'] = "multipart/form-data; boundary=" . self :: $boundary;
				}
				return $this->http($url, $method, $body, $headers);
		}
	}

	/**
	 * Make an HTTP request
	 *
	 * @return string API results
	 * @ignore
	 */
	function http($url, $method, $postfields = NULL, $headers = array()) {
		$params = array(
			"method" => $method,
			"timeout" => $this->timeout,
			"user-agent" => $this->useragent,
			"sslverify" => $this->ssl_verifypeer,
			"body" => $postfields,
			"headers" => $headers
		);
		$http = new class_http();
		return $http->request($url, $params);
	}

	public function file_get_contents($url, $timeout = 30) {
		$http = new class_http();
		return $http->get($url, $timeout);
	} 
}

/**
 * 腾讯微博
 * 
 * @version 2.0
 */
class qqweiboAPP extends classOAuthV2 {
	public $oauth_version = '2.a';
	public $host = "https://open.t.qq.com/api/"; //Set up the API root URL.
	public $root_domain = "qq.com"; //root domain.  

	function get_ip() {
		if ($HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"]) {
			$ip = $HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"];
		} elseif ($HTTP_SERVER_VARS["HTTP_CLIENT_IP"]) {
			$ip = $HTTP_SERVER_VARS["HTTP_CLIENT_IP"];
		} elseif ($HTTP_SERVER_VARS["REMOTE_ADDR"]) {
			$ip = $HTTP_SERVER_VARS["REMOTE_ADDR"];
		} elseif (getenv("HTTP_X_FORWARDED_FOR")) {
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		} elseif (getenv("HTTP_CLIENT_IP")) {
			$ip = getenv("HTTP_CLIENT_IP");
		} elseif (getenv("REMOTE_ADDR")) {
			$ip = getenv("REMOTE_ADDR");
		} else {
			$ip = "Unknown";
		} 
		return $ip;
	} 
	// 获取其他人资料
	function show_user( $name = null ) {
		$params = array();
		if (!$name) {
			return $this->get( 'user/info', $params );
		} 
		$params['name'] = $name;
		return $this->get( 'user/other_info', $params );
	}
	// 其他用户发表时间线
	function user_timeline( $name = null, $page = 0, $count = 20, $type = 0, $lastid = 0, $pagetime = 0, $contenttype = 0 ) {
		$params = array();
		$params['reqnum'] = $count;
		$params['pageflag'] = $page;
		$params['lastid'] = $lastid;
		$params['pagetime'] = $pagetime;
		$params['type'] = $type;
		$params['contenttype'] = $contenttype;
		if (!$name) {
			return $this->get( 'statuses/broadcast_timeline', $params );
		}
		$params['name'] = $name;
		return $this->get( 'statuses/user_timeline', $params );
	}
	// 其他帐户听众列表
	function followers( $name = null, $count = 20 ) {
		$params = array();
		$params['format'] = 'json';
		$params['reqnum'] = $count;
		if (!$name) {
			return $this->get( 'friends/fanslist', $params );
		}
		$params['name'] = $name;
		return $this->get( 'friends/user_fanslist', $params );
	}
	// 收听某用户
	function friends_add( $name ) {
		$params = array();
		$params['name'] = $name;
		return $this->post('friends/add', $params);
	}
	// 发表微博(文本、图片、视频、音乐)
	function update($text, $value = '') {
		$params = array();
		$params['content'] = $text;
		$params['clientip'] = $this->get_ip();
		if ($value && is_array($value)) {
			// 兼容旧版本
			if (in_array($value[0], array('image', 'video', 'music'))) {
				if ($value[1]) {
					if ($value[0] == 'image') {
						$value = array($value[1], '', '');
					} elseif ($value[0] == 'video') {
						$value = array('', $value[1], '');
					} elseif ($value[0] == 'music') {
						$value = array('', '', $value[1]);
					} 
				} else {
					return $this->post('t/add', $params);
				} 
			} 
			if ($value[0] && !$value[1]) { // 图片
				$params['pic'] = $value[0];
				$params['content'] = rawurldecode($params['content']);
				return $this->post('t/add_pic', $params, true);
				// $params['pic_url'] = $value[0];
				// return $this->post('t/add_pic_url', $params);
			} elseif ($value[1] && !$value[0]) { // 视频
				$params['video_url'] = $value[1];
				return $this->post('t/add_video', $params);
			} else { // 图片、视频、音乐
				$params['pic_url'] = $value[0];
				$params['video_url'] = $value[1];
				if (is_array($value[2])) {
					$params['music_url'] = $value[2][2];
					$params['music_title'] = $value[2][1];
					$params['music_author'] = $value[2][0];
				} 
				return $this->post('t/add_multi', $params);
			} 
		} 
		return $this->post('t/add', $params);
	} 
	// 对一条微博信息进行评论
	function comment($sid, $text) {
		$params = array();
		$params['content'] = $text;
		$params['reid'] = $sid;
		$params['clientip'] = $this->get_ip();
		return $this->post('t/comment', $params);
	} 
	// 转播一条微博
	function repost($sid, $text) {
		$params = array();
		$params['content'] = $text;
		$params['reid'] = $sid;
		$params['clientip'] = $this->get_ip();
		return $this->post('t/re_add', $params);
	} 
	// 根据微博ID返回某条微博的评论列表
	function get_comments($rootid, $page = 0, $count = 20, $flag = 1, $twitterid = 0, $pagetime = 0) {
		$params = array();
		$params['flag'] = $flag; // 0－转播列表 1－点评列表 2－点评与转播列表
		$params['rootid'] = $rootid; // 转发或回复的微博根结点id（源微博id）
		$params['pageflag'] = $page;
		$params['pagetime'] = $pagetime;
		$params['reqnum'] = $count;
		$params['twitterid'] = $twitterid;
		return $this->get('t/re_list', $params);
	} 
	// 获取自己信息
	function verify_credentials() {
		return $this->get('user/info');
	} 
	// 根据微博ID批量获取微博内容
	function get_list($ids) {
		$params = array();
		$params['ids'] = $ids;
		return $this->get('t/list', $params);
	} 
} 

/** 
 * 新浪微博操作类 
 * 
 * @version 2.0
 */ 
class sinaweiboAPP extends classoauthv2
{ 
	public $host = "https://api.weibo.com/2/"; //Set up the API root URL.
	public $format = 'json'; // Respons format.

	/**
	 * 根据用户UID或昵称获取用户资料
	 *
	 * 按用户UID或昵称返回用户资料，同时也将返回用户的最新发布的微博。
	 * <br />对应API：{@link http://open.weibo.com/wiki/2/users/show users/show}
	 * 
	 * @access public
	 * @param int  $uid 用户UID。
	 * @return array
	 */
	function show_user( $uid )
	{
		$params = array();
		$params['uid'] = $uid;
		return $this->get( 'users/show', $params );
	}

	/**
	 * 获取用户发布的微博信息列表
	 *
	 * 返回用户的发布的最近n条信息，和用户微博页面返回内容是一致的。此接口也可以请求其他用户的最新发表微博。
	 * <br />对应API：{@link http://open.weibo.com/wiki/2/statuses/user_timeline statuses/user_timeline}
	 * 
	 * @access public
	 * @param int $page 页码
	 * @param int $count 每次返回的最大记录数，最多返回200条，默认50。
	 * @param mixed $uid 指定用户UID或微博昵称
	 * @param int $since_id 若指定此参数，则只返回ID比since_id大的微博消息（即比since_id发表时间晚的微博消息）。可选。
	 * @param int $max_id 若指定此参数，则返回ID小于或等于max_id的提到当前登录用户微博消息。可选。
	 * @param int $base_app 是否基于当前应用来获取数据。1为限制本应用微博，0为不做限制。默认为0。
	 * @param int $feature 过滤类型ID，0：全部、1：原创、2：图片、3：视频、4：音乐，默认为0。
	 * @param int $trim_user 返回值中user信息开关，0：返回完整的user信息、1：user字段仅返回uid，默认为0。
	 * @return array
	 */
	function user_timeline( $uid = '', $page = 1, $count = 50, $feature = 0, $since_id = 0, $max_id = 0, $trim_user = 0, $base_app = 0 )
	{
		$params = array();
		if ($uid) {
			$params['uid'] = $uid;
		}
		if ($since_id) {
			$params['since_id'] = $since_id;
		}
		if ($max_id) {
			$params['max_id'] = $max_id;
		}
		$params['base_app'] = intval($base_app);
		$params['feature'] = intval($feature);
		$params['count'] = intval($count);
		$params['page'] = intval($page);
		$params['trim_user'] = intval($trim_user);
		return $this->get( 'statuses/user_timeline', $params );
	}

	/**
	 * 批量获取指定的一批用户的微博列表 (高级接口)
	 *
	 * <br />对应API：{@link http://open.weibo.com/wiki/2/statuses/timeline_batch statuses/timeline_batch}
	 * 
	 * @param int $page 页码
	 * @param int $count 单页返回的记录条数，默认为20
	 * @param string $uids 需要查询的用户ID，用半角逗号分隔，一次最多20个。
	 * @param int $base_app 是否基于当前应用来获取数据。1为限制本应用微博，0为不做限制。默认为0。
	 * @param int $feature 过滤类型ID，0：全部、1：原创、2：图片、3：视频、4：音乐，默认为0。
	 * @return array
	 */
	function user_timeline_batch( $uids = NULL, $page = 1, $count = 50, $feature = 0, $base_app = 0 )
	{
		$params = array();
		$params['uids'] = $uids;
		$params['count'] = intval($count);
		$params['page'] = intval($page);
		$params['base_app'] = intval($base_app);
		$params['feature'] = intval($feature);
		return $this->get( 'statuses/timeline_batch', $params );
	}

	/**
	 * 获取用户的粉丝列表
	 *
	 * 对应API：{@link http://open.weibo.com/wiki/2/friendships/followers friendships/followers}
	 *
	 * @param int $uid  需要查询的用户UID
	 * @param int $count 单页返回的记录条数，默认为50，最大不超过200。
	 * @param int $cursor false 返回结果的游标，下一页用返回值里的next_cursor，上一页用previous_cursor，默认为0。
	 * @return array
	 **/
	function followers( $uid , $cursor = 0 , $count = 50 )
	{
		$params = array();
		$params['uid'] = $uid;
		$params['count'] = $count;
		$params['cursor'] = $cursor;
		return $this->get( 'friendships/followers', $params );
	}

	/**
	 * 获取优质粉丝
	 *
	 * 对应API：{@link http://open.weibo.com/wiki/2/friendships/followers/active friendships/followers/active}
	 *
	 * @param int $uid 需要查询的用户UID。
	 * @param int $count 返回的记录条数，默认为20，最大不超过200。
     * @return array
	 **/
	function followers_active( $uid, $count = 20 )
	{
		$params = array();
		$params['uid'] = $uid;
		$params['count'] = $count;
		return $this->get( 'friendships/followers/active', $params );
	}

    // 发布一条微博信息(文本、图片)
    function update( $text, $value = '' )
    {  
        $params = array(); 
        $params['status'] = $text;
		if ($value[0] == "image" && $value[1]) {
			$params['pic'] = '@'.$value[1];
			return $this->post( 'statuses/upload', $params, true );
			// $params['url'] = $value[1];
			// return $this->post( 'statuses/upload_url_text', $params ); // 高级接口
		} else {
            return $this->post( 'statuses/update', $params ); 
		}
    } 

	/**
	 * 对一条微博进行评论
	 *
	 * 对应API：{@link http://open.weibo.com/wiki/2/comments/create comments/create}
	 *
	 * @param string $comment 评论内容，内容不超过140个汉字。
	 * @param int $id 需要评论的微博ID。
	 * @param int $comment_ori 当评论转发微博时，是否评论给原微博，0：否、1：是，默认为0。
	 * @return array
	 */
	function comment( $id, $comment, $comment_ori = 1 )
	{
		$params = array();
		$params['comment'] = $comment;
		$params['id'] = $id;
		$params['comment_ori'] = $comment_ori;
		return $this->post( 'comments/create', $params );
	}

	/**
	 * 回复一条评论/评论一条评论
	 *
	 * 对应API：{@link http://open.weibo.com/wiki/2/comments/reply comments/reply}
	 * 对应API：{@link http://open.weibo.com/wiki/2/comments/create comments/create}
	 *
	 * @param string $comment 评论内容，内容不超过140个汉字。
	 * @param int $id 需要评论的微博ID。
	 * @param int $cid 需要回复的评论ID。  
	 * @param int $without_mention 回复中是否自动加入“回复@用户名”，0：是、1：否，默认为0。
	 * @param int $comment_ori 当评论转发微博时，是否评论给原微博，0：否、1：是，默认为0。
	 * @return array
	 */
	function reply( $id, $cid, $comment, $comment_ori = 1, $without_mention = 0)
	{
		$params = array();
		$params['comment'] = $comment;
		$params['id'] = $id;
		$params['comment_ori'] = $comment_ori;
		if ($cid && $id != $cid) {
			$params['cid'] = $cid;
			$params['without_mention'] = $without_mention;
			return $this->post( 'comments/reply', $params );
		}
		return $this->post( 'comments/create', $params );
	}
	/**
	 * 根据微博ID返回某条微博的评论列表
	 *
	 * 对应API：{@link http://open.weibo.com/wiki/2/comments/show comments/show}
	 *
	 * @param int $sid 需要查询的微博ID。
	 * @param int $page 返回结果的页码，默认为1。
	 * @param int $count 单页返回的记录条数，默认为50。
	 * @param int $since_id 若指定此参数，则返回ID比since_id大的评论（即比since_id时间晚的评论），默认为0。
	 * @param int $max_id  若指定此参数，则返回ID小于或等于max_id的评论，默认为0。
	 * @param int $filter_by_author 作者筛选类型，0：全部、1：我关注的人、2：陌生人，默认为0。
	 * @return array
	 */
	function get_comments( $sid, $page = 1, $count = 20, $since_id = 0, $max_id = 0, $filter_by_author = 0 )
	{
		$params = array();
		$params['id'] = $sid;
		if ($since_id) {
			$params['since_id'] = $since_id;
		}
		if ($max_id) {
			$params['max_id'] = $max_id;
		}
		$params['count'] = $count;
		$params['page'] = $page;
		$params['filter_by_author'] = $filter_by_author;
		return $this->get( 'comments/show',  $params );
	}

	/**
	 * 根据评论ID批量返回评论信息
	 *
	 * 对应API：{@link http://open.weibo.com/wiki/2/comments/show_batch comments/show_batch}
	 *
	 * @param string $cids 需要查询的批量评论ID，用半角逗号分隔，最大50
	 * @return array
	 */
	function comments_show_batch( $cids )
	{
		$params = array();
		if (is_array($cids) && !empty($cids)) {
			$params['cids'] = implode(',', $cids);
		} else {
			$params['cids'] = $cids;
		}
		return $this->get( 'comments/show_batch', $params );
	}

	/**
	 * 转发一条微博信息。
	 *
	 * 可加评论。为防止重复，发布的信息与最新信息一样话，将会被忽略。
	 * <br />对应API：{@link http://open.weibo.com/wiki/2/statuses/repost statuses/repost}
	 * 
	 * @access public
	 * @param int $sid 转发的微博ID
	 * @param string $text 添加的评论信息。可选。
	 * @param int $is_comment 是否在转发的同时发表评论，0：否、1：评论给当前微博、2：评论给原微博、3：都评论，默认为0。
	 * @return array
	 */
    function repost( $sid , $text = NULL, $is_comment = 3 ) 
    { 
        $params = array();
        $params['id'] = $sid;
        if( $text ) $params['status'] = $text;
        $params['is_comment'] = $is_comment;
        return $this->post( 'statuses/repost', $params );
    }

	/**
	 * 批量获取短链接的富内容信息 (高级接口)
	 *
	 * <br />对应API：{@link http://open.weibo.com/wiki/2/short_url/info short_url/info}
	 * 
	 * @param string url_short 需要获取富内容信息的短链接，需要URLencoded，最多不超过20个。多个url参数需要使用如下方式：url_short=aaa&url_short=bbb 
	 * @return array
	 */
    function short_url_info( $url_short ) 
    { 
        $params = array();
        $params['url_short'] = $url_short;
        return $this->get( 'short_url/info', $params );
    }

	/**
	 * 搜索某一话题下的微博。 (高级接口)
	 *
	 * <br />对应API：{@link http://open.weibo.com/wiki/2/search/topics search/topics}
	 * 
	 * @access public
	 * @param string $q 搜索的话题关键字，必须进行URLencode，utf-8编码。
	 * @param int $count 单页返回的记录条数，默认为10，最大为50。
	 * @param int $page 返回结果的页码，默认为1。
	 * @return array
	 */
    function search_ht( $q, $count = 10, $page = 1 ) 
    { 
        $params = array();
        $params['q'] = $q;
        $params['count'] = $count;
        $params['page'] = $page;
        return $this->get( 'search/topics', $params );
    }
} 