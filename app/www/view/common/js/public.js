//载入各种模块
$(function(){
	var p=0,t=0;  
    $(window).scroll(function(e){ 
        p = $(this).scrollTop();       
        if(t<=p && p>50){
            $('.header').removeClass('slideInDown').addClass('slideOutUp');
        }else{
            $('.header').removeClass('slideOutUp').addClass('slideInDown');
        }  
        t=p;     
    });
    
    $(".loginBtn").click(function(){
        login();
    });

	layui.use('form', function(){
  		var form = layui.form;  		
  		form.on('submit(go)', function(data){
            if (data.field.remember==1){
                localStorage.setItem('userNumber',data.field.userNumber);
            }
  			var load = layer.load(0,{shade: [0.7, '#000000']});
  			var formUrl = data.elem.getAttribute("url");
            $.ajax({
                url:formUrl,
                method:'post',
                data:data.field,
                dataType:'JSON',
                success:function(res){
                	layer.close(load);
                    if(res.code == 1){
                        if (res.msg!=''){
                            layer.open({
                                type:0, 
                                icon:1,
                                content:res.msg,
                                time:3000,
                                end: function(){ 
                                    if(res.url!='' && res.url!=undefined && res.url!="undefined"){
                                        if (res.data=='reload') {
                                            window.location.reload();
                                        }else{
                                            window.location.href = res.url;
                                        }                                   
                                    }
                                } 
                            });
                        }else{
                            if(res.url!='' && res.url!=undefined && res.url!="undefined"){
                                if (res.data=='reload') {
                                    window.location.reload();
                                }else{
                                    window.location.href = res.url;
                                }                                   
                            }
                        }                        
                    }else{
                        layer.alert(res.msg);
                    }
                },
                error:function (data) {
                	layer.close(load);
                	layer.alert("服务器连接失败");
                }
            })
            return false;
        });


		//自定义验证规则
		form.verify({
			_mobile: function(value) {
				if(value !='') {
					if (!checkMobile(value)) {
						return '请输入正确的手机号码';
					}
				}
			},
			__mobile: function(value) {
				if (!checkMobile(value)) {
					return '请输入正确的手机号码';
				}
			},
			_url: function(value) {
				if(value !='') {
					if (!checkUrl(value)) {
						return '请输入正确URL格式';
					}
				}
			},
			__url: function(value) {
				if (!checkUrl(value)) {
					return '请输入正确URL格式';
				}
			},
			__username: function(value) {
				if (!checkWordLong(value,2,8)) {
					return '请输入用户名2-8个字符';
				}
			},
			_password: function(value) {
				if(value !='') {
					if (!checkWordLong(value,6,12)) {
						return '请输入密码6-12个字符';
					}
				}
			},
			__password: function(value) {
				if (!checkWordLong(value,6,12)) {
					return '请输入密码6-12个字符';
				}
			},
			__repassword: function(value) {
				if (!checkRepassword(value)) {
					return '两次密码不同';
				}
			},
			_cardNo: function(value) {
				if(value !='') {
					if (!checkCardNo(value)) {
						return '请输入正确身份证号';
					}
				}
			},
			__cardNo: function(value) {
				if (!checkCardNo(value)) {
					return '请输入正确身份证号';
				}
			},
			sign: function(value) {
                if(value =='') {return '请输入签名，中文1个汉字，英文不超过1个单词';}
                var reg = /^[\u4E00-\u9FA5]{1,500}$/;
                if(reg.test(value)){//中文
                    if (value.length>1){
                        return '中文只能1个汉字';
                    }
                }else{
                    if (value.indexOf(' ') >= 0){
                        return '英文不能超过1个单词';
                    }
                }
			}
		});
	});
})

//登录
function login(){
    var userNumber='';
    if (localStorage.getItem("userNumber")!='' && localStorage.getItem("userNumber")!=undefined){
        userNumber = localStorage.getItem("userNumber");
    }
    var bg = $("#loginBg").val();
    layer.open({
      type: 1,
      title:"登录",
      skin: 'layui-layer-rim', //加上边框
      area: ['1000px', '540px'], //宽高
      content: `
        <div class="loginBox" style="background-image: url(`+bg+`); ">
            <div class="login">
            <div class="hd">会员登录</div>
            <form class="layui-form" method="post">
                <div class="layui-form-item">
                    <input type="number" name="userNumber" value="`+userNumber+`" required lay-verify="required" placeholder="请输入登录账号" autocomplete="off" class="layui-input">
                </div>

                <div class="layui-form-item">
                    <input type="password" name="password" required lay-verify="required" placeholder="请输入登录密码" autocomplete="off" class="layui-input">
                </div>

                <div class="layui-form-item">
                    <input type="checkbox" name="remember" value="1" lay-skin="primary" title="记住账号" checked="checked"/>
                </div>

                <div class="layui-form-item">
                    <button class="layui-btn layui-btn-danger layui-btn-fluid" lay-submit lay-filter="go" url="/www/login/loginDo">登录</button>
                </div>

                <div class="shuoming">微信扫码左侧二维码，关注我们的公众号，点击【商城账号】获取登录账号密码</div>
            </form>
            </div>
        </div>
      `,
      success: function(layero, index){
        layui.use(['form'], function(){
            var form = layui.form;
            form.render();
        })
      }
    });
}


