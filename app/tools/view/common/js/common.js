mui.ready(function(){    
    $('#submitBtn').click(function(){
        var res = muiFormCheck('#myForm');
        //提交
        if(res){
            url = $(this).attr("action");
            var data = mui.getFormData('#myForm');
            mui.showLoading();
            mui.post(url,data,function(r){
                mui.hideLoading();
                if (r.code=='1'){
                    if ($("#cartContent").length > 0) {
                        $("#cartContent").html($("#emptyDiv").html());
                    }
                    mui.alert(r.msg, '系统消息', function(){
                        if(r.url!='' && r.url!=undefined){
                            if (r.url=='reload') {
                                window.location.reload();
                            }else{
                                window.location.href = r.url;
                            }                                   
                        }                       
                    })          
                }else{
                    mui.toast(r.msg);
                }

                if ($("#verifyImg").length > 0) {
                    $("#verifyImg").click();
                    $("#checkcode").val("");
                }
            },'json');
        }
    });

    //退出
    $("#exit").click(function(){
        mui.showLoading();
        url = '/mobile/login/signout';
        mui.get(url,function(r){
            mui.hideLoading();
            if (r.code=='1'){
                window.location.href = r.url;          
            }else{
                mui.toast(r.msg);
            }
        },'json');
    });
})

function uploadImage(o,url){
    $("#uploadfile").click();
    $("#uploadfile").unbind().change(function(){
        if ($(this).val() != "") {
            mui.showLoading('上传中');
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
        //必须false才会避开jQuery对 formdata 的默认处理 
        // XMLHttpRequest会对 formdata 进行正确的处理
        processData: false,
        //必须false才会自动加上正确的Content-Type 
        contentType: false,
        xhrFields: {
           withCredentials: true
        },
        success: function(res) {
            mui.hideLoading();
            if(res.code=='1'){      
                $("#uploadfile").unbind("change");
                $("#uploadfile").val("");                        
                _html = '<li><img src="'+res.msg+'" /><i class="mui-icon mui-icon-closeempty" onclick="removeLi(this)"></i><input name="image[]" type="hidden" value="'+res.msg+'"/></li>';
                $("#insert-btn").before(_html);
            }else{
                mui.toast(res.msg);
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

function removeLi(obj) {
    $(obj).parent().remove();
}

function setFace(obj,to,thumb_s,thumb_b){
    $("#"+to+"_s").val(thumb_s);
    $("#"+to+"_b").val(thumb_b);
    $(".insert-img li a").removeClass("active").html('设为封面');
    $(obj).addClass("active").html('封面');
}

function setAddress(address,lng,lat){
    $("#address").val(address);
    $("#address_str").html('<span>'+address+'</span>');
    $("#longitude").val(lng);
    $("#latitude").val(lat);
}
