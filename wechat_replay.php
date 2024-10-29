<?php 
/*
Plugin Name: 微信公众号关键词回复
Description: 可适用于微信公众号（订阅号、服务号等），绑定以后用户在公众号发生关键词，公众号即可根据关键词获取网站相关内容推送URL回复客户。

Version: 1.0.1
Author: 沃之涛科技
Author URI: https://www.rbzzz.com
*/
// 声明全局变量$wpdb 和 数据表名常量
global $wpdb,$WechatReplay_log;
$WechatReplay_log = get_option('WechatReplay_log');
define('WECHATREPLAY_URL','http://wp.seohnzz.com');
define('WECHATREPLAY_SALT','seohnzz.com');
define('WECHATREPLAY_FILE',__FILE__);
define('WECHATREPLAY_VERSION','1.0.1');
require plugin_dir_path( __FILE__ ) . '/inc/index.php';
require plugin_dir_path( __FILE__ ) . '/inc/jssdk.php';
require plugin_dir_path( __FILE__ ) . 'post.php';//公用函数
require plugin_dir_path( __FILE__ ) . '/inc/wx_yzm.php';
require plugin_dir_path( __FILE__ ) . '/inc/wx_share.php';
require plugin_dir_path( __FILE__ ) . '/inc/wx_login.php';

$wechatreplay = new wechatreplay();
$wechatreplay->init();
$wechatreplay_yzm = new wechatreplay_yzm();
$wechatreplay_yzm->init();
$wechatreplay_share = new wechatreplay_share();
$wechatreplay_share->init();





    



?>