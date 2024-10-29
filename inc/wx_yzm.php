<?php
class wechatreplay_yzm{
    public function init(){
        add_filter( 'mce_external_plugins', [$this,'WechatReplay_add_plugin'] );
        add_filter( 'mce_buttons', [$this,'WechatReplay_register_button'] ); 
        add_action('wp_ajax_nopriv_gdk_pass_view', [$this,'WechatReplay_captcha_view']);
        add_action('wp_ajax_gdk_pass_view', [$this,'WechatReplay_captcha_view']);
        add_shortcode('WechatReplay', [$this,'WechatReplay_secret_view']);
        add_action( 'wp_head', [$this,'WechatReplay_mainpage'],1 );
        $this->wechatreplay_tongji();

    }
    public function WechatReplay_add_plugin($plugin_array){
        $plugin_array['yzm'] =   esc_url(plugins_url('../yzm.js',__FILE__));
        return $plugin_array;  
    }
    public function WechatReplay_register_button($buttons){
        array_push( $buttons,"yzm" );    //添加 一个myadvert按钮   
        return $buttons;
    }
    public static function WechatReplay_captcha(){
         global $WechatReplay_log;
        if(!$WechatReplay_log){
            echo '';exit;
        }
        $WechatReplay_captcha = get_option('WechatReplay_captcha');
        $wechat_replay_qrcode = get_option('wechat_replay_qrcode');
        if(isset($WechatReplay_captcha['time']) && $WechatReplay_captcha['time']>time()){
            return $WechatReplay_captcha['value'];
        }else{
            if($WechatReplay_captcha!==false){
                $WechatReplay_captcha['value'] = mt_rand(100000,999999);
                if(isset($wechat_replay_qrcode['time']) && $wechat_replay_qrcode['time']){
                    $WechatReplay_captcha['time'] = time()+60*$wechat_replay_qrcode['time'];
                }else{
                    $WechatReplay_captcha['time'] = time()+600;
                }
                $WechatReplay_captcha['num'] = 0;
                update_option('WechatReplay_captcha',$WechatReplay_captcha);
                return $WechatReplay_captcha['value'];
            }else{
                $WechatReplay_captcha = [];
                if(isset($wechat_replay_qrcode['time']) && $wechat_replay_qrcode['time']){
                    $WechatReplay_captcha['time'] = time()+60*$wechat_replay_qrcode['time'];
                }else{
                    $WechatReplay_captcha['time'] = time()+600;
                }
                $WechatReplay_captcha['value'] = mt_rand(100000,999999);
                $WechatReplay_captcha['num'] = 0;
                add_option('WechatReplay_captcha',$WechatReplay_captcha);
                return $WechatReplay_captcha['value'];
            }
        }
    }
    public function wechatreplay_tongji(){
        $wechatreplay_tongji = get_option('wechatreplay_tongji');
        if(!$wechatreplay_tongji || (isset($wechatreplay_tongji) && $wechatreplay_tongji['time']<time()) ){
            $wp_version =  get_bloginfo('version');
            $data =  wechatreplay_url();
        	$url = "http://wp.seohnzz.com/api/wechatreplay/index?url={$data}&type=2&url1=".md5($data.'seohnzz.com')."&theme_version=".WECHATREPLAY_VERSION."&php_version=".PHP_VERSION."&wp_version={$wp_version}";
        	$defaults = array(
                'timeout' => 120,
                'connecttimeout'=>120,
                'redirection' => 3,
                'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',
                'sslverify' => FALSE,
            );
            $result = wp_remote_get($url,$defaults);
            if($wechatreplay_tongji!==false){
                update_option('wechatreplay_tongji',['time'=>time()+7*3600*24]);
            }else{
                add_option('wechatreplay_tongji',['time'=>time()+7*3600*24]);
            }
        }
    }
    public static function WechatReplay_captcha_view(){
        $action = sanitize_text_field($_POST['action']);
    	$post_id = (int)$_POST['id'];
    	$pass = sanitize_text_field($_POST['pass']);
    	$wechat_replay = get_option('wechat_replay_qrcode');
        $pass_content = get_post_meta($post_id, 'WechatReplay_content')[0];
    	if(isset($wechat_replay['type']) && $wechat_replay['type']==1){
        	$wxcaptcha = $wechat_replay['yzm'];
        	if($pass==$wxcaptcha){
                exit($pass_content);
        	}else{
        	    exit('400');
        	}
    	}else{
    	    $WechatReplay_captcha = get_option('WechatReplay_captcha');
    	    
    	    if(isset($WechatReplay_captcha['time']) && $WechatReplay_captcha['time']>time()){
            	$wxcaptcha = $WechatReplay_captcha['value'];
            	
            	if($wechat_replay['yzm_num'] && isset($WechatReplay_captcha['num']) && ($WechatReplay_captcha['num']<$wechat_replay['yzm_num'])){
                	if(!isset( $action )  ||  !isset( $post_id )  ||  !isset( $pass )   ) exit('400');
                	if($pass == $wxcaptcha ) {
                	    if(isset($WechatReplay_captcha['time']) && $WechatReplay_captcha['time']>time()){
                	        $WechatReplay_captcha['num'] +=1; 
                	        
                 	        update_option('WechatReplay_captcha',$WechatReplay_captcha);
                    	    exit(wp_kses_post($pass_content));
                	    }else{
                	        exit('400');
                	    }
                	}else{
                		exit('400');
                	}
            	}else if(!$wechat_replay['yzm_num']){
            	    if(!isset( $action )  ||  !isset( $post_id )  ||  !isset( $pass )   ) exit('400');
                	if($pass == $wxcaptcha ) {
                	    if(isset($WechatReplay_captcha['time']) && $WechatReplay_captcha['time']>time()){
                	        $WechatReplay_captcha['num'] +=1; 
                 	        update_option('WechatReplay_captcha',$WechatReplay_captcha);
                    	    exit(wp_kses_post($pass_content));
                	    }else{
                	        exit('400');
                	    }
                	}else{
                		exit('400');
                	}
            	}else{
            	   $res = delete_option('WechatReplay_captcha');
            	    
            	    exit('401'); 
            	}
    	    }else{
    	        exit('401');
    	    }
    	}
    	
    }
    // 部分内容输入密码可见
    public function WechatReplay_secret_view($atts, $content = null) {
        $id = get_the_ID();
        if(isset($_COOKIE['WechatReplay_'.$id]) && $_COOKIE['WechatReplay_'.$id]){
            return $content;
        }else{
            $wechat_replay = get_option('wechat_replay_qrcode');
            if($wechat_replay['content']){
                
            }else{
                $wechat_replay['content'] = '此处有隐藏内容--请扫描下方二维码查看';
            }
            return '<style>
                    #wx_border_animate{
                        background: linear-gradient(90deg, #333 50%, transparent 0) repeat-x,
                            linear-gradient(90deg, #333 50%, transparent 0) repeat-x,
                            linear-gradient(0deg, #333 50%, transparent 0) repeat-y,
                            linear-gradient(0deg, #333 50%, transparent 0) repeat-y;
                            background-size: 10px 2px, 10px 2px, 2px 10px, 2px 10px;
                            background-position: 0 0, 0 100%, 0 0, 100% 0;
                        animation: borderAnimate 500ms infinite linear;
                        padding: 10px;
                        text-align: center;
                    }
                    @keyframes borderAnimate {
                        100% {
                            background-position: 10px 0, -10px 100%, 0 -10px, 100% 10px;
                        }
                    }
                    </style>
                    <div id="wx_border_animate">'.$wechat_replay['content'].'</div><div class="WechatReplay_add_qrcode" style="display:none"></div>';
            
        }
        
    }
    public function WechatReplay_mainpage(){
        add_action( 'the_content', [$this,'WechatReplay_addlink']);
    }
    public function WechatReplay_addlink($content){
        $pid = get_the_ID();
        if(strpos($content,'[WechatReplay]') !== false){
            $count = substr_count($content,'[WechatReplay]');
           
            if(isset($_COOKIE['WechatReplay_'.$pid]) && $_COOKIE['WechatReplay_'.$pid]){
                 return $content.'<script>
                    document.cookie = 	"WechatReplay_'.$pid.'=0";
                </script>';
            }else{
                $content1 = str_replace('[WechatReplay]','',$content);
                $content1 = str_replace('[/WechatReplay]','',$content1);
                $WechatReplay_content = get_option('WechatReplay_content');
                if($WechatReplay_content!==false){
                    update_post_meta($pid,'WechatReplay_content',$content1);
                }else{
                    add_post_meta($pid, 'WechatReplay_content', $content1, true);
                }
                $wechat_replay = get_option('wechat_replay_qrcode');
                if(isset($wechat_replay['color']) && $wechat_replay['color']){
                    $wechat_replay['description'] = str_replace('/_/','<span style="color:'.$wechat_replay['color'].'">',$wechat_replay['description']);
                    $wechat_replay['description'] = str_replace('/__/','</span>',$wechat_replay['description']);
                    $wechat_replay['description'] = str_replace('《《_','<b>',$wechat_replay['description']);
                    $wechat_replay['description'] = str_replace('_》》','</b>',$wechat_replay['description']);
                }else{
                    $wechat_replay['description'] = str_replace('/_/','',$wechat_replay['description']);
                    $wechat_replay['description'] = str_replace('/__/','',$wechat_replay['description']);
                    $wechat_replay['description'] = str_replace('《《_','<b>',$wechat_replay['description']);
                    $wechat_replay['description'] = str_replace('_》》','</b>',$wechat_replay['description']);
                }
                if(isset($wechat_replay['size']) && $wechat_replay['size']){
                    $wechat_replay['size'] = $wechat_replay['size'].'px';
                }else{
                    $wechat_replay['size'] ='16px';
                }
                 if($count>1){
                        return '<div class="WechatReplay_yzm_content">           
                                '.wp_kses_post($content).'</div><style>
                                .cm-grid {
                                    padding-right: 15px;
                                    padding-left: 15px;
                                    margin-right: auto;
                                    margin-left: auto;
                                    box-sizing: border-box;
                                    overflow: hidden;
                                    margin-bottom: 15px;
                                    border-radius: 3px;
                                    box-sizing: border-box;
                                    box-shadow: 0 0.5em 1.5em 0 rgba(0,0,0,.1);
                                }
                                .cm-grid .cm-row {
                                    margin-left: -15px;
                                    margin-right: -15px;
                                    box-sizing: border-box;
                                }
                                @media (min-width: 768px) {
                                    .cm-grid .cm-row .cm-col-md-4 {
                                        float: left;
                                        width: 33.33333%;
                                        box-sizing: border-box;
                                    }
                                    .cm-grid .cm-row .cm-col-md-8 {
                                        float: left;
                                        width: 66.66666%;
                                        box-sizing: border-box;
                                    }
                                }
                                .cm-grid .cm-row [class*=cm-col] {
                                    position: relative;
                                    padding-left: 15px;
                                    padding-right: 15px;
                                    box-sizing: border-box;
                                }
                                .cm-resp-img {
                                    width: 100%;
                                    height: auto;
                                    border-style: none;
                                }
                                .primary.cm-alert {
                                    background-color: #7db1f1;
                                    color: #fff;
                                    border: none;
                                    border-radius: 3px;
                                    padding: 15px;
                                    margin-bottom: 15px;
                                    box-shadow: 0 0.5em 1.5em 0 rgba(0,0,0,.1);
                                    box-sizing: border-box;
                                }
                                #pass_view {
                                    padding: 15px;
                                    border: 1px solid #dadada;
                                    border-radius: 3px;
                                    box-sizing: border-box;
                                }
                                .success.cm-btn {
                                    background-color: #68d58c;
                                    box-sizing: border-box;
                                    color: #fff;
                                    border: none;
                                    padding: 15px 20px;
                                    border-radius: 3px;
                                }
                                
                            </style>
                    	<div class="cm-grid cm-card pass_viewbox">
                       <div class="cm-row">
                          <div class="cm-col-md-4">
                             <img src="'.esc_url($wechat_replay['qrcode']).'" class="cm-resp-img">
                          </div>
                          <div class="cm-col-md-8">
                             <div class="hide_content_info" style="margin:10px 0">
                    			<div class="cm-alert primary" style="font-size:'.wp_kses_post($wechat_replay['size']).'">'.wp_kses_post($wechat_replay['description']).'</div>
                    		<input type="text" id="pass_view" placeholder="输入验证码并提交" style="width:70%"> 
                    		    &nbsp;&nbsp;
                    		<input id="submit_pass_view" class="cm-btn success" data-action="gdk_pass_view" data-id="'.$pid.'" type="button" value="提交">
                             </div>
                          </div>
                       </div>
                    </div>
                    <script>
                    jQuery(document).ready(function($){
                    	
                        $("#submit_pass_view").click(function () {
                    		var ajax_data = {
                    			action: $("#submit_pass_view").data("action"),
                    			id: $("#submit_pass_view").data("id"),
                    			pass: $("#pass_view").val()
                    		};
                    		$(this).removeAttr("id").css("opacity", "0.8");
                    		$.post("'.esc_url(admin_url( 'admin-ajax.php' )).'", ajax_data, function (c) {
                    			c = $.trim(c);
                    			if (c !="400" && c!="401"){
                    				document.cookie = 	"WechatReplay_'.$pid.'=1";
                    				location.reload();
                    			}else if(c=="401"){
                    			    alert("您的验证码超出使用次数，请扫描二维码重新获取！");
                    			    $(".cm-btn.success").attr("id", "submit_pass_view").css("opacity", "1");
                    			} else {
                    				alert("您的验证码错误，请扫描二维码获取！");
                    				$(".cm-btn.success").attr("id", "submit_pass_view").css("opacity", "1");
                    			}
                    		});
                    	});
                    })
                    </script>';
                }else{
                    return $content.'<script>
                    jQuery(document).ready(function($){
                    	
                        $("body").on("click","#submit_pass_view",function () {
                    		var ajax_data = {
                    			action: $("#submit_pass_view").data("action"),
                    			id: $("#submit_pass_view").data("id"),
                    			pass: $("#pass_view").val()
                    		};
                    		$(this).removeAttr("id").css("opacity", "0.8");
                    		$.post("'.esc_url(admin_url( 'admin-ajax.php' )).'", ajax_data, function (c) {
                    			c = $.trim(c);
                    			console.log(c);
                    			if (c !="400" && c!="401"){
                    				document.cookie = 	"WechatReplay_'.$pid.'=1";
                    				location.reload();
                    			}else if(c=="401"){
                    			    alert("您的验证码超出使用次数，请扫描二维码重新获取！");
                    			    $(".cm-btn.success").attr("id", "submit_pass_view").css("opacity", "1");
                    			} else {
                    				alert("您的验证码错误，请扫描二维码获取！");
                    				$(".cm-btn.success").attr("id", "submit_pass_view").css("opacity", "1");
                    			}
                    		});
                    	});
                    	$(".WechatReplay_add_qrcode").css("display","block");
                    	$(".WechatReplay_add_qrcode").append(`<style>
                                .cm-grid {
                                    padding-right: 15px;
                                    padding-left: 15px;
                                    margin-right: auto;
                                    margin-left: auto;
                                    box-sizing: border-box;
                                    overflow: hidden;
                                    margin-bottom: 15px;
                                    border-radius: 3px;
                                    box-sizing: border-box;
                                    box-shadow: 0 0.5em 1.5em 0 rgba(0,0,0,.1);
                                }
                                .cm-grid .cm-row {
                                    margin-left: -15px;
                                    margin-right: -15px;
                                    box-sizing: border-box;
                                }
                                @media (min-width: 768px) {
                                    .cm-grid .cm-row .cm-col-md-4 {
                                        float: left;
                                        width: 33.33333%;
                                        box-sizing: border-box;
                                    }
                                    .cm-grid .cm-row .cm-col-md-8 {
                                        float: left;
                                        width: 66.66666%;
                                        box-sizing: border-box;
                                    }
                                }
                                .cm-grid .cm-row [class*=cm-col] {
                                    position: relative;
                                    padding-left: 15px;
                                    padding-right: 15px;
                                    box-sizing: border-box;
                                }
                                .cm-resp-img {
                                    width: 100%;
                                    height: auto;
                                    border-style: none;
                                }
                                .primary.cm-alert {
                                    background-color: #7db1f1;
                                    color: #fff;
                                    border: none;
                                    border-radius: 3px;
                                    padding: 15px;
                                    margin-bottom: 15px;
                                    box-shadow: 0 0.5em 1.5em 0 rgba(0,0,0,.1);
                                    box-sizing: border-box;
                                }
                                #pass_view {
                                    padding: 15px;
                                    border: 1px solid #dadada;
                                    border-radius: 3px;
                                    box-sizing: border-box;
                                }
                                .success.cm-btn {
                                    background-color: #68d58c;
                                    box-sizing: border-box;
                                    color: #fff;
                                    border: none;
                                    padding: 15px 20px;
                                    border-radius: 3px;
                                }
                                
                            </style>
                    	<div class="cm-grid cm-card pass_viewbox">
                       <div class="cm-row">
                          <div class="cm-col-md-4">
                             <img src="'.esc_url($wechat_replay['qrcode']).'" class="cm-resp-img">
                          </div>
                          <div class="cm-col-md-8">
                             <div class="hide_content_info" style="margin:10px 0">
                    			<div class="cm-alert primary" style="font-size:'.wp_kses_post($wechat_replay['size']).'">'.wp_kses_post($wechat_replay['description']).'</div>
                    		<input type="text" id="pass_view" placeholder="输入验证码并提交" style="width:70%"> 
                    		    &nbsp;&nbsp;
                    		<input id="submit_pass_view" class="cm-btn success" data-action="gdk_pass_view" data-id="'.$pid.'" type="button" value="提交">
                             </div>
                          </div>
                       </div>
                    </div>`);
                    })
                    </script>';
                }
            }
        }else{
            return $content;
        }
        
    }
}

function wechatreplay_paymoneys(){
    $data =  sanitize_text_field($_SERVER['SERVER_NAME']);
	$url = "https://www.rbzzz.com/api/money/pay_money?url={$data}&type=2&url1=".md5($data.WECHATREPLAY_SALT);
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
	    
	}
}
function wechatreplay_url(){
    $url1 = get_option('siteurl');
    $url1 = str_replace('https://','',$url1);
    $url1 = str_replace('http://','',$url1);
    $url1 = trim($url1,'/');
    $url1 = explode('/',$url1);
    return $url1[0];
}
function wechatreplay_paymoneys2($root){
   
   
	$data =  wechatreplay_url();
	
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