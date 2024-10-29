<?php
class wechatreplay_share{
    
    public function init(){
        add_action( 'wp_enqueue_scripts', [$this,'WechatReplay_enqueue_index'] );
        add_action( 'wp_footer', [$this,'wechatreplay_shares'] ,100);
        
    }
    public function WechatReplay_enqueue_index(){
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        wp_enqueue_script( 'jweixin-1.6.0.js',  $http_type.'res.wx.qq.com/open/js/jweixin-1.6.0.js',false,'','all');
        wp_enqueue_script("jquery");
    }
    public function wechatreplay_shares(){
        global $WechatReplay_log;
        if(!$WechatReplay_log){
            
            return;
        }
        $pay = wechatreplay_paymoney('/api/index/pay_money');
        
        if(!isset($pay['status']) || $pay['status']!=1){
            return;
        }
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    
        $url = esc_url_raw($http_type.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        $wechat_replay = get_option('wechat_replay_share');
        $wechat_replay1 = get_option('wechat_replay');
        if(isset($wechat_replay1['appid']) && $wechat_replay1['appid']){
            $wechat_replay['appid'] = $wechat_replay1['appid'];
        }
        if(isset($wechat_replay1['secret']) && $wechat_replay1['secret']){
            $wechat_replay['secret'] = $wechat_replay1['secret'];
        }
        if(!isset($wechat_replay['appid'])){
            return;
        }
        $jssdk = new wechat_JSSDK($wechat_replay['appid'],$wechat_replay['secret']);
        
        $signPackage = $jssdk->GetSignPackage();
        
        $title = wechatreplay_get_title();
        
        $desc = wechatreplay_get_desc();
         
        $icon = wechatreplay_get_icon();
         
        ?>
        <script>
            wx.config({
                debug: false,
                appId: '<?php echo esc_attr($wechat_replay['appid']);?>', 
                timestamp: <?php echo (int)$signPackage["timestamp"];?>, 
                nonceStr: '<?php echo esc_attr($signPackage["nonceStr"]);?>', 
                signature: '<?php echo esc_attr($signPackage["signature"]);?>',
                jsApiList: ['updateAppMessageShareData','updateTimelineShareData','onMenuShareAppMessage','onMenuShareTimeline'],
            });
            wx.ready(function () {   
             
              wx.updateAppMessageShareData({
                title: '<?php echo esc_attr($title);?>', 
                desc: '<?php echo esc_attr($desc);?>', 
                link: '<?php echo esc_url($url);?>', 
                imgUrl: '<?php echo esc_url($icon);?>', 
                success: function (res) {
                  console.log(res)
                }
              })
            }); 
            
    </script>
        <?php
    }
}


function wechatreplay_get_title(){
    $blog_name = get_bloginfo('name');
    $wechat_replay = get_option('wechat_replay_share');
    if(is_home() || is_front_page()){
        return isset($wechat_replay['title'])&&$wechat_replay['title']?$wechat_replay['title']:$blog_name;
    }elseif(is_category()){
        $cat_id = get_query_var('cat');
        return get_cat_name( $cat_id );
        
    }else if(is_tag()){
    	return single_tag_title( '', false );
    }elseif(is_single()){
        global $post;
        return get_the_title( $post->ID )?get_the_title( $post->ID ):$wechat_replay['title'];
    }else{
        return isset($wechat_replay['title'])&&$wechat_replay['title']?$wechat_replay['title']:$blog_name;
    }
}
function wechatreplay_get_desc(){
    $wechat_replay = get_option('wechat_replay_share');
    if(is_home() || is_front_page()){
        return isset($wechat_replay['description'])?$wechat_replay['description']:'';
    }elseif(is_category()){
        return isset($wechat_replay['description'])?$wechat_replay['description']:'';
    }else if(is_tag()){
    	return isset($wechat_replay['description'])?$wechat_replay['description']:'';
    }elseif(is_single()){
        global $post;
        if($post->post_excerpt){
            return $post->post_excerpt;
        }else{
        return str_replace("\n",'',trim(mb_strimwidth(wp_strip_all_tags(get_the_content()),0,40,'')));
        }
    }else{
        return isset($wechat_replay['description'])?$wechat_replay['description']:'';
    }
}
function wechatreplay_get_icon(){
    $wechat_replay = get_option('wechat_replay_share');
    if(is_home() || is_front_page()){
        
        return isset($wechat_replay['icon'])?$wechat_replay['icon']:'';
    }elseif(is_category()){
        
        return isset($wechat_replay['icon'])?$wechat_replay['icon']:'';
        
    }else if(is_tag()){
        
    	return isset($wechat_replay['icon'])?$wechat_replay['icon']:'';
    }elseif(is_single()){
        global $post;
        $atten = get_post_thumbnail_id($post->ID);
        $atten = wp_get_attachment_image_src($atten);
        return isset($atten[0])?$atten[0]:$wechat_replay['icon'];
    }else{
        
        return isset($wechat_replay['icon'])?$wechat_replay['icon']:'';
    }
}

function wechatreplay_paymoney1($root){
	$data =  sanitize_text_field($_SERVER['SERVER_NAME']);
	$url = WECHATREPLAY_URL.$root."?url={$data}&type=2&url1=".md5($data.WECHATREPLAY_SALT);
	$defaults = array(
        'timeout' => 120,
        'connecttimeout'=>120,
        'redirection' => 3,
        'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',
        'sslverify' => FALSE,
    );
	$result = wp_remote_get($url,$defaults);
    $content = wp_remote_retrieve_body($result);
	$content = json_decode($content,true);
	if(isset($content['status']) && $content['status']==1){
		return $content;
	}else{
	    return wechatreplay_paymoneys();
	}
}
