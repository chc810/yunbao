<?php

$_config = array();

$_config['default_site_title'] = '鹿鹿收货';

$_config['token'] = 'TestWeixin123';

$_config['weixin']['appid'] = '123456';
$_config['weixin']['appsecret'] = '122345';
// -- params: appid, redirect_uri, state
$_config['weixin']['snsapi_base'] = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_base&state=%s#wechat_redirect';
// -- params: appid, secret, code
$_config['weixin']['get_access_token_url'] = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code';

//$_config['base_url'] = 'http://ds100.16868.org:18080/huo158/weixin/';
$_config['base_url'] = 'http://ts1.16868.org:18080/huo158/weixin/';

$_config['GetCheckcode_url'] = $_config['base_url'] . 'GetCheckcode';
$_config['CheckUser_url'] = $_config['base_url'] . 'CheckUser';
$_config['AddBinding_url'] = $_config['base_url'] . 'AddBinding';
$_config['GetWayBillList_url'] = $_config['base_url'] . 'GetWaybillList';
