(function(tinymce) {   

    tinymce.create('tinymce.plugins.yzm', { //注意这里有个 myadvert   

        init : function(ed, url){
            console.log(url)
            ed.addButton('yzm', { //注意这一行有一个 myadvert
                title : '验证码',
                image : url+'/image/weixingzh.jpg', //注意图片的路径 url是当前js的路径   

                onclick : function() {   
                    ed.selection.setContent('[WechatReplay][/WechatReplay]'); 
                }   

            });   

        },
     createControl : function(n, cm) {   

          return null;   

        },   

    });   

    tinymce.PluginManager.add('yzm', tinymce.plugins.yzm); //注意这里有两个 myadvert   

})(window.tinymce);  // JavaScript Document