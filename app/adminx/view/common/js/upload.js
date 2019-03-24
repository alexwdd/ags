layui.use(['upload','layer','fsConfig','fsCommon'], function(){
	var upload = layui.upload,
	 	fsConfig = layui.fsConfig,
	  	layer = layui.layer,
	  	fsCommon = layui.fsCommon,
		statusName = $.result(fsConfig,"global.result.statusName","errorNo"),
  		msgName = $.result(fsConfig,"global.result.msgName","errorInfo"),
		successNo = $.result(fsConfig,"global.result.successNo","0"),
  		uploadUrl = $.result(fsConfig,"global.uploadUrl",""),
  		dataName = $.result(fsConfig,"global.result.dataName","results.data");

  	//多图
  	//$(".muploud").each(function(){
  		var btn = $(".muploud");
		var url = btn.attr("url");
		var tag = btn.attr("tag");
		var fileExts = btn.attr("exts");
		var fileSize = btn.attr("size");
		var fileAccept = btn.attr("accept");
		upload.render({
		    elem: '.muploud',
		    url: this.url,
		    accept :fileAccept,
		    exts :fileExts,
		    size :fileSize,
		    before: function(obj){
    			layer.load(); //上传loading
		    },
		    done: function(result){		    	
		    	layer.closeAll(); //关闭loading
		    	if(result[statusName] != successNo){
					var filters = fsConfig["filters"];
				   	if(!$.isEmpty(filters)){
			     	  var otherFunction = filters[result[statusName]];
			      	  if($.isFunction(otherFunction)){
			       	     otherFunction(result);
			      	      return;
			     	   }
				    }
			   		fsCommon.errorMsg(result[msgName]);
			   		return;
				}
				
				//上传成功后，返回文件路径
				var data = $.result(result,dataName);
				_html = '<li><a href="'+data.url+'" target="_blank"><img src="'+data.url+'" /></a><input type="hidden" name="image[]" value="'+data.url+'" /><i class="layui-icon" onclick="removeLi(this)">&#x1006;</i></li>';
				thisBtn = this.item;
				thisBtn.before(_html);
		    }
		});
  	//})
  	/*if($(".muploud").length>0){
  		$
  		upload.render({
		    elem: '.muploud',
		    before: function(){
		      layer.tips('接口地址：'+ this.url, this.item, {tips: 1});
		    },
		    done: function(res, index, upload){
		      var item = this.item;
		      console.log(item); //获取当前触发上传的元素，layui 2.1.0 新增
		    }
		})
  	}*/
  	

	if($("#muploud").length>0){
		var btn = $("#muploud");
		var url = btn.attr("url");
		var tag = btn.attr("tag");
		var fileExts = btn.attr("exts");
		var fileSize = btn.attr("size");
		var fileAccept = btn.attr("accept");
		upload.render({
		    elem: '#muploud',
		    url: url,
		    accept :fileAccept,
		    exts :fileExts,
		    size :fileSize,
		    before: function(obj){    	
    			layer.load(); //上传loading
		    },
		    done: function(result){
		    	layer.closeAll(); //关闭loading
		    	if(result[statusName] != successNo){
					var filters = fsConfig["filters"];
				   	if(!$.isEmpty(filters)){
			     	  var otherFunction = filters[result[statusName]];
			      	  if($.isFunction(otherFunction)){
			       	     otherFunction(result);
			      	      return;
			     	   }
				    }
			   		fsCommon.errorMsg(result[msgName]);
			   		return;
				}
				
				//上传成功后，返回文件路径
				var data = $.result(result,dataName);
				_html = '<li><a href="'+data.url+'" target="_blank"><img src="'+data.url+'" /></a><input type="hidden" name="image[]" value="'+data.url+'" /><i class="layui-icon" onclick="removeLi(this)">&#x1006;</i></li>';
				btn.before(_html);
		    }
		});
	}

	if($("#muploud1").length>0){
		var btn1 = $("#muploud1");
		var url1 = btn1.attr("url");
		var tag1 = btn1.attr("tag");
		var fileExts1 = btn1.attr("exts");
		var fileSize1 = btn1.attr("size");
		var fileAccept1 = btn1.attr("accept");
		upload.render({
		    elem: '#muploud1',
		    url: url1,
		    accept :fileAccept1,
		    exts :fileExts1,
		    size :fileSize1,
		    before: function(obj){    	
    			layer.load(); //上传loading
		    },
		    done: function(result){
		    	layer.closeAll(); //关闭loading
		    	if(result[statusName] != successNo){
					var filters = fsConfig["filters"];
				   	if(!$.isEmpty(filters)){
			     	  var otherFunction = filters[result[statusName]];
			      	  if($.isFunction(otherFunction)){
			       	     otherFunction(result);
			      	      return;
			     	   }
				    }
			   		fsCommon.errorMsg(result[msgName]);
			   		return;
				}
				
				//上传成功后，返回文件路径
				var data = $.result(result,dataName);
				_html = '<li><a href="'+data.url+'" target="_blank"><img src="'+data.url+'" /></a><input type="hidden" name="eimg[]" value="'+data.url+'" /><i class="layui-icon" onclick="removeLi(this)">&#x1006;</i></li>';
				btn1.before(_html);
		    }
		});
	}
})

function removeLi(obj) {
	console.info($(obj).parent());
	$(obj).parent().remove();
}

function delImage(domid){
	$("#"+domid+"_url").val("");
	$("#"+domid+"_src").attr('src','/app/adminx/view/common/image/image.jpg');
}