function uploadImage(o,url){
    $("#uploadfile").click();
    $("#uploadfile").bind("change", function(){
        if ($(this).val() != "") {
            load = layer.load(0,{shade: [0.7, '#000000']});
            var fileObj = document.getElementById("uploadfile").files[0]; // js 获取文件对象
            var form = new FormData(); // FormData 对象            
            if(fileObj.size/1024 > 1025) { //大于1M，进行压缩上传
                cutImageBase64(document.getElementById("uploadfile"),800,0.8, function(base64Codes){
                    //console.log("压缩后：" + base64Codes.length / 1024 + " " + base64Codes);
                    var bl = convertBase64UrlToBlob(base64Codes);
                    form.append("file", bl, "file_"+Date.parse(new Date())+".jpg"); // 文件对象
                    _upload(url,form,o);
                });
            }else{ //小于等于1M 原图上传
                form.append("file", fileObj); // 文件对象
                _upload(url,form,o);
            }
        }
    });
}

function _upload(url,form,o){
    $.ajax({
        url: url,
        type: 'POST',
        cache: false,
        data: form,
        dataType:'json',
        timeout: 5000,
        //必须false才会避开jQuery对 formdata 的默认处理 
        // XMLHttpRequest会对 formdata 进行正确的处理
        processData: false,
        //必须false才会自动加上正确的Content-Type 
        contentType: false,
        xhrFields: {
           withCredentials: true
        },
        success: function(res) {
            layer.close(load);
            if(res.code=='1'){
                $("#uploadfile").unbind("change");
                $("#uploadfile").val("");                        
                $("#"+o).val(res.msg);
                $("#"+o+"_src").attr("src",res.msg);
            }else{
                layer.alert(res.msg);
            }
        }
    })
}

/*
 * @param m_this当前对象
 * @param id展示图片id
 * @param wid压缩后宽度
 * @param quality压缩质量 
 * */  
function cutImageBase64(m_this,wid,quality,callback) {    
    var file = m_this.files[0];
    var URL = window.URL || window.webkitURL;
    var blob = URL.createObjectURL(file);
    var base64;
    var img = new Image();
    img.src = blob;
    img.onload = function() {
        var that = this;
        //生成比例
        var w = that.width,
            h = that.height,
            scale = w / h;
            w = wid || w;
            h = w / scale;
        //生成canvas
        var canvas = document.createElement('canvas');
        var ctx = canvas.getContext('2d');
        $(canvas).attr({
            width: w,
            height: h
        });
        ctx.drawImage(that, 0, 0, w, h);
        // 生成base64            
        base64 = canvas.toDataURL('image/jpeg', quality || 0.8);
        callback(base64);
        //$(id).attr('src',base64);
    };
}

/**
 * 将以base64的图片url数据转换为Blob
 * @param urlData
 * 用url方式表示的base64图片数据
 */
function convertBase64UrlToBlob(urlData){
    var arr = urlData.split(','), mime = arr[0].match(/:(.*?);/)[1],
        bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
    while(n--){
        u8arr[n] = bstr.charCodeAt(n);
    }
    return new Blob([u8arr], {type:mime});
}

//验证手机号码
function checkMobile(numStr){ 
	var pattern = /^1[3|4|5|6|7|8|9][0-9]{9}$|^04[0-9]{8}$/;
	flag = pattern.test(numStr);
	if(!flag){
		return false;
	}else{
		return true;
	}
}

//验证URL
function checkUrl(v){
	var strRegex = "^((https|http|ftp|rtsp|mms)://)?[a-z0-9A-Z]{0,20}\.[a-z0-9A-Z][a-z0-9A-Z]{0,61}?[a-z0-9A-Z]\.com|net|cn|cc (:s[0-9]{1-4})?/$";
	var re = new RegExp(strRegex);
	if (re.test(v)) {
		return true;
	} else {
		return false;
	}
}

//验证邮箱
function checkEmail(v){
	var pattern = /^([a-zA-Z0-9_-])+(\.([a-zA-Z0-9_-])+)*@([a-zA-Z0-9_-])+(\.([a-zA-Z0-9_-])+)+$/;
	flag = pattern.test(v);
	if(flag){
		return true;
	}else{
		return false;
		}
}

//验证字符长度
function checkWordLong(v,min,max){	
	if(v.length>=min && v.length<=max){
		return true;
	}else{
		return false;
		}
}

//验证重复密码
function checkRepassword(v){
	if(v==$("#password").val()){
		return true;
	}else{
		return false;
		}
}

//验证身份证号码
function checkCardNo(v){
	var pattern = /(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{2}[0-9Xx]$)/;
	flag = pattern.test(v);
	if(flag){
		return true;
	}else{
		return false;
		}
}

//打开新窗口
function openModel (url,title,width,height) {
	layer.open({
		type: 2,
		title: title,
		shadeClose: true,
		shade: 0.8,
		area: [width, height],
		content: url //iframe的url
	}); 
}

//加入购物车
function addCart($itemID,$number,$goodsID){
    var load = layer.load(0,{shade: [0.7, '#000000']});
    $.get("/www/store/addcart?goodsID="+goodsID+"&itemID="+itemID+"&number="+number+"&temp="+new Date().getTime(),function(res){
        layer.close(load);
        if (res.code==0){
            layer.alert("操作失败");
        }else{
            $("#cartNumber").show().html(res.msg);
        }
    },'json');
}