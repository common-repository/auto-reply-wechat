<?php
class wechatreplay{
    public function init(){
        global $WechatReplay_log;
        if($WechatReplay_log){
            $this->WechatReplay_zdhf();
        }else{
            add_action('wp_footer', [$this,'WechatReplay_wztwx']);
        }
        if(is_admin()){
            add_action( 'admin_enqueue_scripts', [$this,'wechatreplay_enqueue'] );
            add_action('admin_menu', [$this,'wechatreplay_addpages']);
            $this->wechatreplay_pluginaction();
            $WechatReplay_post = new WechatReplay_post();
            $WechatReplay_post->init();
            add_filter('plugin_action_links_' . plugin_basename(WECHATREPLAY_FILE), [$this,'wechatreplay_plugin_action_links']);
        }
        add_action( 'init', [$this,'wechatreplay_gutenberg_block'] );
        
    }
    public function wechatreplay_gutenberg_block(){
        
        	//注册古腾堡编辑器
        // 	wp_register_script( 'wechatreplay_icon-js', esc_url(plugins_url('../block/icon.js',__FILE__)), array('wp-blocks', 'wp-element', 'wp-editor', 'wp-i18n'), '1.0.0' );
        	wp_register_script( 'wechatreplay_block-js', esc_url(plugins_url('../block/yzm.js',__FILE__)), array('wp-blocks', 'wp-element', 'wp-editor', 'wp-i18n'), '1.0.0' );
        	 wp_localize_script('wechatreplay_block-js', 'url', plugin_dir_url(__FILE__));
        	//插入模块
        	//fishtheme/block可自定义, 比如: demo/block
        	register_block_type( 'fishtheme/block', array(
        		'editor_script' => 'wechatreplay_block-js'
        	) );
        }

    public function wechatreplay_plugin_action_links($links){
        $links[] = '<a href="' . esc_url_raw(admin_url( 'admin.php?page=wechatreplay' )) . '">设置</a>';
        return $links;
    }
    public function wechatreplay_pluginaction() {
        global $wpdb;
        $charset_collate = '';
        if (!empty($wpdb->charset)) {
          $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }
        if (!empty( $wpdb->collate)) {
          $charset_collate .= " COLLATE {$wpdb->collate}";
        }
         require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        if($wpdb->get_var("show tables like '{$wpdb->prefix}WechatReplay_keywords'") !=  $wpdb->prefix."WechatReplay_keywords"){
            $sql = "CREATE TABLE ".$wpdb->prefix . "WechatReplay_keywords(
                id int(10) NOT NULL AUTO_INCREMENT,
                keywords text NOT NULL,
                is_pub int(10) default 0,
                type int(10) default 1,
                link text NOT NULL,
                replay text NOT NULL,
                replay_title text NOT NULL,
                replay_img text NOT NULL,
                replay_desc text NOT NULL,
                addtime timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP,
                img_id varchar(255) NUll,
                UNIQUE KEY id (id)
            ) $charset_collate;";
            dbDelta( $sql );
        }
        $sql3 = 'Describe '.$wpdb->prefix.'WechatReplay_keywords title';
        $res = $wpdb->query($sql3);
        
        if($res){
             $wpdb->query(' ALTER TABLE '.$wpdb->prefix.'WechatReplay_keywords DROP COLUMN `title` ');
        }
         $sql3 = 'Describe '.$wpdb->prefix.'WechatReplay_keywords img_id';
            $res = $wpdb->query($sql3);
            
