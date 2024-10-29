<?php
class Wechat_login{
    public function __construct() {
       
        // add_shortcode('WechatLogin', [$this,'Wechat_login']);
        // add_action('WechatLogin',[$this,'Wechat_login']);
        add_action('wp_ajax_WechatReplay_login_true', [$this,'WechatReplay_login_true']);
        add_action('wp_ajax_nopriv_WechatReplay_login_true', [$this,'WechatReplay_login_true']);
        $WechatReplay_login = get_option('WechatReplay_login');
        // if(isset($WechatReplay_login['auto']) && $WechatReplay_login['auto']){
            add_action('wp_footer', [$this,'Wechat_login']);
        // }
    }
    public function WechatReplay_login_true(){
        if (is_user_logged_in()){
             echo json_encode(['code'=>1]);exit;
        }else{
            if(isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']),'WechatReplay')){
                $str = sanitize_text_field($_POST['str']);
                global $wpdb;
                $res = $wpdb->get_results($wpdb->prepare('select * from '.$wpdb->prefix . 'usermeta where meta_key="wechatreplay_key" and meta_value="%s"',$str),ARRAY_A);
               
                if(!empty($res)){
                    $openid = get_user_meta($res[0]['user_id'],'wechatreplay_openid',true);
                    // var_dump($openid);exit;
                    $login_data                  = array();
                    $login_data['user_login']    = $openid;
                    $login_data['user_password'] = $openid;
                    $login_data['remember']      = false;
                    
                    $user_verify = wp_signon($login_data, false);
                   
                   
                    if (is_wp_error($user_verify)) {
                         echo json_encode(['code'=>0]);exit;
                    }else{
                         echo json_encode(['code'=>1]);exit;
                    }
                   
                }else{
                    echo json_encode(['code'=>0]);exit;
                }
                 
            }
        }
         
    }
    public function Wechat_login(){
         if ( !is_user_logged_in () ) {
        $WechatReplay_login = get_option('WechatReplay_login');
        $msg = $this->qrcode();
        $msg = json_decode($msg,true);
       
        add_action('user_profile_update_errors', [$this,'my_user_profile_update_errors'], 10, 3 );
       
        ?>
        <style type='text/css'>
            .footer{
                position: fixed;
                bottom:0px;
                right: 0px;
                width: 100%;
                height: 78px;
                background-color: #ffffff;
                /*background-color: pink;*/
                display: flex;
                justify-content: space-between;
                align-items: center;
                transition: all 1s;
                border-top: 1px solid #f3eded;
                z-index: 99999;
            }
            .left{
                display: flex;
                gap: 0px 10px;
                align-items: center;
                margin-left: 100px;
                >img{
                    /*width: 78px;*/
                    height: 68px;
                }
                >div{
                    color: #333333;
                    font-size: 16px;
                    font-weight: 500;
                }
            }
            .right{
                width: 120px;
                height: 40px;
                justify-content: space-evenly;
                align-items: center;
                background-color: <?php echo $WechatReplay_login['bcolor'];?>;
                display: flex;
                margin-right: 100px;
                border-radius: 5px;
                transition: all 1s;
                /*>img{*/
                    /*width: 24px;*/
                /*    height: 24px;*/
                /*}*/
                >div{
                    color: #FFFFFF;
                    font-size: 12px;
                }
            }
            .btn_del{
                display: none;
            }
            /*默认隐藏缩小图标*/
            .sx{
                display: none;
                position: absolute;
                top: 5px;
                right: 25px;
                color: #AAAAAA;
                cursor: pointer;
                display: block;
                font-size: 20px;
            }
            .xs{
                position: absolute;
                top: 0px;
                right: 1px;
                color: #AAAAAA;
                display: none;
                cursor: pointer;
                font-size: 20px;
            }
            .tc{
                position: fixed;
                top: 0px;
                left: 0px;
                right: 0px;
                bottom:0px;
                background: rgba(0, 0, 0, 0.42);
                display: none;
                z-index: 100000;
            }
            .tc_nr{
                width: 480px;
                height: 480px;
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%,-50%);
                border-radius: 28px;
                background-color: #ffffff;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content:center;
                gap: 45px 0px;
            }
            .tc_nr_aqdl{
                color: #333;
                font-size: 24px;
                font-weight: 500;
            }
            .tc_nr_yjdl{
                color: #aaa;
                font-size: 13px;
                font-weight: 400;
            }
            .tc_nr_ewm{
                width: 208px;
                height: 238px;
                background-color: #ffffff;
                box-shadow: 0px 0px 32px 0px rgba(0, 0, 0, 0.06);
                display: flex;
                flex-direction: column;
                justify-content: space-evenly;
                align-items: center;
                border-radius: 16px;
                >img{
                    /*width: 176px;*/
                    height: 176px;
                }
            }
            .tc_nr_xy{
                color: #aaa;
                font-size: 13px;
                font-weight: 400;
            }
            .close_tc{
                color: #AAAAAA;
                position: absolute;
                top: 10px;
                right: 20px;
                display: none;
                cursor: pointer;
            }
            .tc_nr:hover .close_tc{
                display: block;
            }
            /*手机端*/
            @media screen and (max-width: 750px) {
                .footer{
                    left: 0px;
                    right: auto;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .left{
                    display: none;
                }

                .right{
                    margin-right: 0px;
                    width: 120px;
                    position: relative;
                }
              .btn_del {
                color: #FFFFFF;
                font-size: 12px;
                display: flex;
                justify-content: center;
                align-items: center;
                position: absolute;
                bottom: 12px;
                left: 0px;
                width: 28px;
                height: 50px;
                writing-mode: vertical-lr;
                background-color: <?php echo $WechatReplay_login['bcolor'];?>;
            
                /*>span {*/
                /*  display: flex;*/
                /*  justify-content: center;*/
                /*  align-items: center;*/
                /*  color: white;*/
                /*  background: #00000099;*/
                /*  border-top-right-radius: 8px;*/
                /*  width: 17px;*/
                /*  height: 18px;*/
                /*}*/
              }
                .sx{
                    display: none;
                }
                .xs{
                    display: none;
                }
                .tc_nr{
                    width: 290px;
                    height: 310px;
                    gap:10px 0px;
                    border-radius: 15px;
                }
                .tc_nr_ewm{
                    width: 195px;
                    height: 200px;
                }
                .tc_nr_aqdl{
                    font-size: 17px;
                }
                .close_tc{
                    top: 5px;
                    right: 6px;
                    display: block;
                }
            }
        </style>
        <div class="footer">
           <span class="sx">x</span>
           <span class="xs">+</span>
           <div class="left">
               <img <?php echo 'src="'.$WechatReplay_login['logo'].'"';?>>
               <div <?php echo 'style="color:'.$WechatReplay_login['color'].'"';?>><?php echo $WechatReplay_login['title'];?></div>
           </div>
           <div class="right">
               <!--<img>-->
               <div class="right_wxdl">微信登录</div>
                
           </div>
            <div class="btn_del">
                     <!--<span>x</span>-->
                     隐藏
            </div>
           <!--弹窗层-->
           <div class="tc">
               <div class="tc_nr">
                   <span class="tc_nr_aqdl"><?php echo $WechatReplay_login['logintitle'];?></span>
                   <span class="tc_nr_yjdl">微信内打开可长按扫码一键登录</span>
                   <div class="tc_nr_ewm">
                       
                       <img src="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=<?php echo $msg['ticket'];?>">
                       <span>微信扫码</span>
                   </div>
                   <?php if($WechatReplay_login['is_xy']){?>
                   <span class="tc_nr_xy">登录即表示同意<a href="<?php echo $WechatReplay_login['xy'];?>" target="_blank"><span style="color:#2281ff">服务协议条款</span></a></span>
                   <?php }?>
                    <!--关闭弹窗图标-->
                  <span class="close_tc">X</span>
               </div>
           </div>
        </div>
        <script>
          const sx =  document.querySelector(".sx")//缩小
          const xs =  document.querySelector(".xs")//显示
          const left =  document.querySelector(".left")
          const right =  document.querySelector(".right")
          const btn_del = document.querySelector('.btn_del')
          const innerDiv = right.querySelector(".right_wxdl")
          const footer =  document.querySelector(".footer")
          const tc = document.querySelector(".tc")
          const tcnr = document.querySelector(".tc_nr")
          const closetc = document.querySelector(".close_tc")
          sx.addEventListener("click",function(){
                  left.style.display = 'none'
                  right.style.width = '100%'
                  right.style.height = '100%'
                  right.style.marginRight = '0' + 'px'
                  footer.style.width = '120' + 'px'
                  footer.style.height = '40' + 'px'
                  sx.style.display = 'none'
                  xs.style.display = 'block'
                  //xs.style.cursor = 'pointer'
          })
          xs.addEventListener("click",function(){
                  left.style.display = 'flex'
                  right.style.width = '140' + 'px'
                  right.style.height = '40' + 'px'
                  right.style.marginRight = '100' + 'px'
                  footer.style.width = '100%'
                  footer.style.height = '78' + 'px'
                  sx.style.display = 'block'
                  xs.style.display = 'none'
          })
          let isSamll = true
          btn_del.addEventListener("click",function(event){
                 console.log("111")
                 event.stopPropagation()
                  footer.style.width = '28' + 'px'
                  footer.style.height = '50' + 'px'
                  right.style.width = '28' + 'px'
                  right.style.height = '50' + 'px'
                  right.style.display = 'flex'
                  right.style.justifyContent = "center"
                  right.style.alignItems = "center"
                  right.style.borderRadius = '0' + 'px'
                  right.style.writingMode = 'vertical-lr'
                  footer.style.display = 'flex'
                  footer.style.justifyContent = 'end'
                  btn_del.style.display = 'none'
                  innerDiv.innerHTML = '展开'
                  isSamll = false
            
               
             
          })
          right.addEventListener("click",function(){
              if(window.innerWidth > 750){
                  tc.style.display = 'block'
              }else{
                if(isSamll){
                 tc.style.display = 'block'
              }
              console.log("测试")
              footer.style.width = '100%'
              footer.style.height = '78' + 'px'
              right.style.width = '120' + 'px'
              right.style.height = '40' + 'px'
              right.style.alignItems = 'center'
              right.style.justifyContent = 'center'
              right.style.borderRadius = '5' + 'px'
              right.style.writingMode = 'horizontal-tb'
              footer.style.alignItems = 'center'
              footer.style.justifyContent = 'center'
              btn_del.style.display = 'flex'
              btn_del.style.alignItems = 'center'
              btn_del.style.justifyContent = 'center'
              innerDiv.innerHTML = '微信登录'
              isSamll = true
              }
             
          })
          tc.addEventListener("click",function(){
              tc.style.display = 'none'
          })
          tcnr.addEventListener("click",function(event){
              event.stopPropagation(); 
          })
          closetc.addEventListener("click",function(){
              tc.style.display = 'none'
          })
         
           setInterval(function(){
            jQuery(document).ready(function($){
                $.ajax({
                    url:"<?php echo esc_url(admin_url( 'admin-ajax.php' ));?>",
                    type:"post",
                    dataType:"json",
                    data:{str:"<?php echo $msg['str'];?>","action":"WechatReplay_login_true","nonce":"<?php echo wp_create_nonce('WechatReplay');?>"},
                    success:function(res){
                        if(res.code){
                            window.location.href="/" 
                        }
                    }
                })
            })
        },1000)
        

        </script>
        <?php
        }
       
    }
    public function my_user_profile_update_errors($errors, $update, $user){
        $errors->remove('empty_email');
    }
    public function qrcode(){
        $wechat_replay1 = get_option('wechat_replay');
        if(isset($wechat_replay1['appid']) && $wechat_replay1['appid']){
            $wechat_replay['appid'] = $wechat_replay1['appid'];
        }
        if(isset($wechat_replay1['secret']) && $wechat_replay1['secret']){
            $wechat_replay['secret'] = $wechat_replay1['secret'];
        }
        if(!isset($wechat_replay['appid'])){
             $msg = json_encode(['code'=>0]);
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
                $msg = json_encode(['code'=>1,'ticket'=>urlencode($data['ticket']),'str'=>$str]);
            }else{
                $msg = json_encode(['code'=>0]);
            }
        }else{
            $msg = json_encode(['code'=>0]);
        }
       return $msg;
    }
    
}

$Wechat_login = new Wechat_login();

