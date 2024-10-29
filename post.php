<?php
if(!defined('ABSPATH'))exit;

class WechatReplay_post{
    public function init(){
        global $WechatReplay_log;
        
        add_action('wp_ajax_WechatReplay_index', [$this,'WechatReplay_index']);
        add_action('wp_ajax_WechatReplay_add', [$this,'WechatReplay_add']);
        add_action('wp_ajax_WechatReplay_edit', [$this,'WechatReplay_edit']);
        add_action('wp_ajax_WechatReplay_pladd', [$this,'WechatReplay_pladd']);
        add_action('wp_ajax_WechatReplay_qrcode', [$this,'WechatReplay_qrcode']);
        add_action('wp_ajax_WechatReplay_share', [$this,'WechatReplay_share']);
        add_action('wp_ajax_WechatReplay_replay_delete', [$this,'WechatReplay_replay_delete']);
        add_action('wp_ajax_WechatReplay_pl_delete', [$this,'WechatReplay_pl_delete']);
        add_action('wp_ajax_WechatReplay_pl_stop', [$this,'WechatReplay_pl_stop']);
        add_action('wp_ajax_WechatReplay_pl_start', [$this,'WechatReplay_pl_start']);
        add_action('wp_ajax_WechatReplay_login', [$this,'WechatReplay_login']);
        add_action('wp_ajax_WechatReplay_get_key', [$this,'WechatReplay_get_key']);
        add_action('wp_ajax_WechatReplay_get_vip', [$this,'WechatReplay_get_vip']);
        add_action('wp_ajax_wechatreplay_get_sucai_total', [$this,'wechatreplay_get_sucai_total']);
        add_action('wp_ajax_wechatreplay_get_sucai', [$this,'wechatreplay_get_sucai']);
        add_action('wp_ajax_WechatReplay_get_login', [$this,'WechatReplay_get_login']);
        add_action('wp_ajax_WechatReplay_get_index', [$this,'WechatReplay_get_index']);
        add_action('wp_ajax_WechatReplay_get_qrcode', [$this,'WechatReplay_get_qrcode']);
        add_action('wp_ajax_WechatReplay_get_share', [$this,'WechatReplay_get_share']);
        add_action('wp_ajax_WechatReplay_get_keywords', [$this,'WechatReplay_get_keywords']);
        add_action('wp_ajax_WechatReplay_get_replay', [$this,'WechatReplay_get_replay']);
        
    }
      public function WechatReplay_get_vip(){
        
        if(isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
            global $WechatReplay_log;
             $defaults = array(
                'timeout' => 4000,
                'connecttimeout'=>4000,
                'redirection' => 3,
                'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',
                'sslverify' => FALSE,
            );
            $wechatreplay_level = get_option('wechatreplay_level');
            if(!isset($wechatreplay_level[5]) || $wechatreplay_level[5]<time()-24*3600){
                $url = 'https://www.rbzzz.com/api/money/level2?url='.wechatreplay_url();
                $result = wp_remote_get($url,$defaults);
                if(!is_wp_error($result)){
                    $level = wp_remote_retrieve_body($result);
                    $level = json_decode($level,true);
                    
                    $level1 = explode(',',$level['level']);
                    $level1[2] = WECHATREPLAY_VERSION;
                    $level1[3] = $level['version'];
                    $level1[4] = $WechatReplay_log;
                    $level2 = $level1;
                    if(isset($level1[0]) && ($level1[0]==1 || $level1[0]==2)){
                        $level2[5] = time();
                        
                        update_option('wechatreplay_level',$level2);
                        
                    }
                }
            }else{
                $level1 = $wechatreplay_level;
                $level1[4] = $WechatReplay_log;
               
            }
            $data['level'] = $level1;
            
            echo wp_json_encode(['code'=>1,'data'=>$data]);exit;
        }
        echo wp_json_encode(['code'=>0]);exit;
    }
    public function WechatReplay_get_keywords(){
         if(isset($_POST['nonce']) && isset($_POST['action']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
           global $wpdb;
           $id = (int)$_POST['id'];
		    $keywords = $wpdb->get_results($wpdb->prepare('select * from '.$wpdb->prefix . 'WechatReplay_keywords where id=%d',$id),ARRAY_A);
           echo wp_json_encode(['code'=>1,'data'=>$keywords]);exit;
        }
        echo wp_json_encode(['code'=>0]);exit;
    }
    public function WechatReplay_get_share(){
         if(isset($_POST['nonce']) && isset($_POST['action']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
           $wechat_replay = get_option('wechat_replay_share');
           echo wp_json_encode(['code'=>1,'data'=>$wechat_replay]);exit;
        }
        echo wp_json_encode(['code'=>0]);exit;
    }
    public function WechatReplay_get_qrcode(){
         if(isset($_POST['nonce']) && isset($_POST['action']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
           $wechat_replay = get_option('wechat_replay_qrcode');
           echo wp_json_encode(['code'=>1,'data'=>$wechat_replay]);exit;
        }
        echo wp_json_encode(['code'=>0]);exit;
    }
    public function WechatReplay_get_index(){
        if(isset($_POST['nonce']) && isset($_POST['action']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
           $wechat_replay = get_option('wechat_replay');
           if($wechat_replay===false){
               $wechat_replay = [];
               $wechat_replay['title'] = esc_url(get_option('siteurl'));
           }else{
               $wechat_replay['title'] = esc_url(get_option('siteurl'));
           }
           echo wp_json_encode(['code'=>1,'data'=>$wechat_replay]);exit;
        }
        echo wp_json_encode(['code'=>0]);exit;
    }
    public function WechatReplay_get_login(){
        if(isset($_POST['nonce']) && isset($_POST['action']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
            $WechatReplay_login = get_option('WechatReplay_login');
            echo wp_json_encode(['code'=>1,'data'=>$WechatReplay_login]);exit;
        }
         echo wp_json_encode(['code'=>0]);exit;
    }
    public function qrcode(){
        $wechat_replay1 = get_option('wechat_replay');
        if(isset($wechat_replay1['appid']) && $wechat_replay1['appid']){
            $wechat_replay['appid'] = $wechat_replay1['appid'];
        }
        if(isset($wechat_replay1['secret']) && $wechat_replay1['secret']){
            $wechat_replay['secret'] = $wechat_replay1['secret'];
        }
        if(!isset($wechat_replay['appid']) && $wechat_replay['appid']){
            $msg = json_encode(['code'=>0,'msg'=>'微信公众号首页的AppID未配置']);
        }
        if(!isset($wechat_replay['secret']) && $wechat_replay['secret']){
            $msg = json_encode(['code'=>0,'msg'=>'微信公众号首页的AppSecret未配置']);
        }
        $jssdk = new wechat_JSSDK($wechat_replay['appid'],$wechat_replay['secret']);
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$jssdk->Wechatacc();
        $str = time().'user'.rand(1000,9999);
        $post ='{"expire_seconds": 604800, "action_name": "QR_STR_SCENE", "action_info": {"scene": {"scene_str": "'.$str.'"}}}';
        $data = wp_remote_post($url,['body'=>$post]);
      
        if(!is_wp_error($data)){
            $data = wp_remote_retrieve_body($data);
            
            $data = json_decode($data,true);
            if(isset($data['ticket'])){
                $msg = json_encode(['code'=>1]);
            }else{
                $msg = json_encode(['code'=>0,'msg'=>'公众号配置不正确，无法开启公众号登录']);
            }
        }else{
            $msg = json_encode(['code'=>0,'msg'=>'请检查微信公众号首页的AppID和AppSecret是否正确']);
        }
        return $msg;
    }
    public function WechatReplay_login(){
       if(isset($_POST['nonce']) && isset($_POST['action']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
            $pay = wechatreplay_paymoney('/api/index/pay_money');
            if(isset($pay['status']) && $pay['status']==1){
                $WechatReplay_login = get_option('WechatReplay_login');
                $arr = [
                    'title'=>sanitize_text_field($_POST['title']),
                    'color'=>sanitize_text_field($_POST['color']),
                    'logo'=>sanitize_url($_POST['logo']),
                    'bcolor'=>sanitize_text_field($_POST['bcolor']),
                    'logintitle'=>sanitize_text_field($_POST['logintitle']),
                    'xy'=>sanitize_url($_POST['xy']),
                    'is_xy'=>(int)$_POST['is_xy'],
                    'auto'=>(int)$_POST['auto']
                ];
                if($_POST['auto']==1){
                    $msg = $this->qrcode();
                    $m = json_decode($msg,true);
                    if($m['code']==1){
                        if($WechatReplay_login!==false){
                            update_option('WechatReplay_login',$arr);
                        }else{
                            add_option('WechatReplay_login',$arr);
                        }
                    }
                }else{
                  
                     if($WechatReplay_login!==false){
                            update_option('WechatReplay_login',$arr);
                        }else{
                            add_option('WechatReplay_login',$arr);
                        }
                    echo wp_json_encode(['code'=>1]);exit;
                }
               echo $msg;exit;
            }else{
                echo wp_json_encode(['code'=>0,'msg'=>'请先授权']);exit;
            }
        }
        echo wp_json_encode(['code'=>0,'msg'=>'保存失败']);exit;
    }
    public function WechatReplay_pl_start(){
        if(isset($_POST['nonce'])  && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
            $pay = wechatreplay_paymoney('/api/index/pay_money');
             if(!isset($pay['status']) || !$pay['status']){
                 echo wp_json_encode(['code'=>0,'msg'=>'请先授权']);exit;
             }
            global $wpdb;
            $ids = explode(',',sanitize_text_field($_POST['id']));
            $ids = array_map('intval',$ids);
            foreach($ids as $key=>$val){
                $wpdb->update($wpdb->prefix . 'WechatReplay_keywords',['is_pub'=>1],['id'=>$val]);
            }
            echo wp_json_encode(['code'=>1]);exit;
            
        }
         echo wp_json_encode(['code'=>0]);exit;
    }
    public function WechatReplay_pl_stop(){
        if(isset($_POST['nonce'])  && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
            $pay = wechatreplay_paymoney('/api/index/pay_money');
            if(!isset($pay['status']) || !$pay['status']){
                echo wp_json_encode(['code'=>0,'msg'=>'请先授权']);exit;
            }
            global $wpdb;
            $ids = explode(',',sanitize_text_field($_POST['id']));
            $ids = array_map('intval',$ids);
            foreach($ids as $key=>$val){
                $wpdb->update($wpdb->prefix . 'WechatReplay_keywords',['is_pub'=>0],['id'=>$val]);
            }
            echo wp_json_encode(['code'=>1]);exit;
        }
        echo wp_json_encode(['code'=>0]);exit;
    }
    public function WechatReplay_pl_delete(){
        if(isset($_POST['nonce'])  && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
             $pay = wechatreplay_paymoney('/api/index/pay_money');
            if(!isset($pay['status']) || !$pay['status']){
                echo wp_json_encode(['code'=>0,'msg'=>'请先授权']);exit;
            }
            global $wpdb;
            $ids = explode(',',sanitize_text_field($_POST['id']));
            $ids = array_map('intval',$ids);
            foreach($ids as $key=>$val){
                $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "WechatReplay_keywords where id=  %d",$val));
            }
            echo wp_json_encode(['code'=>1]);exit;
        }
        echo wp_json_encode(['code'=>0]);exit;
    }
    public function WechatReplay_replay_delete(){
        if(isset($_POST['nonce'])  && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
             $pay = wechatreplay_paymoney('/api/index/pay_money');
            if(!isset($pay['status']) || !$pay['status']){
                echo wp_json_encode(['code'=>0,'msg'=>'请先授权']);exit;
            }
             global $wpdb;
            $id = (int)$_POST['id'];
            $res = $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "WechatReplay_keywords where id=  %d",$id));
            if($res){
            	echo wp_json_encode(['code'=>1]);exit;
            }else{
            	echo wp_json_encode(['code'=>0]);exit;
            }
        }
        echo wp_json_encode(['code'=>0]);exit;
    }
    public function WechatReplay_get_replay(){
       
        if(isset($_POST['nonce'])  && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
            
            global $wpdb;
            $p1 = isset($_POST['pages'])?(int)$_POST['pages']:1;
            
		    $start1 = ($p1-1)*20;
		    $search =isset($_POST['search'])?sanitize_text_field($_POST['search']):'';
		    if($search){
    		    $count = $wpdb->query($wpdb->prepare('select * from '.$wpdb->prefix . 'WechatReplay_keywords where keywords like "%s"','%'.$search.'%'),ARRAY_A);
    		   
    		    $keywords = $wpdb->get_results($wpdb->prepare('select * from '.$wpdb->prefix . 'WechatReplay_keywords where keywords like "%s"  order by id desc limit %d ,20','%'.$search.'%',$start1),ARRAY_A);
		    }else{
		        $count = $wpdb->query('select * from '.$wpdb->prefix . 'WechatReplay_keywords',ARRAY_A);
    		    
    		    $keywords = $wpdb->get_results($wpdb->prepare('select * from '.$wpdb->prefix . 'WechatReplay_keywords order by id desc limit %d ,20',$start1),ARRAY_A);
		    }
		    if(is_array($keywords) && !empty($keywords)){
    		    foreach($keywords as $key=>$val){
    		        if($val['is_pub']==1){
    		            $keywords[$key]['is_pub']='启动';
    		        }elseif($val['is_pub']==0){
    		            $keywords[$key]['is_pub']='停止';
    		        }
    		    }
		    }
		    echo wp_json_encode(['code'=>1,'msg'=>'','count'=>$count,'data'=>$keywords,'total'=>ceil($count/20),'current_page'=>$p1,'pagesize'=>20]);exit;
        }
        echo wp_json_encode(['msg'=>0]);exit;
    }
    public function WechatReplay_get_key(){
        if(isset($_POST['nonce'])  && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
            $key = sanitize_text_field($_POST['key']);
            $data = wechatreplay_url();
            $url1 = sanitize_text_field($_SERVER['SERVER_NAME']);
            $url = 'https://www.rbzzz.com/api/money/log2?url='.$data.'&url1='.$url1.'&key='.$key;
            $defaults = array(
                'timeout' => 120,
                'connecttimeout'=>120,
                'redirection' => 3,
                'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',
                'sslverify' => FALSE,
            );
            $result = wp_remote_get($url,$defaults);
            if(!is_wp_error($result)){
                $content = wp_remote_retrieve_body($result);
                if($content){
                    $baiduseo_wzt_log = get_option('WechatReplay_log');
                    if($baiduseo_wzt_log!==false){
            	        update_option('WechatReplay_log',$key);
            	    }else{
            	        add_option('WechatReplay_log',$key);
            	    }
                    echo wp_json_encode(['code'=>1]);exit;
                }
        	}
        	echo wp_json_encode(['code'=>0]);exit;
        	
        
        }
    }
    public function WechatReplay_share(){
        if(isset($_POST['nonce']) && isset($_POST['action']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
            $pay = wechatreplay_paymoney('/api/index/pay_money');
            if(!isset($pay['status']) || !$pay['status']){
                echo wp_json_encode(['code'=>0,'msg'=>'请先授权']);exit;
            }
        	$post = [
		        'title'=>sanitize_text_field($_POST['title']),
		        'description'=>sanitize_textarea_field($_POST['description']),
		        'icon'=>sanitize_text_field($_POST['icon']),
		        ];
			$wechat_replay = get_option('wechat_replay_share');
			if($wechat_replay!==false){
				update_option('wechat_replay_share',$post);
			}else{
				add_option('wechat_replay_share',$post);
			}
			echo wp_json_encode(['code'=>1]);exit;
        }
        	echo wp_json_encode(['code'=>0]);exit;
    }
    public function WechatReplay_qrcode(){
        if(isset($_POST['nonce']) && isset($_POST['action']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
            $pay = wechatreplay_paymoney('/api/index/pay_money');
            if(!isset($pay['status']) || !$pay['status']){
                echo wp_json_encode(['code'=>0,'msg'=>'请先授权']);exit;
            }
            $post = [
        	        'title'=>sanitize_text_field($_POST['title']),
    		        'qrcode'=>sanitize_text_field($_POST['qrcode']),
    		        'description'=>sanitize_textarea_field($_POST['description']),
    		        'istheme'=>(int)$_POST['istheme'],
    		        'time'=>(int)$_POST['time'],
    		        'type'=>(int)$_POST['type'],
    		        'yzm'=>sanitize_text_field($_POST['yzm']),
    		        'color'=>sanitize_text_field($_POST['color']),
    		        'size'=>(int)($_POST['size']),
    		        'yzm_num'=>(int)$_POST['yzm_num'],
    		        'content'=>sanitize_text_field($_POST['content']),
    		        ];
    		$wechat_replay = get_option('wechat_replay_qrcode');
			if($wechat_replay!==false){
				update_option('wechat_replay_qrcode',$post);
			}else{
				add_option('wechat_replay_qrcode',$post);
			}
		   echo wp_json_encode(['code'=>1]);exit;
        }
        echo wp_json_encode(['code'=>0]);exit;
    }
    public function WechatReplay_pladd(){
        if(isset($_POST['nonce']) && isset($_POST['action']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
            $pay = wechatreplay_paymoney('/api/index/pay_money');
            if(!isset($pay['status']) || !$pay['status']){
                echo wp_json_encode(['code'=>0,'msg'=>'请先授权']);exit;
            }
             global $wpdb;
            $replay = explode("\n",sanitize_textarea_field($_POST['replay']));
    	    if(!empty($replay)){
    	        foreach($replay as $key=>$val){
    	            if($val){
        	            $title = explode(',',$val);
        	            
        	            	$post = [
        			        'keywords'=>sanitize_text_field($title[0]),
        			        'type'=>1,
        			        'replay'=>sanitize_text_field($title[1]),
        			        'replay_title'=>'',
        			        'replay_img'=>'',
        			        'replay_desc'=>'',
        			        'link'=>'',
        			        'is_pub'=>1
        			    ];
        			    $res = $wpdb->insert($wpdb->prefix . 'WechatReplay_keywords',$post);
    	            }
    	        }
    	    }
    	    echo wp_json_encode(['code'=>1]);exit;
        }
         echo wp_json_encode(['code'=>0]);exit;
    }
    public function WechatReplay_edit(){
         if(isset($_POST['nonce']) && isset($_POST['action']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
             $pay = wechatreplay_paymoney('/api/index/pay_money');
            if(!isset($pay['status']) || !$pay['status']){
                echo wp_json_encode(['code'=>0,'msg'=>'请先授权']);exit;
            }
            global $wpdb;
            $post = [
		        'keywords'=>sanitize_text_field($_POST['keywords']),
		        'type'=>(int)$_POST['type'],
		        'replay'=>sanitize_textarea_field($_POST['replay']),
		        'replay_title'=>sanitize_text_field($_POST['replay_title']),
		        'replay_img'=>sanitize_text_field($_POST['replay_img']),
		        'replay_desc'=>sanitize_textarea_field($_POST['replay_desc']),
		        'link'=>sanitize_url($_POST['link']),
		        'is_pub'=>(int)$_POST['is_pub'],
		        'img_id'=>sanitize_textarea_field($_POST['img_id'])
		    ];
		    
		        
			    $current_time = current_time( 'Y/m/d H:i:s');
		        $post['addtime'] = $current_time;
		        $res =  $wpdb->update($wpdb->prefix . 'WechatReplay_keywords',$post,['id'=>(int)$_POST['id']]);
		        
		   echo wp_json_encode(['code'=>1]);exit;
        }
        echo wp_json_encode(['code'=>0]);exit;
    }
    public function  WechatReplay_add(){
        if(isset($_POST['nonce']) && isset($_POST['action']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
            $pay = wechatreplay_paymoney('/api/index/pay_money');
            if(!isset($pay['status']) || !$pay['status']){
                echo wp_json_encode(['code'=>0,'msg'=>'请先授权']);exit;
            }
            global $wpdb;
            $post = [
		        'keywords'=>sanitize_text_field($_POST['keywords']),
		        'type'=>(int)$_POST['type'],
		        'replay'=>sanitize_textarea_field($_POST['replay']),
		        'replay_title'=>sanitize_text_field($_POST['replay_title']),
		        'replay_img'=>sanitize_text_field($_POST['replay_img']),
		        'replay_desc'=>sanitize_textarea_field($_POST['replay_desc']),
		        'link'=>sanitize_url($_POST['link']),
		        'is_pub'=>(int)$_POST['is_pub'],
		        'img_id'=>sanitize_textarea_field($_POST['img_id']),
		        
		    ];
		    
		    $res = $wpdb->insert($wpdb->prefix . 'WechatReplay_keywords',$post);
		  
		    if($res){
		        echo wp_json_encode(['code'=>1]);exit;
		    }else{
		        echo wp_json_encode(['code'=>0]);exit;
		    }
        }
        echo wp_json_encode(['code'=>0]);exit;
    }
    public function WechatReplay_index(){
        
        if(isset($_POST['nonce']) && isset($_POST['action']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
            $pay = wechatreplay_paymoney('/api/index/pay_money');
            if(!isset($pay['status']) || !$pay['status']){
                echo wp_json_encode(['code'=>0,'msg'=>'请先授权']);exit;
            }
            	$we = [
					'title'=>esc_url(get_option('siteurl')),
					'token'=>sanitize_text_field($_POST['token']),
					'key'=>sanitize_text_field($_POST['key']),
					'replay'=>sanitize_textarea_field($_POST['replay']),
					'type' =>(int)$_POST['type'],
					'replay_img'=>sanitize_url($_POST['replay_img']),
					'secret'=>sanitize_text_field($_POST['secret']),
		            'appid'=>sanitize_text_field($_POST['appid']),
		            'icontype'=>(int)$_POST['icontype']
				];
				$we['istrue'] =(int)$_POST['istrue'];
				
				$we['auto_replay'] = sanitize_textarea_field($_POST['auto_replay']);
			
				$wechat_replay = get_option('wechat_replay');
				if($wechat_replay!==false){
					update_option('wechat_replay',$we);
				}else{
					add_option('wechat_replay',$we);
				}
			 echo wp_json_encode(['code'=>1]);exit;
        }
        echo wp_json_encode(['code'=>0]);exit;
    }
    public function wechatreplay_get_sucai_total(){
        
        if(isset($_POST['nonce']) && isset($_POST['action']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
            
            $wechat_replay = get_option('wechat_replay');
            
            $jssdk = new wechat_JSSDK($wechat_replay['appid'],$wechat_replay['secret']);
            
             $url = 'https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token='.$jssdk->Wechatacc();
            
            $defaults = array(
                'timeout' => 120,
                'connecttimeout'=>120,
                'redirection' => 3,
                'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',
                'sslverify' => FALSE,
            );
            $result = wp_remote_get($url,$defaults);
           
            if(!is_wp_error($result)){
                $content = wp_remote_retrieve_body($result);
                $content = json_decode($content,true);
                
                echo wp_json_encode(['total'=>$content['image_count'],'code'=>1]);exit;
                
        	}
        }
        echo wp_json_encode(['code'=>0]);exit;
    }
    public function wechatreplay_get_sucai(){
        if(isset($_POST['nonce']) && isset($_POST['action']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'wechatreplay')){
            
            $wechat_replay = get_option('wechat_replay');
           
            $jssdk = new wechat_JSSDK($wechat_replay['appid'],$wechat_replay['secret']);
            $pages = isset($_POST['pages'])?(int)$_POST['pages']:1;
            //  https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=ACCESS_TOKEN
             $url = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token='.$jssdk->Wechatacc();
           
    	    $result = wp_remote_post($url,['body'=>wp_json_encode(['type'=>'image','offset'=>($pages-1)*20,'count'=>20])]);
    	    if(!is_wp_error($result)){
    	        $result = wp_remote_retrieve_body($result);
    	        echo wp_json_encode(['data'=>$result,'code'=>1]);exit;
    	    }
        }
        echo wp_json_encode(['code'=>0]);exit;
    }
    
}
function wechatreplay_paymoney($root){
    $wechatreplay_shouquan = get_option('wechatreplay_shouquan');
    if(isset($wechatreplay_shouquan['time']) && $wechatreplay_shouquan['time']>time() ){
        return $wechatreplay_shouquan['content'];
    }
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
	    if($wechatreplay_shouquan!==false){
	        update_option('wechatreplay_shouquan',['content'=>$content,'time'=>time()+24*3600]);
	    }else{
	        add_option('wechatreplay_shouquan',['content'=>$content,'time'=>time()+24*3600]);
	    }
		return $content;
	}else{
	    return wechatreplay_paymoneys2($root);
	}
}


