<?php


define('ROOT', dirname(__FILE__));

require ROOT . '/config.php';	// 配置

// 全局变量
$_G = array();

// 快递公司信息
$_G['name'] = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
$_G['id'] 	= isset($_REQUEST['id']) ? $_REQUEST['id'] : '';



// 判断 openid 是否 存在
if (!isset($_COOKIE['openid']) || empty($_COOKIE['openid'])) {
	// 获取openid
	if (isset($_GET['state']) && isset($_GET['code']) && 'GET_Code_state' == $_GET['state']) {	// -- weixin callback

		$url = sprintf($_config['weixin']['get_access_token_url'], $_config['weixin']['appid'], $_config['weixin']['appsecret'], $_GET['code']);
		$json = http_get($url);
		$result = json_decode($json, true);
		
		if (!isset($result['errcode']) && isset($result['openid'])) {
			$openid = $result['openid'];
		} else {
			// -- 获取 openid 失败, 重新获取
			header('Location: ' . createurl('list.php'));
			exit;
		}

	} else {
		$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$url = sprintf($_config['weixin']['snsapi_base'], $_config['weixin']['appid'], urlencode($redirect_uri), 'GET_Code_state');
		header('Location: ' . $url);
		exit;
	}
} else {
	$openid = $_COOKIE['openid'];
}


// 设置 “永久”
setcookie('openid', $openid, time() + 31536000);	// -- 5 年

$_G['openid'] = $openid;



if (!defined('IN_USER')) {

	// 判断是否绑定了手机
	$res = checkuser($openid);

	if ($res !== true) {
		if ($res['status'] == '403') {	// 未绑定
			header('Location: ' . createurl('user.php'));
			exit;
		} else if ($res['status'] == '400') {
			alertmsg('系统错误');
		} else {
			alertmsg('系统内部错误');
		}
	}
}


function template($name, $ext = 'html') {
	$tpl = ROOT . '/template/' . $name . '.' . $ext;
	if (is_file($tpl)) {
		return $tpl;
	}
}

function getsitetitle() {
	global $_config, $_G;
	if (!empty($_G['name'])) {
		return $_G['name'];
	}
	return $_config['default_site_title'];
}

/**
 * 用于 帮助url带上id和name
 */
function createurl($url) {
	global $_G;
	$url = $url . (empty($_G['id']) ? '' : (strpos($url, '?') === false ? '?' : '&') . ($_G['id'] ? 'id=' . $_G['id'] : ''));
	$url = $url . (empty($_G['name']) ? '' : (strpos($url, '?') === false ? '?' : '&') . ($_G['name'] ? 'name=' . $_G['name'] : ''));
	return $url;
}

/**
 * 用于 帮助 api url 带上 id 
 */
function createapiurl($url) {
	global $_G;
	$url = $url . (empty($_G['id']) ? '' : (strpos($url, '?') === false ? '?' : '&') . ($_G['id'] ? 'id=' . $_G['id'] : ''));
	return $url;
}

function checkuser($openid) {
	global $_config;
	$url = $_config['CheckUser_url'] . '?token=' . $_config['token'] . '&openid=' . $openid;
	$result = http_get($url);
	$json = json_decode($result, true);
	if ($json['status'] == '200') {
		return true;
	}
	return $json;
}


function http_post($url,$param){
	$oCurl = curl_init();
	if(stripos($url,"https://")!==FALSE){
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
	}
	if (is_string($param)) {
		$strPOST = $param;
	} else {
		$aPOST = array();
		foreach($param as $key=>$val){
			$aPOST[] = $key."=".urlencode($val);
		}
		$strPOST =  join("&", $aPOST);
	}
	curl_setopt($oCurl, CURLOPT_URL, $url);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt($oCurl, CURLOPT_POST,true);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
	$sContent = curl_exec($oCurl);
	$aStatus = curl_getinfo($oCurl);
	curl_close($oCurl);
	if(intval($aStatus["http_code"])==200){
		return $sContent;
	}else{
		return false;
	}
}

function http_get($url){
	$oCurl = curl_init();
	if(stripos($url,"https://")!==FALSE){
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
	}
	curl_setopt($oCurl, CURLOPT_URL, $url);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
	$sContent = curl_exec($oCurl);
	$aStatus = curl_getinfo($oCurl);
	curl_close($oCurl);
	if(intval($aStatus["http_code"])==200){
		return $sContent;
	}else{
		return false;
	}
}

function alertmsg($message, $goback = false, $exit = true) {
	echo '<meta charset="utf-8" />';
	echo '<link href="css/sweet-alert.css" media="all" rel="stylesheet" type="text/css" />';
	echo '<script type="text/javascript" src="script/sweet-alert.min.js"></script>';
	echo '<script type="text/javascript">';
	echo 'window.onload = function () {swal({   title: "' . $message . '",   confirmButtonColor: "#DD6B55",   confirmButtonText: "确定",   closeOnConfirm: false }, function(){  ' . ($goback ? 'history.back();' : '') . ' });};';
	//if ($goback) {
	//	echo 'history.go(-1);';
	//}
	echo '</script>';
	if ($exit) exit;
}