            if($res){
                 
            }else{
               $wpdb->query(' ALTER TABLE '.$wpdb->prefix.'WechatReplay_keywords ADD COLUMN `img_id` varchar(255)');
            }
       
    }
    public function wechatreplay_addpages(){
        add_menu_page(__('微信公众号','wechat_replay_html'), __('微信公众号','wechat_replay_html'), 'manage_options', 'wechatreplay', [$this,'wechatreplay_toplevelpage'] );
    }
    public function wechatreplay_toplevelpage(){
        echo "<div id='wechatreplay_wztkj-app'></div>";
    }
    public function wechatreplay_enqueue($hook){
        //后端
         wp_enqueue_style('wechatreplay_admin.css', plugin_dir_url(WECHATREPLAY_FILE) . 'block/wechatreplay_admin.css', false, '', 'all');
        if( 'toplevel_page_wechatreplay' != $hook ) return;
        wp_enqueue_media();
         require plugin_dir_path( WECHATREPLAY_FILE ) . 'assets.php';
        foreach($assets as $key=>$val){
            if($val['type']=='css'){
                 wp_enqueue_style( $val['name'],  plugin_dir_url( WECHATREPLAY_FILE ).$val['url'],false,'','all');
            }elseif($val['type']=='js'){
                wp_enqueue_script( $val['name'], plugin_dir_url( WECHATREPLAY_FILE ).$val['url'], '', '', true);
            }
           
        }
        wp_register_script('wechatreplay.js', false, null, false);
        wp_enqueue_script('wechatreplay.js');
        wp_add_inline_script('wechatreplay.js', 'var wechatreplay_wztkj_url="'.plugins_url('auto-reply-wechat').'/",wechatreplay_nonce="'. wp_create_nonce('wechatreplay').'",wechatreplay_ajax="'.esc_url(admin_url('admin-ajax.php')).'",wechatreplay_url="'.esc_url(get_option('siteurl')).'";', 'before');
    }
    public function WechatReplay_wztwx(){
        echo '<script>
        console.log("%c 微信公众号关键词回复插件 作者：沃之涛科技\n官网地址：www.rbzzz.com\n联系QQ：1500351892", "color: #fff;background-image: linear-gradient(90deg, red 0%, rgb(240, 240, 240) 100%);padding:5px 0;width: 200px;display: inline-block;");
        </script>';
    }
    public function WechatReplay_zdhf(){
        global $wpdb;
        $we = get_option('wechat_replay');
        
        if(isset($_GET['echostr'])){
    	    echo esc_attr($_GET['echostr']);exit;
    	}
    // 	if(1){
    	if( isset($_GET['signature']) && isset($_GET['timestamp']) && isset($_GET['nonce']) && isset($_GET['openid'])){
    	   
    		if($we['istrue']==1){
    			echo '';exit;
    		}
    	
    		$encryptMsg = @file_get_contents("php://input");
    	
            $objectxml = simplexml_load_string($encryptMsg, 'SimpleXMLElement', LIBXML_NOCDATA);//将文件转换成 对象
           
            $xmljson= json_encode($objectxml );//将对象转换个JSON
           
            $arr =json_decode($xmljson,true);//将json转换成数组
         
            $wechat_replay = get_option('wechat_replay_qrcode');
            if(isset($wechat_replay['title'])){
                $keys = explode(',',$wechat_replay['title']);
            }else{
                $keys = [];
            }
            
    		if($arr['MsgType']=='text'){
    		    if(in_array($arr['Content'],$keys)){
    		        $WechatReplay_captcha = get_option('WechatReplay_captcha');
    		        $open = get_option('WechatReplay_'.$arr['FromUserName']);
    		        if(!empty($WechatReplay_captcha) && $open!==false && $open>time()){
    		            echo '';exit;
    		        }else{
        		        if($WechatReplay_captcha!==false){
        		           if($WechatReplay_captcha['time']>time()){
        		               $WechatReplay_captcha = $WechatReplay_captcha['value'];
        		           }else{
        		               $WechatReplay_captcha =  wechatreplay_yzm::WechatReplay_captcha();
        		           }
        		        }else{
        		           $WechatReplay_captcha =  wechatreplay_yzm::WechatReplay_captcha();
        		        }
        		        if($open===false){
        		            add_option('WechatReplay_'.$arr['FromUserName'],time()+$wechat_replay['time']*60);
        		        }else{
        		            update_option('WechatReplay_'.$arr['FromUserName'],time()+$wechat_replay['time']*60);
        		        }
        		        echo '<xml>
        					  <ToUserName><![CDATA['.$arr['FromUserName'].']]></ToUserName>
        					  <FromUserName><![CDATA['.$arr['ToUserName'].']]></FromUserName>
        					  <CreateTime>'.time().'</CreateTime>
        					  <MsgType><![CDATA[text]]></MsgType>
        					  <Content><![CDATA[验证码（有效期：'.$wechat_replay['time'].'分钟）：'.$WechatReplay_captcha.']]></Content>
        					</xml>';exit;
    		        }
    		    }else{
    		       
    			$res1 =  $wpdb->get_results($wpdb->prepare('select * from '.$wpdb->prefix . 'WechatReplay_keywords where keywords="%s" and is_pub=1 order by id desc',$arr['Content']),ARRAY_A);
    			if(count($res1)>=1){
    			    
    				if($res1[0]['type']==2){
    					echo '<xml>
    					  <ToUserName><![CDATA['.$arr['FromUserName'].']]></ToUserName>
    					  <FromUserName><![CDATA['.$arr['ToUserName'].']]></FromUserName>
    					  <CreateTime>'.time().'</CreateTime>
    					  <MsgType><![CDATA[news]]></MsgType>
    					  <ArticleCount>1</ArticleCount>
    					  <Articles>
    					    <item>
    					      <Title><![CDATA['.$res1[0]['replay_title'].']]></Title>
    					      <Description><![CDATA['.$res1[0]['replay_desc'].']]></Description>
    					      <PicUrl><![CDATA['.$res1[0]['replay_img'].']]></PicUrl>
    					      <Url><![CDATA['.$res1[0]['link'].']]></Url>
    					    </item>
    					  </Articles>
    					</xml>';exit;
    				}else if($res1[0]['type']==1){
    					echo '<xml>
    					  <ToUserName><![CDATA['.$arr['FromUserName'].']]></ToUserName>
    					  <FromUserName><![CDATA['.$arr['ToUserName'].']]></FromUserName>
    					  <CreateTime>'.time().'</CreateTime>
    					  <MsgType><![CDATA[text]]></MsgType>
    					  <Content><![CDATA['.$res1[0]['replay'].']]></Content>
    					</xml>';exit;
    				}else if($res1[0]['type']==3){//回复图片
    				    echo '<xml>
                                  <ToUserName><![CDATA['.$arr['FromUserName'].']]></ToUserName>
                                  <FromUserName><![CDATA['.$arr['ToUserName'].']]></FromUserName>
                                  <CreateTime>'.time().'</CreateTime>
                                  <MsgType><![CDATA[image]]></MsgType>
                                  <Image>
                                    <MediaId><![CDATA['.$res1[0]['img_id'].']]></MediaId>
                                  </Image>
                                </xml>';
    				}
    			}else{
    			   
    			    if($we['type']==2){
    			        if($we['replay']){
        			        echo '<xml>
        					  <ToUserName><![CDATA['.$arr['FromUserName'].']]></ToUserName>
        					  <FromUserName><![CDATA['.$arr['ToUserName'].']]></FromUserName>
        					  <CreateTime>'.time().'</CreateTime>
        					  <MsgType><![CDATA[text]]></MsgType>
        					  <Content><![CDATA['.$we['replay'].']]></Content>
        					</xml>';exit;
    			        }else{
    			            echo '';exit;
    			        }
    			    }else{
    			      
    			        $res =  $wpdb->get_results($wpdb->prepare('select * from '.$wpdb->prefix . 'posts where post_status="publish" and post_type="post" and post_title like %s order by ID desc','%'.$arr['Content'].'%'),ARRAY_A);
    			        if($we['type']==3){
    			            if(count($res)>=1){
    			            $url = get_permalink($res[0]['ID']);
    			            
        			     
        			            $img = $we['replay_img'];
    			            
        			            echo '<xml>
            					  <ToUserName><![CDATA['.$arr['FromUserName'].']]></ToUserName>
            					  <FromUserName><![CDATA['.$arr['ToUserName'].']]></FromUserName>
            					  <CreateTime>'.time().'</CreateTime>
            					  <MsgType><![CDATA[news]]></MsgType>
            					  <ArticleCount>1</ArticleCount>
            					  <Articles>
            					    <item>
            					      <Title><![CDATA['.$res[0]['post_title'].']]></Title>
            					      <PicUrl><![CDATA['.$img.']]></PicUrl>
            					      <Url><![CDATA['.$url.']]></Url>
            					    </item>
            					  </Articles>
            					</xml>';exit;
    			            }else{
    			                 echo '<xml>
            					  <ToUserName><![CDATA['.$arr['FromUserName'].']]></ToUserName>
            					  <FromUserName><![CDATA['.$arr['ToUserName'].']]></FromUserName>
            					  <CreateTime>'.time().'</CreateTime>
            					  <MsgType><![CDATA[text]]></MsgType>
            					  <Content><![CDATA['.$we['replay'].']]></Content>
            					</xml>';exit;
    			            }
    			        }else{
    			            $img = $we['replay_img'];
        			        if(count($res)==1){
        			            $url = get_permalink($res[0]['ID']);
        			            $atten = get_post_thumbnail_id($res[0]['ID']);
        			      
        			            $atten = wp_get_attachment_image_src($atten);
        			                    
        			            echo '<xml>
            					  <ToUserName><![CDATA['.$arr['FromUserName'].']]></ToUserName>
            					  <FromUserName><![CDATA['.$arr['ToUserName'].']]></FromUserName>
            					  <CreateTime>'.time().'</CreateTime>
            					  <MsgType><![CDATA[news]]></MsgType>
            					  <ArticleCount>1</ArticleCount>
            					  <Articles>
            					    <item>
            					      <Title><![CDATA['.$res[0]['post_title'].']]></Title>
            					      <Description><![CDATA['.$res[0]['post_excerpt'].']]></Description>
            					      <PicUrl><![CDATA['.$atten[0].']]></PicUrl>
            					      <Url><![CDATA['.$url.']]></Url>
            					    </item>
            					  </Articles>
            					</xml>';exit;
        			        }else{
            			        echo '<xml>
            					  <ToUserName><![CDATA['.$arr['FromUserName'].']]></ToUserName>
            					  <FromUserName><![CDATA['.$arr['ToUserName'].']]></FromUserName>
            					  <CreateTime>'.time().'</CreateTime>
            					  <MsgType><![CDATA[news]]></MsgType>
            					  <ArticleCount>1</ArticleCount>
            					  <Articles>
            					    <item>
            					      <Title><![CDATA[点击进入搜索页面查看]]></Title>
            					      <Description><![CDATA[恭喜您找到了相关资源因为资源过多请点击进入搜索页查看]]></Description>
            					       <PicUrl><![CDATA['.$img.']]></PicUrl>
            					      <Url><![CDATA['.get_option('siteurl').'?s='.$arr['Content'].']]></Url>
            					    </item>
            					  </Articles>
            					</xml>';exit;
        			        }
    			        }
    			    }
    			}
    		    }
        	}elseif(($arr['MsgType']=='event') && ($arr['Event']=='subscribe') ){
        	     require_once( ABSPATH . WPINC . '/pluggable.php' );
        	    $EventKey =str_replace('qrscene_','',$arr['EventKey']);
        	     $res = $wpdb->get_results($wpdb->prepare('select * from '.$wpdb->prefix . 'usermeta where meta_key="wechatreplay_openid" and meta_value="%s"',$arr['FromUserName']),ARRAY_A);
        	    if(!empty($res)){
        	        update_user_meta($res[0]['user_id'],'wechatreplay_key',$EventKey);
        	    }else{
        	       
        	       
        	        $user_id = username_exists( $arr['FromUserName'] );
        	        if(!$user_id){
        	            $user_id = wp_create_user( $arr['FromUserName'], $arr['FromUserName'] );  
        	        }
                    add_user_meta($user_id,'wechatreplay_key',$EventKey);
                    add_user_meta($user_id,'wechatreplay_openid',$arr['FromUserName']);
        	    }
        	    if(isset($we['auto_replay']) && $we['auto_replay']){
        	       
        	        echo '<xml>
    					  <ToUserName><![CDATA['.$arr['FromUserName'].']]></ToUserName>
    					  <FromUserName><![CDATA['.$arr['ToUserName'].']]></FromUserName>
    					  <CreateTime>'.time().'</CreateTime>
    					  <MsgType><![CDATA[text]]></MsgType>
    					  <Content><![CDATA['.$we['auto_replay'].']]></Content>
    					</xml>';exit;
        	    }
        	}elseif($arr['MsgType']=='event' && $arr['Event']=='SCAN'   ){
        	     require_once( ABSPATH . WPINC . '/pluggable.php' );
        	    $res = $wpdb->get_results($wpdb->prepare('select * from '.$wpdb->prefix . 'usermeta where meta_key="wechatreplay_openid" and meta_value="%s"',$arr['FromUserName']),ARRAY_A);
        	    
        	    if(!empty($res)){
        	        update_user_meta($res[0]['user_id'],'wechatreplay_key',$arr['EventKey']);    
        	    }else{
        	       
        	       
        	        $user_id = username_exists( $arr['FromUserName'] );
        	        if(!$user_id){
        	            $user_id = wp_create_user( $arr['FromUserName'], $arr['FromUserName'] );  
        	        }
                    add_user_meta($user_id,'wechatreplay_key',$arr['EventKey']);
                    add_user_meta($user_id,'wechatreplay_openid',$arr['FromUserName']);
        	    }
        	    
        	    
        	}
    	}
    }
}
    
